<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Models\Meeting;
use App\Repositories\MeetingRepository;
use App\Repositories\FormatRepository;
use Illuminate\Support\Collection;

class MeetingResource extends JsonResource
{
    private static bool $isRequestInitialized = false;
    private static ?Collection $dataTemplates = null;
    private static ?Collection $customFields = null;
    private static ?Collection $formatsById = null;
    private static ?int $virtualFormatId = null;
    private static ?int $hybridFormatId = null;
    private static ?int $temporarilyClosedFormatId = null;
    private static ?Collection $hiddenFormatIds = null;

    public static function resetStaticVariables()
    {
        self::$isRequestInitialized = false;
        self::$dataTemplates = null;
        self::$customFields = null;
        self::$formatsById = null;
        self::$virtualFormatId = null;
        self::$hybridFormatId = null;
        self::$hiddenFormatIds = null;
        self::$temporarilyClosedFormatId = null;
    }

    public function toArray($request)
    {
        if (!self::$isRequestInitialized) {
            $meetingRepository = new MeetingRepository();
            self::$dataTemplates = $meetingRepository
                ->getDataTemplates()
                ->reject(fn ($template, $_) => $template->key == 'meeting_name');
            self::$customFields = $meetingRepository->getCustomFields();
            $formatRepository = new FormatRepository();
            self::$formatsById = $formatRepository->getAsTranslations()->mapWithKeys(fn ($fmt) => [$fmt->shared_id_bigint => $fmt]);
            self::$virtualFormatId = $formatRepository->getVirtualFormat()->shared_id_bigint;
            self::$hybridFormatId = $formatRepository->getHybridFormat()->shared_id_bigint;
            self::$temporarilyClosedFormatId = $formatRepository->getTemporarilyClosedFormat()->shared_id_bigint;
            self::$hiddenFormatIds = collect([self::$virtualFormatId, self::$hybridFormatId, self::$temporarilyClosedFormatId]);
            self::$isRequestInitialized = true;
        }

        $meetingData = $this->data
            ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])
            ->toBase()
            ->merge(
                $this->longdata
                    ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])
                    ->toBase()
            );

        $formatIds = empty($this->formats) ? collect([]) : collect(explode(',', $this->formats))
            ->map(fn ($id) => intval($id))
            ->reject(fn ($id) => !self::$formatsById->has($id))
            ->sort();

        return array_merge(
            [
                'id' => $this->id_bigint,
                'serviceBodyId' => $this->service_body_bigint,
                'formatIds' => $formatIds->reject(fn ($id) => self::$hiddenFormatIds->contains($id))->toArray(),
                'venueType' => $this->venue_type,
                'temporarilyVirtual' => $this->venue_type == Meeting::VENUE_TYPE_VIRTUAL && $formatIds->contains(self::$temporarilyClosedFormatId),
                'day' => $this->weekday_tinyint,
                'startTime' => is_null($this->start_time) ? null : (\DateTime::createFromFormat('H:i:s', $this->start_time) ?: \DateTime::createFromFormat('H:i', $this->start_time))->format('H:i'),
                'duration' => is_null($this->duration_time) ? null : (\DateTime::createFromFormat('H:i:s', $this->duration_time) ?: \DateTime::createFromFormat('H:i', $this->duration_time))->format('H:i'),
                'timeZone' => $this->time_zone ?: null,
                'latitude' => $this->latitude ?? null,
                'longitude' => $this->longitude ?? null,
                'published' => $this->published === 1,
                'email' => $this->email_contact ?: null,
                'worldId' => $this->worldid_mixed ?: null,
                'name' => $meetingData->get('meeting_name') ?: null,
            ],
            self::$dataTemplates
                ->reject(fn ($t, $_) => self::$customFields->contains($t->key))
                ->mapWithKeys(fn ($t, $_) => [$t->key => $meetingData->get($t->key) ?: null])
                ->toArray(),
            [
                'customFields' => self::$dataTemplates
                    ->reject(fn ($t, $_) => !self::$customFields->contains($t->key))
                    ->mapWithKeys(fn ($t, $_) => [$t->key => $meetingData->get($t->key) ?: null])
                    ->toArray()
            ],
        );
    }
}
