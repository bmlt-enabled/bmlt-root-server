<?php

namespace App\Http\Resources\Query;

use App\Http\Resources\JsonResource;
use App\Repositories\FormatRepository;
use App\Repositories\ServiceBodyRepository;
use App\Http\Resources\Traits\ChangeDetailsTrait;
use Illuminate\Support\Collection;

class MeetingChangeResource extends JsonResource
{
    use ChangeDetailsTrait;
    private static bool $isRequestInitialized = false;
    private static Collection $allFormats;
    private static Collection $allServiceBodies;

    private bool $isBeforeObjectLoaded = false;
    private ?array $cachedBeforeObject;

    private bool $isAfterObjectLoaded = false;
    private ?array $cachedAfterObject;

    private static array $objectClassToStrMap = [
        'c_comdef_meeting' => 'meeting',
        'c_comdef_format' => 'format',
        'c_comdef_user' => 'user',
        'c_comdef_service_body'=> 'service_body',
    ];

    private static array $changeTypeToStrMap = [
        'comdef_change_type_new' => 'created',
        'comdef_change_type_delete' => 'deleted',
        'comdef_change_type_change' => 'changed',
        'comdef_change_type_rollback' => 'rolled_back',
    ];

    // Allows tests to reset state
    public static function resetStaticVariables()
    {
        self::$isRequestInitialized = false;
    }

    public function toArray($request)
    {
        if (!self::$isRequestInitialized) {
            $this->initializeRequest($request);
            self::$isRequestInitialized = true;
        }

        return [
            'date_int' => strval(strtotime($this->change_date)),
            'date_string' => date('g:i A, n/j/Y', strtotime($this->change_date)),
            'change_type' => $this->change_type_enum,
            'change_id' => strval($this->id_bigint),
            'meeting_id' => strval($this->before_id_bigint ?? $this->after_id_bigint ?? 0),
            'meeting_name' => $this->beforeMeeting?->getName() ?? $this->afterMeeting?->getName() ?? '',
            'user_id' => strval($this->user_id_bigint),
            'user_name' => $this->user?->name_string ?? '',
            'service_body_id' => strval($this->service_body_id_bigint),
            'service_body_name' => $this->serviceBody?->name_string ?? '',
            'meeting_exists' => $this->getAfterObject() ? '1' : '0',
            'details' => $this->getChangeDetailsString(),
            'json_data' => $this->getJsonDataArray(),
        ];
    }

    private function initializeRequest()
    {
        $formatRepository = new FormatRepository();
        self::$allFormats = $formatRepository->search(showAll: true)->groupBy(['shared_id_bigint', 'lang_enum'], preserveKeys: true);

        $serviceBodyRepository = new ServiceBodyRepository();
        self::$allServiceBodies = $serviceBodyRepository->search()->mapWithKeys(fn ($sb) => [$sb->id_bigint => $sb]);
    }

    private function getJsonDataArray(): array
    {
        $ret = [];

        $beforeObject = $this->getBeforeObject();
        if ($beforeObject) {
            $ret['before'] = $this->convertObjectToArray($beforeObject);
        }

        $afterObject = $this->getAfterObject();
        if ($afterObject) {
            $ret['after'] = $this->convertObjectToArray($afterObject);
        }

        return $ret;
    }

    private function convertObjectToArray($meetingObject): array
    {
        $ret = collect([]);

        $mainValues = $meetingObject['main_table_values'] ?? null;
        if ($mainValues) {
            $idBigint = $mainValues['id_bigint'] ?? null;
            if (!is_null($idBigint)) {
                $ret->put('id_bigint', (string)$idBigint);
            }

            $serviceBodyBigint = $mainValues['service_body_bigint'] ?? null;
            if (!is_null($serviceBodyBigint)) {
                $ret->put('service_body_bigint', (string)$serviceBodyBigint);
            }

            $weekdayTinyint = $mainValues['weekday_tinyint'] ?? null;
            if (!is_null($weekdayTinyint)) {
                $ret->put('weekday_tinyint', (string)($weekdayTinyint + 1));
            }

            $venueType = $mainValues['venue_type'] ?? null;
            if (!is_null($venueType)) {
                $ret->put('venue_type', (string)$venueType);
            }

            $startTime = $mainValues['start_time'] ?? null;
            if (!is_null($startTime)) {
                $ret->put('start_time', (string)$startTime);
            }

            $langEnum = $mainValues['lang_enum'] ?? null;
            if (!is_null($langEnum)) {
                $ret->put('lang_enum', (string)$langEnum);
            }

            $durationTime = $mainValues['duration_time'] ?? null;
            if (!is_null($durationTime)) {
                $ret->put('duration_time', (string)$durationTime);
            }

            $longitude = $mainValues['longitude'] ?? null;
            if (!is_null($longitude)) {
                $ret->put('longitude', (string)$longitude);
            }

            $latitude = $mainValues['latitude'] ?? null;
            if (!is_null($latitude)) {
                $ret->put('latitude', (string)$latitude);
            }

            $worldIdMixed = $mainValues['worldid_mixed'] ?? null;
            if (!is_null($worldIdMixed)) {
                $ret->put('worldid_mixed', (string)$worldIdMixed);
            }

            $timeZone = $mainValues['time_zone'] ?? null;
            if (!is_null($timeZone)) {
                $ret->put('time_zone', (string)$timeZone);
            }

            $published = $mainValues['published'] ?? null;
            if (!is_null($published)) {
                $ret->put('published', (string)$published);
            }

            $formats = $mainValues['formats'];
            if (!is_null($formats) && $formats != '') {
                $formats = explode(',', $mainValues['formats'] ?? '');
                $formatKeys = $this->convertFormatIdsToFormatKeys($formats, $langEnum ?? 'en');
                $ret->put('formats', $formatKeys);
            }
        }

        $dataTableValues = $meetingObject['data_table_values'] ?? [];
        foreach ($dataTableValues as $data) {
            if (isset($data['key']) && $data['key'] == 'root_server_uri') {
                continue;
            }
            if (!isset($data['data_string'])) {
                continue;
            }
            $ret->put($data['key'], $data['data_string']);
        }

        return $ret->toArray();
    }
}
