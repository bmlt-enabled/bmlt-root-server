<?php

namespace App\Http\Resources;

use App\Models\Format;
use App\Models\ServiceBody;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class MeetingChangeResource extends JsonResource
{
    private static bool $areFormatsLoaded = false;
    private static Collection $allFormats;

    private static bool $areServiceBodiesLoaded = false;
    private static Collection $allServiceBodies;

    private bool $isBeforeObjectLoaded = false;
    private ?array $cachedBeforeObject;

    private bool $isAfterObjectLoaded = false;
    private ?array $cachedAfterObject;

    private static $objectClassToStrMap = [
        'c_comdef_meeting' => 'meeting',
        'c_comdef_format' => 'format',
        'c_comdef_user' => 'user',
        'c_comdef_service_body'=> 'service_body',
    ];

    private static $changeTypeToStrMap = [
        'comdef_change_type_new' => 'created',
        'comdef_change_type_delete' => 'deleted',
        'comdef_change_type_change' => 'changed',
        'comdef_change_type_rollback' => 'rolled_back',
    ];

    public function toArray($request)
    {
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

    private function getBeforeObject(): ?array
    {
        if (!$this->isBeforeObjectLoaded) {
            $this->cachedBeforeObject = $this->before_object;
            $this->isBeforeObjectLoaded = true;
        }

        return $this->cachedBeforeObject;
    }

    private function getAfterObject(): ?array
    {
        if (!$this->isAfterObjectLoaded) {
            $this->cachedAfterObject = $this->after_object;
            $this->isAfterObjectLoaded = true;
        }

        return $this->cachedAfterObject;
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


    public function getChangeDetailsString(): string
    {
        $objectType = self::$objectClassToStrMap[$this->object_class_string];
        $changeType = self::$changeTypeToStrMap[$this->change_type_enum];

        if ($objectType != 'meeting' || $changeType != 'changed') {
            $translationKey = 'change_type.' . $objectType . '_' . $changeType;
            $translation = __($translationKey);
            return $translation == $translationKey ? '' : $translation;
        }

        $beforeObject = $this->getBeforeObject();
        $afterObject = $this->getAfterObject();
        if (!$beforeObject || !$afterObject) {
            return '';
        }

        $beforeValues = collect($beforeObject['main_table_values'] ?? [])->merge(
            collect($beforeObject['data_table_values'] ?? [])
                ->mapWithKeys(fn ($data) => [$data['key'] => $data])
        );

        $afterValues = collect($afterObject['main_table_values'] ?? [])->merge(
            collect($afterObject['data_table_values'] ?? [])
                ->mapWithKeys(fn ($data) => [$data['key'] => $data])
        );

        $seenKeys = [];
        $changeStrings = [];
        foreach ($beforeValues->keys()->merge($afterValues->keys()) as $key) {
            if ($key == 'root_server_uri') {
                continue;
            }

            if (!array_key_exists($key, $seenKeys)) {
                $seenKeys[$key] = null;
            } else {
                continue;
            }

            $fieldName = $key;
            $isVisible = true;
            $beforeValue = $beforeValues->get($key);
            $afterValue = $afterValues->get($key);
            if (is_array($afterValue)) {
                $isVisible = $afterValue['visibility'] !== 1;
                $fieldName = $afterValue['field_prompt'] ?? $fieldName;
                $afterValue = $afterValue['data_string'] ?? null;
            }
            if (is_array($beforeValue)) {
                if ($isVisible) {
                    $isVisible = $beforeValue['visibility'] !== 1;
                }
                $fieldName = $beforeValue['field_prompt'] ?? $fieldName;
                $beforeValue = $beforeValue['data_string'] ?? null;
            }

            if (!is_null($beforeValue) && is_null($afterValue)) {
                if ($key == 'published') {
                    $changeStrings[] = $fieldName . ' ' . __('change_detail.was_unpublished') . '.';
                    continue;
                }
                $changeStrings[] = $fieldName . ' ' . __('change_detail.was_deleted') . '.';
            } elseif (is_null($beforeValue) && !is_null($afterValue)) {
                if (!$isVisible) {
                    $changeStrings[] = $fieldName . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'published') {
                    $changeStrings[] = (int)$afterValue != 0 ?  $fieldName . ' ' . __('change_detail.was_published') . '.' : null;
                    continue;
                } elseif ($key == 'email_contact') {
                    $changeStrings[] = $fieldName . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'start_time' || $key == 'duration_time') {
                    $fieldName = __("change_detail.$key");
                    $afterValue = explode(':', $afterValue);
                    $afterValue = (intval($afterValue[0]) * 100) + intval($afterValue[1]);
                }
                $changeStrings[] = $fieldName . ' ' . __('change_detail.was_added_as') . ' "' . $afterValue . '".';
            } elseif ($key == 'formats') {
                $langEnum = $beforeValues->get('lang_enum');
                if (is_null($langEnum)) {
                    $langEnum = $afterValues->get('lang_enum', 'en');
                }
                if ($beforeValue != '') {
                    $beforeValue = explode(',', $beforeValue);
                    $beforeValue = $this->convertFormatIdsToFormatKeys($beforeValue, $langEnum);
                    $beforeValue = implode(', ', $beforeValue);
                }
                if ($afterValue != '') {
                    $afterValue = explode(',', $afterValue);
                    $afterValue = $this->convertFormatIdsToFormatKeys($afterValue, $langEnum);
                    $afterValue = implode(', ', $afterValue);
                }
                if ($beforeValue != $afterValue) {
                    $changeStrings[] = __('change_detail.formats_prompt') . ' ' . __('change_detail.was_changed_from') . ' "' . $beforeValue . '" ' . __('change_detail.to') . ' "' . $afterValue . '".';
                }
            } elseif ($beforeValue != $afterValue) {
                if (!$isVisible) {
                    $changeStrings[] = $fieldName . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'published') {
                    $changeStrings[] =  (int)$afterValue != 0 ? __('change_detail.was_published') . '.' : __('change_detail.was_unpublished') . '.';
                    continue;
                } elseif ($key == 'email_contact') {
                    $changeStrings[] = $fieldName . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'weekday_tinyint') {
                    $intToDay = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    $beforeValue = $intToDay[max(0, min(6, intval($beforeValue)))];
                    $beforeValue = __("weekdays.$beforeValue");
                    $afterValue = $intToDay[max(0, min(6, intval($afterValue)))];
                    $afterValue = __("weekdays.$afterValue");
                } elseif ($key == 'service_body_bigint') {
                    $allServiceBodies = $this->getAllServiceBodies();
                    $beforeValue = $allServiceBodies->get((int)$beforeValue);
                    $beforeValue = $beforeValue?->name_string ?? __('change_detail.non_existent_service_body');
                    $afterValue = $allServiceBodies->get((int)$afterValue);
                    $afterValue = $afterValue?->name_string ?? __('change_detail.non_existent_service_body');
                    $fieldName = __('change_detail.sb_prompt');
                    $changeStrings[] = $fieldName . ' ' . $beforeValue . ' ' . __('change_detail.to') . ' ' . $afterValue . '.';
                    continue;
                } elseif ($key == 'longitude' || $key == 'latitude') {
                    $beforeValue = floatval($beforeValue);
                    $afterValue = floatval($afterValue);
                } elseif ($key == 'start_time' || $key == 'duration_time') {
                    // idk... copied from the old code...
                    $beforeValue = explode(':', $beforeValue);
                    $beforeValue = strval(intval($beforeValue[0])).':'.(intval($beforeValue[1]) < 10 ? '0' : '').strval(intval($beforeValue[1]));
                    $afterValue = explode(':', $afterValue);
                    $afterValue = strval(intval($afterValue[0])).':'.(intval($afterValue[1]) < 10 ? '0' : '').strval(intval($afterValue[1]));
                    if ($beforeValue == $afterValue) {  // ???
                        continue;
                    }
                }
                $changeStrings[] = $fieldName . ' ' . __('change_detail.was_changed_from') . ' "' . $beforeValue . '" ' . __('change_detail.to') . ' "' . $afterValue . '".';
            }
        }

        return implode(' ', $changeStrings);
    }

    private function convertFormatIdsToFormatKeys($formatIds, $langEnum): array
    {
        $formatKeys = [];
        foreach ($formatIds as $formatId) {
            $allFormats = $this->getAllFormats();
            $formatsByLanguage = $allFormats->get(intval($formatId), collect([]));
            $format = $formatsByLanguage->get($langEnum);
            if ($format) {
                $format = $format->first();
                if ($format) {
                    $formatKeys[] = $format->key_string;
                }
            }
        }

        $formatKeys = array_unique($formatKeys);
        asort($formatKeys);
        return $formatKeys;
    }

    private function getAllFormats(): Collection
    {
        if (!self::$areFormatsLoaded) {
            self::$allFormats = Format::all()->groupBy(['shared_id_bigint', 'lang_enum'], preserveKeys: true);
            self::$areFormatsLoaded = true;
        }

        return self::$allFormats;
    }

    private function getAllServiceBodies(): Collection
    {
        if (!self::$areServiceBodiesLoaded) {
            self::$allServiceBodies = ServiceBody::all()->mapWithKeys(fn ($sb) => [$sb->id_bigint => $sb]);
            self::$areServiceBodiesLoaded = true;
        }

        return self::$allServiceBodies;
    }
}
