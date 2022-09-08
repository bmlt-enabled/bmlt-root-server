<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    protected $table = 'comdef_changes';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = [
        'user_id_bigint',
        'service_body_id_bigint',
        'lang_enum',
        'change_date',
        'object_class_string',
        'change_name_string',
        'change_description_text',
        'before_id_bigint',
        'before_lang_enum',
        'after_id_bigint',
        'after_lang_enum',
        'change_type_enum',
        'before_object',
        'after_object',
    ];

    public function getMeetingData($key)
    {
        return $this->meetingData->{$key} ?? null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id_bigint');
    }

    public function serviceBody()
    {
        return $this->belongsTo(ServiceBody::class, 'service_body_id_bigint');
    }

    public function beforeMeeting()
    {
        return $this->belongsTo(Meeting::class, 'before_id_bigint');
    }

    public function afterMeeting()
    {
        return $this->belongsTo(Meeting::class, 'after_id_bigint');
    }

    public function beforeObject(): Attribute
    {
        return Attribute::make(
            get: fn ($beforeObject) => $this->unserialize($beforeObject),
        );
    }

    public function afterObject(): Attribute
    {
        return Attribute::make(
            get: fn ($afterObject) => $this->unserialize($afterObject),
        );
    }

    private function unserialize($object)
    {
        if (!$object) {
            return null;
        }

        $ret = unserialize($object);
        $ret['main_table_values'] = unserialize($ret['main_table_values']);
        $ret['data_table_values'] = unserialize($ret['data_table_values']);
        $ret['longdata_table_values'] = unserialize($ret['longdata_table_values']);
        return $ret;
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

    public function getChangeDetails(): string
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
                continue;
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
                    if (!isset($allServiceBodies)) {
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
