<?php

namespace App\Http\Resources\Traits;

use Illuminate\Support\Collection;

trait ChangeDetailsTrait
{
    private static Collection $allFormats;
    private static Collection $allServiceBodies;

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

    public function getChangeDetails(bool $asArray = false): string|array
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
            $fieldPrompt = $key;
            $beforeValue = $beforeValues->get($key);
            $afterValue = $afterValues->get($key);
            if (is_array($afterValue)) {
                $isVisible = $afterValue['visibility'] !== 1;
                $fieldPrompt = $afterValue['field_prompt'] ?? $fieldName;
                $afterValue = $afterValue['data_string'] ?? null;
            }
            if (is_array($beforeValue)) {
                if ($isVisible) {
                    $isVisible = $beforeValue['visibility'] !== 1;
                }
                if ($fieldPrompt == $key) {
                    $fieldPrompt = $beforeValue['field_prompt'] ?? $fieldName;
                }
                $beforeValue = $beforeValue['data_string'] ?? null;
            }
            if ($fieldPrompt == $key) {
                $translatedPrompt = __("change_detail.$key");
                if ($translatedPrompt != "change_detail.$key") {
                    $fieldPrompt = $translatedPrompt;
                }
            }

            if (!is_null($beforeValue) && is_null($afterValue)) {
                if ($key == 'published') {
                    $changeStrings[] = __('change_detail.was_unpublished') . '.';
                    continue;
                }
                $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_deleted') . '.';
            } elseif (is_null($beforeValue) && !is_null($afterValue)) {
                if (!$isVisible) {
                    $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'published') {
                    if ((int)$afterValue != 0) {
                        $changeStrings[] = __('change_detail.was_published') . '.';
                    }
                    continue;
                } elseif ($key == 'email_contact') {
                    $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'start_time' || $key == 'duration_time') {
                    $afterValue = explode(':', $afterValue);
                    $afterValue = (intval($afterValue[0]) * 100) + intval($afterValue[1]);
                }
                $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_added_as') . ' "' . $afterValue . '".';
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
                    $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'published') {
                    $changeStrings[] =  (int)$afterValue != 0 ? __('change_detail.was_published') . '.' : __('change_detail.was_unpublished') . '.';
                    continue;
                } elseif ($key == 'email_contact') {
                    $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_changed') . '.';
                    continue;
                } elseif ($key == 'weekday_tinyint') {
                    $intToDay = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                    $beforeValue = $intToDay[max(0, min(6, intval($beforeValue)))];
                    $beforeValue = __("weekdays.$beforeValue");
                    $afterValue = $intToDay[max(0, min(6, intval($afterValue)))];
                    $afterValue = __("weekdays.$afterValue");
                } elseif ($key == 'service_body_bigint') {
                    $beforeValue = self::$allServiceBodies->get((int)$beforeValue);
                    $beforeValue = $beforeValue?->name_string ?? __('change_detail.non_existent_service_body');
                    $afterValue = self::$allServiceBodies->get((int)$afterValue);
                    $afterValue = $afterValue?->name_string ?? __('change_detail.non_existent_service_body');
                    $changeStrings[] = __('change_detail.sb_prompt') . ' ' . $beforeValue . ' ' . __('change_detail.to') . ' ' . $afterValue . '.';
                    continue;
                } elseif ($key == 'longitude' || $key == 'latitude') {
                    $beforeValue = floatval($beforeValue);
                    $afterValue = floatval($afterValue);
                } elseif ($key == 'start_time' || $key == 'duration_time') {
                    $beforeValue = explode(':', $beforeValue);
                    $beforeValue = strval(intval($beforeValue[0])).':'.(intval($beforeValue[1]) < 10 ? '0' : '').strval(intval($beforeValue[1]));
                    $afterValue = explode(':', $afterValue);
                    $afterValue = strval(intval($afterValue[0])).':'.(intval($afterValue[1]) < 10 ? '0' : '').strval(intval($afterValue[1]));
                    if ($beforeValue == $afterValue) {
                        continue;
                    }
                }
                $changeStrings[] = $fieldPrompt . ' ' . __('change_detail.was_changed_from') . ' "' . $beforeValue . '" ' . __('change_detail.to') . ' "' . $afterValue . '".';
            }
        }

        return $asArray ? $changeStrings : implode(' ', $changeStrings);
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

    private function convertFormatIdsToFormatKeys($formatIds, $langEnum): array
    {
        $formatKeys = [];
        foreach ($formatIds as $formatId) {
            $formatsByLanguage = self::$allFormats->get(intval($formatId), collect([]));
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
}
