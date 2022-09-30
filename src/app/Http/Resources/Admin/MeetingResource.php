<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Repositories\MeetingRepository;
use App\Repositories\FormatRepository;
use Illuminate\Support\Collection;

class MeetingResource extends JsonResource
{
    private static bool $isRequestInitialized = false;
    private static ?Collection $extraFieldsTemplates = null;
    private static ?Collection $formatsById = null;
    private static ?int $virtualFormatId = null;
    private static ?int $hybridFormatId = null;
    private static ?int $temporarilyClosedFormatId = null;
    private static ?Collection $hiddenFormatIds = null;

    public static function resetStaticVariables()
    {
        self::$isRequestInitialized = false;
        self::$extraFieldsTemplates = null;
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
            self::$extraFieldsTemplates = $meetingRepository
                ->getDataTemplates()
                ->reject(fn ($template, $_) => in_array($template->key, MeetingData::STOCK_FIELDS));
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

        return array_merge([
            'id' => $this->id_bigint,
            'serviceBodyId' => $this->service_body_bigint,
            'formatIds' => $formatIds->reject(fn ($id) => self::$hiddenFormatIds->contains($id))->toArray(),
            'venueType' => $this->venue_type,
            'temporarilyVirtual' => $this->venue_type == Meeting::VENUE_TYPE_VIRTUAL && $formatIds->contains(self::$temporarilyClosedFormatId),
            'day' => $this->weekday_tinyint,
            'startTime' => (\DateTime::createFromFormat('H:i:s', $this->start_time) ?: \DateTime::createFromFormat('H:i', $this->start_time))->format('H:i'),
            'duration' => (\DateTime::createFromFormat('H:i:s', $this->duration_time) ?: \DateTime::createFromFormat('H:i', $this->duration_time))->format('H:i'),
            'timeZone' => $this->time_zone ?: null,
            'latitude' => $this->latitude ?? null,
            'longitude' => $this->longitude ?? null,
            'published' => $this->published === 1,
            'email' => $this->email_contact ?: null,
            'worldId' => $this->worldid_mixed ?: null,
            'name' => $meetingData->get('meeting_name') ?: null,
            'location_text' => $meetingData->get('location_text') ?: null,
            'location_info' => $meetingData->get('location_info') ?: null,
            'location_street' => $meetingData->get('location_street') ?: null,
            'location_neighborhood' => $meetingData->get('location_neighborhood') ?: null,
            'location_city_subsection' => $meetingData->get('location_city_subsection') ?: null,
            'location_municipality' => $meetingData->get('location_municipality') ?: null,
            'location_sub_province' => $meetingData->get('location_sub_province') ?: null,
            'location_province' => $meetingData->get('location_province') ?: null,
            'location_postal_code_1' => $meetingData->get('location_postal_code_1') ?: null,
            'location_nation' => $meetingData->get('location_nation') ?: null,
            'phone_meeting_number' => $meetingData->get('phone_meeting_number') ?: null,
            'virtual_meeting_link' => $meetingData->get('virtual_meeting_link') ?: null,
            'virtual_meeting_additional_info' => $meetingData->get('virtual_meeting_additional_info') ?: null,
            'contact_name_1' => $meetingData->get('contact_name_1') ?: null,
            'contact_name_2' => $meetingData->get('contact_name_2') ?: null,
            'contact_phone_1' => $meetingData->get('contact_phone_1') ?: null,
            'contact_phone_2' => $meetingData->get('contact_phone_2') ?: null,
            'contact_email_1' => $meetingData->get('contact_email_1') ?: null,
            'contact_email_2' => $meetingData->get('contact_email_2') ?: null,
            'bus_lines' => $meetingData->get('bus_lines') ?: null,
            'train_lines' => $meetingData->get('train_lines') ?: null,
            'comments' => $meetingData->get('comments') ?: null,
        ], self::$extraFieldsTemplates->mapWithKeys(fn ($t, $_) => [$t->key => $meetingData->get($t->key) ?: null])->toArray());
    }
}
