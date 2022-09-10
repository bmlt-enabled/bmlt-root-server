<?php

namespace App\Http\Resources;

use App\Models\Format;
use App\Models\ServiceBody;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingChangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $jsonData = $this->getJsonData();
        $langEnum = isset($jsonData['after']) ? $jsonData['after']['lang_enum'] ?? null : null;
        if (!$langEnum) {
            $langEnum = isset($jsonData['before']) ? $jsonData['before']['lang_enum'] ?? 'en' : 'en';
        }

        return [
            'date_int' => strval(strtotime($this->change_date)),
            'date_str' => date('g:i A, n/j/Y', strtotime($this->change_date)),
            'change_type' => $this->change_type_enum,
            'change_id' => strval($this->id_bigint),
            'meeting_id' => strval($this->before_id_bigint ?? $this->after_id_bigint ?? 0),
            'meeting_name' => $this->beforeMeeting?->getName() ?? $this->afterMeeting?->getName() ?? '',
            'user_id' => strval($this->user_id_bigint),
            'user_name' => $this->user?->name_string ?? '',
            'service_body_id' => strval($this->service_body_id_bigint),
            'service_body_name' => $this->serviceBody?->name_string ?? '',
            'meeting_exists' => ($this->before_object || $this->after_object) ? '1' : '0',
            'details' => $this->getChangeDetails($langEnum),
            'json_data' => $jsonData,
        ];
    }

    private function getJsonData()
    {
        $ret = [];

        if ($this->before_object) {
            $ret['before'] = $this->getMeetingJsonData($this->before_object);
        }

        if ($this->after_object) {
            $ret['after'] = $this->getMeetingJsonData($this->after_object);
        }

        return $ret;
    }

    private function getMeetingJsonData($meeting): array
    {
        $ret = [];

        $mainValues = $meeting['main_table_values'] ?? null;
        if ($mainValues) {
            $ret['id_bigint'] = strval($mainValues['id_bigint'] ?? '');
            $ret['service_body_bigint'] = strval($mainValues['service_body_bigint'] ?? '');
            $ret['weekday_tinyint'] = strval($mainValues['weekday_tinyint'] ?? '');
            $ret['venue_type'] = strval($mainValues['venue_type'] ?? '');
            $ret['start_time'] = $mainValues['start_time'] ?? '';
            $ret['lang_enum'] = $mainValues['lang_enum'] ?? '';
            $ret['duration_time'] = $mainValues['duration_time'] ?? '';
            $ret['longitude'] = strval($mainValues['longitude'] ?? '');
            $ret['latitude'] = strval($mainValues['latitude'] ?? '');
            $ret['published'] = strval($mainValues['published'] ?? '');
            $formatIds = $mainValues['formats'];
            if ($formatIds && $formatIds != '') {
                $formatIds = explode(',', $mainValues['formats'] ?? '');
                $langEnum = $ret['lang_enum'] ?: 'en';
                $formatKeys = $this->getFormatKeys($formatIds, $langEnum);
                $ret['formats'] = implode(', ', $formatKeys);
            } else {
                $ret['formats'] = '';
            }
        }

        $dataTableValues = [];
        foreach (($meeting['data_table_values'] ?? []) as $data) {
            if (isset($data['key']) && $data['key'] == 'root_server_uri') {
                continue;
            }
            if (!isset($data['data_string'])) {
                continue;
            }
            $dataTableValues[$data['key']] = $data['data_string'];
        }

        return collect($ret)->merge($dataTableValues)->toArray();
    }

    private function getFormatKeys($formatIds, $langEnum)
    {
        static $allFormats;
        if (!$allFormats) {
            $allFormats = Format::all()->groupBy(['shared_id_bigint', 'lang_enum'], preserveKeys: true);
        }

        $formatKeys = [];
        foreach ($formatIds as $formatId) {
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

    public function getChangeDetails(string $langEnum): string
    {
        $objectType = self::$objectClassToStrMap[$this->object_class_string];
        $changeType = self::$changeTypeToStrMap[$this->change_type_enum];

        if ($objectType != 'meeting' || $changeType != 'changed') {
            $translationKey = 'change_type.' . $objectType . '_' . $changeType;
            $translation = __($translationKey);
            return $translation == $translationKey ? '' : $translation;
        }

        if (!$this->before_object || !$this->after_object) {
            return '';
        }

        $beforeValues = collect($this->before_object['main_table_values'] ?? [])->merge(
            collect($this->before_object['data_table_values'] ?? [])
                ->mapWithKeys(fn ($data) => [$data['key'] => $data])
        );

        $afterValues = collect($this->after_object['main_table_values'] ?? [])->merge(
            collect($this->after_object['data_table_values'] ?? [])
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
                if ($beforeValue != '') {
                    $beforeValue = explode(',', $beforeValue);
                    $beforeValue = $this->getFormatKeys($beforeValue, $langEnum);
                    $beforeValue = implode(', ', $beforeValue);
                }
                if ($afterValue != '') {
                    $afterValue = explode(',', $afterValue);
                    $afterValue = $this->getFormatKeys($afterValue, $langEnum);
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
                    static $allServiceBodies;
                    if (!$allServiceBodies) {
                        $allServiceBodies = ServiceBody::all()->mapWithKeys(fn ($sb) => [$sb->id_bigint => $sb]);
                    }
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
}
