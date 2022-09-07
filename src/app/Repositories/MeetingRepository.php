<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use App\Interfaces\MeetingRepositoryInterface;
use App\Models\Meeting;
use App\Models\MeetingData;

class MeetingRepository implements MeetingRepositoryInterface
{
    private static array $mainFields = [
        'id_bigint',
        'worldid_mixed',
        'service_body_bigint',
        'weekday_tinyint',
        'venue_type',
        'start_time',
        'duration_time',
        'time_zone',
        'formats',
        'lang_enum',
        'longitude',
        'latitude',
    ];

    public function getFieldKeys(): Collection
    {
        $data = [
            ['key' => 'id_bigint', 'description' => __('main_prompts.id_bigint')],
            ['key' => 'worldid_mixed', 'description' => __('main_prompts.worldid_mixed')],
            ['key' => 'service_body_bigint', 'description' => __('main_prompts.service_body_bigint')],
            ['key' => 'weekday_tinyint', 'description' => __('main_prompts.weekday_tinyint')],
            ['key' => 'venue_type', 'description' => __('main_prompts.venue_type')],
            ['key' => 'start_time', 'description' => __('main_prompts.start_time')],
            ['key' => 'duration_time', 'description' => __('main_prompts.duration_time')],
            ['key' => 'time_zone', 'description' => __('main_prompts.time_zone')],
            ['key' => 'formats', 'description' => __('main_prompts.formats')],
            ['key' => 'lang_enum', 'description' => __('main_prompts.lang_enum')],
            ['key' => 'longitude', 'description' => __('main_prompts.longitude')],
            ['key' => 'latitude', 'description' => __('main_prompts.latitude')],
        ];

        $langEnum = App::currentLocale();
        $fields = MeetingData::query()
            ->where('meetingid_bigint', 0)
            ->where('lang_enum', $langEnum)
            ->where(function (Builder $query) {
                $query->where('visibility', null)->orWhereNot('visibility', 1);
            })
            ->get();

        foreach ($fields as $field) {
            array_push($data, ['key' => $field->key, 'description' => $field->field_prompt]);
        }

        if ($langEnum != 'en') {
            $seenKeys = [];
            foreach ($data as $f) {
                $seenKeys[$f['key']] = null;
            }

            $fields = MeetingData::query()
                ->where('meetingid_bigint', 0)
                ->where('lang_enum', 'en')
                ->where(function ($query) {
                    $query->where('visibility', null)->orWhereNot('visibility', 1);
                })
                ->get();

            foreach ($fields as $field) {
                if (!array_key_exists($field->key, $seenKeys)) {
                    array_push($data, ['key' => $field->key, 'description' => $field->field_prompt]);
                }
            }
        }

        return collect($data);
    }

    public function getFieldValues(string $fieldName, array $specificFormats = [], bool $allFormats = false): Collection
    {
        if (in_array($fieldName, self::$mainFields)) {
            $meetingIdsByValue = Meeting::all()
                ->mapToGroups(function ($meeting, $key) use ($fieldName, $specificFormats, $allFormats) {
                    $value = $meeting->{$fieldName};
                    $value = $fieldName == 'worldid_mixed' && $value ? trim($value) : $value;

                    if ($fieldName == 'formats' && $specificFormats && $value) {
                        $meetingFormatIds = explode(',', $value);
                        $commonFormatIds = array_intersect($meetingFormatIds, $specificFormats);
                        if (!$commonFormatIds) {
                            return [null => (string)$meeting->id_bigint];
                        }

                        if ($allFormats) {
                            if (array_diff($specificFormats, $commonFormatIds)) {
                                return [null => (string)$meeting->id_bigint];
                            }
                        }

                        sort($commonFormatIds, SORT_NUMERIC);

                        $value = implode(',', $commonFormatIds);
                    }

                    return [strval($value) => (string)$meeting->id_bigint];
                })
                ->reject(function ($meetingIds, $value) use ($fieldName, $specificFormats) {
                    return $fieldName == 'formats' && $specificFormats && $value == '';
                });
        } else {
            $meetingIdsByValue = MeetingData::query()
                ->where('key', $fieldName)
                ->whereNot('meetingid_bigint', 0)
                ->where(function ($query) {
                    $query->where('visibility', null)->orWhereNot('visibility', 1);
                })
                ->get()
                ->mapToGroups(function ($meetingData, $key) use ($fieldName) {
                    $value = $meetingData->data_string;
                    return [$value => (string)$meetingData->meetingid_bigint];
                });
        }

        $fieldValues = [];
        foreach ($meetingIdsByValue as $value => $meetingIds) {
            $value = $value == '' ? 'NULL' : strval($value);
            $meetingIds->sort(SORT_NUMERIC);
            $fieldValues[] = [$fieldName => $value, 'ids' => $meetingIds->join(',')];
        }

        return collect($fieldValues);
    }
}
