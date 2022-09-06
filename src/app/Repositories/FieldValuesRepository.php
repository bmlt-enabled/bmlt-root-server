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
        if (in_array($fieldName, FieldValuesRepository::$mainFields)) {
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
