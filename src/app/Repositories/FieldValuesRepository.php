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
            $meetingIdsByValue = [];
            $meetings = Meeting::all();
            foreach ($meetings as $meeting) {
                $value = $meeting->{$fieldName};

                if ($fieldName == 'worldid_mixed' && $value) {
                    $value = trim($value);
                }

                if (array_key_exists($value, $meetingIdsByValue)) {
                    $meetingIdsByValue[$value][] = $meeting->id_bigint;
                } else {
                    $meetingIdsByValue[$value] = [$meeting->id_bigint];
                }
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
                        $newMeetingIdsByValue[$value] = array_merge($meetingIds, $existingMeetingIds);
                    } else {
                        $newMeetingIdsByValue[$value] = $meetingIds;
                    }
                }
                $meetingIdsByValue = $newMeetingIdsByValue;
            }

            foreach ($meetingIdsByValue as $value => $meetingIds) {
                $value = $value == '' ? 'NULL' : strval($value);
                sort($meetingIds);
                $fieldValues[] = [$fieldName => $value, 'ids' => implode(',', $meetingIds)];
            }
        } else {
            $fieldValues = [];
        }

        return collect($fieldValues);
    }
}
