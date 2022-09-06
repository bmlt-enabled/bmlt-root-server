<?php

namespace App\Repositories;

use App\Interfaces\FieldValuesRepositoryInterface;
use App\Models\Meeting;
use App\Models\MeetingData;
use Illuminate\Support\Collection;

class FieldValuesRepository implements FieldValuesRepositoryInterface
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

    public function getFieldValues(string $fieldName, array $specificFormats = [], bool $allFormats = false): Collection
    {
        $fieldValues = [];

        if (in_array($fieldName, FieldValuesRepository::$mainFields)) {
            $meetingIdsByValue = Meeting::all()
                ->mapToGroups(function ($meeting, $key) use ($fieldName) {
                    $value = $meeting->{$fieldName};
                    $value = $fieldName == 'worldid_mixed' && $value ? trim($value) : $value;
                    return [$value => (string)$meeting->id_bigint];
                });
        } else {
            $meetingIdsByValue = MeetingData::query()
                ->where('key', $fieldName)
                ->whereNot('meetingid_bigint', 0)
                ->whereNot('visibility', 1)
                ->get()
                ->mapToGroups(function ($meetingData, $key) use ($fieldName) {
                    $value = $meetingData->data_string;
                    return [$value => (string)$meetingData->meetingid_bigint];
                });
        }

        if ($fieldName == 'formats' && $specificFormats) {
            $newMeetingIdsByValue = [];
            foreach ($meetingIdsByValue as $value => $meetingIds) {
                if ($value == 'NULL') {
                    continue;
                }

                $formatIds = explode(',', $value);
                $commonFormatIds = array_intersect($formatIds, $specificFormats);
                if (!$commonFormatIds) {
                    continue;
                }

                if ($allFormats) {
                    if (array_diff($specificFormats, $commonFormatIds)) {
                        continue;
                    }
                }

                sort($commonFormatIds, SORT_NUMERIC);

                $value = implode(',', $commonFormatIds);

                if (array_key_exists($value, $newMeetingIdsByValue)) {
                    $existingMeetingIds = $newMeetingIdsByValue[$value];
                    $newMeetingIdsByValue[$value] = $existingMeetingIds->merge($meetingIds);
                } else {
                    $newMeetingIdsByValue[$value] = $meetingIds;
                }
            }
            $meetingIdsByValue = $newMeetingIdsByValue;
        }

        foreach ($meetingIdsByValue as $value => $meetingIds) {
            $value = $value == '' ? 'NULL' : strval($value);
            $meetingIds->sort(SORT_NUMERIC);
            $fieldValues[] = [$fieldName => $value, 'ids' => $meetingIds->join(',')];
        }

        return collect($fieldValues);
    }
}
