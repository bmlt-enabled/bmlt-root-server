<?php

namespace App\Repositories;

use App\Interfaces\MeetingRepositoryInterface;
use App\Models\Change;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Repositories\External\ExternalMeeting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class MeetingRepository implements MeetingRepositoryInterface
{
    private string $sqlDistanceFormula = "? * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(latitude)) * COS(RADIANS(?)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(latitude)) * SIN(RADIANS(?)))))";

    public function getSearchResults(
        array $meetingIds = null,
        array $rootServersInclude = null,
        array $rootServersExclude = null,
        array $weekdaysInclude = null,
        array $weekdaysExclude = null,
        array $venueTypesInclude = null,
        array $venueTypesExclude = null,
        array $servicesInclude = null,
        array $servicesExclude = null,
        array $formatsInclude = null,
        array $formatsExclude = null,
        string $formatsComparisonOperator = 'AND',
        string $meetingKey = null,
        string $meetingKeyValue = null,
        string $startsAfter = null,
        string $startsBefore = null,
        string $endsBefore = null,
        string $minDuration = null,
        string $maxDuration = null,
        float $latitude = null,
        float $longitude = null,
        float $geoWidthMiles = null,
        float $geoWidthKilometers = null,
        bool $needsDistanceField = false,
        bool $sortResultsByDistance = false,
        string $searchString = null,
        ?bool $published = true,
        bool $eagerServiceBodies = false,
        bool $eagerRootServers = false,
        array $sortKeys = null,
        int $pageSize = null,
        int $pageNum = null,
    ): Collection {
        $eagerLoadRelations = ['data', 'longdata'];
        if ($eagerServiceBodies) {
            $eagerLoadRelations[] = 'serviceBody';
        }
        if ($eagerRootServers) {
            $eagerLoadRelations[] = 'rootServer';
        }

        $meetings = Meeting::with($eagerLoadRelations);

        if (!is_null($published)) {
            $meetings = $meetings->where('published', $published ? 1 : 0);
        }

        if (!is_null($meetingIds)) {
            $meetings = $meetings->whereIn('id_bigint', $meetingIds);
        }

        if (!is_null($rootServersInclude)) {
            $meetings = $meetings->whereIn('root_server_id', $rootServersInclude);
        }

        if (!is_null($rootServersExclude)) {
            $meetings = $meetings->whereNotIn('root_server_id', $rootServersExclude);
        }

        if (!is_null($weekdaysInclude)) {
            $meetings = $meetings->whereIn('weekday_tinyint', $weekdaysInclude);
        }

        if (!is_null($weekdaysExclude)) {
            $meetings = $meetings->whereNotIn('weekday_tinyint', $weekdaysExclude);
        }

        if (!is_null($venueTypesInclude)) {
            $meetings = $meetings->whereIn('venue_type', $venueTypesInclude);
        }

        if (!is_null($venueTypesExclude)) {
            $meetings = $meetings->whereNotIn('venue_type', $venueTypesExclude);
        }

        if (!is_null($servicesInclude)) {
            $meetings = $meetings->whereIn('service_body_bigint', $servicesInclude);
        }

        if (!is_null($servicesExclude)) {
            $meetings = $meetings->whereNotIn('service_body_bigint', $servicesExclude);
        }

        if (!is_null($formatsInclude)) {
            if ($formatsComparisonOperator == 'AND') {
                foreach ($formatsInclude as $formatId) {
                    $meetings = $meetings->where(function (Builder $query) use ($formatId) {
                        $query
                            ->orWhere('formats', "$formatId")
                            ->orWhere('formats', 'LIKE', "$formatId,%")
                            ->orWhere('formats', 'LIKE', "%,$formatId,%")
                            ->orWhere('formats', 'LIKE', "%,$formatId");
                    });
                }
            } else {
                $meetings = $meetings->where(function (Builder $query) use ($formatsInclude) {
                    foreach ($formatsInclude as $formatId) {
                        $query->orWhere(function (Builder $query) use ($formatId) {
                            $query
                                ->orWhere('formats', "$formatId")
                                ->orWhere('formats', 'LIKE', "$formatId,%")
                                ->orWhere('formats', 'LIKE', "%,$formatId,%")
                                ->orWhere('formats', 'LIKE', "%,$formatId");
                        });
                    }
                });
            }
        }

        if (!is_null($formatsExclude)) {
            foreach ($formatsExclude as $formatId) {
                $meetings = $meetings
                    ->whereNot('formats', "$formatId")
                    ->whereNot('formats', 'LIKE', "$formatId,%")
                    ->whereNot('formats', 'LIKE', "%,$formatId,%")
                    ->whereNot('formats', 'LIKE', "%,$formatId");
            }
        }

        if (!is_null($meetingKey) && !is_null($meetingKeyValue)) {
            if (in_array($meetingKey, Meeting::$mainFields)) {
                if ($meetingKey == 'formats' || $meetingKey == 'latitude' || $meetingKey == 'longitude') {
                    $meetings = $meetings->whereRaw('1 = 0');
                } else {
                    $meetings = $meetings->where($meetingKey, $meetingKeyValue);
                }
            } else {
                $meetings = $meetings->where(function (Builder $query) use ($meetingKey, $meetingKeyValue) {
                    $query
                        ->whereHas('data', function (Builder $query) use ($meetingKey, $meetingKeyValue) {
                            $query->where('key', $meetingKey)->where('data_string', $meetingKeyValue);
                        })
                        ->orWhereHas('longdata', function (Builder $query) use ($meetingKey, $meetingKeyValue) {
                            $query->where('key', $meetingKey)->where('data_blob', $meetingKeyValue);
                        });
                });
            }
        }

        if (!is_null($startsAfter)) {
            $meetings = $meetings->where('start_time', '>', $startsAfter);
        }

        if (!is_null($startsBefore)) {
            $meetings = $meetings->where('start_time', '<', $startsBefore);
        }

        if (!is_null($endsBefore)) {
            $endsBefore = explode(':', $endsBefore);
            $endsBefore = ($endsBefore[0] * 3600) + ($endsBefore[1] * 60);
            $meetings = $meetings->whereRaw("time_to_sec(start_time + duration_time) <= $endsBefore");
        }

        if (!is_null($maxDuration)) {
            $meetings = $meetings->where('duration_time', '<=', $maxDuration);
        }

        if (!is_null($minDuration)) {
            $meetings = $meetings->where('duration_time', '>=', $minDuration);
        }

        // handle full text searches
        if (!is_null($searchString)) {
            $moreMeetingIds = [];
            $searchString = trim($searchString);
            foreach (explode(',', $searchString) as $word) {
                if (is_numeric($word)) {
                    $moreMeetingIds[] = intval($word);
                } else {
                    $moreMeetingIds = [];
                    break;
                }
            }

            if (empty($moreMeetingIds)) {
                $meetings = $meetings->where(function (Builder $query) use ($searchString) {
                    $query
                        ->whereHas('data', function (Builder $query) use ($searchString) {
                            $query->whereFullText('data_string', $searchString);
                        })
                        ->orWhereHas('longdata', function (Builder $query) use ($searchString) {
                            $query->whereFullText('data_blob', $searchString);
                        });
                });
            } else {
                $meetings = $meetings->whereIn('id_bigint', $moreMeetingIds);
            }
        }

        // handle geographic searches - this has to come last, because we need all of the constraints when we clone the query
        $isGeographicSearch = !is_null($latitude) && !is_null($longitude) && (!is_null($geoWidthMiles) || !is_null($geoWidthKilometers) || $needsDistanceField);
        if ($isGeographicSearch) {
            $nNearest = null;
            $geoWidth = null;

            $useMiles = true;
            if (!is_null($geoWidthMiles)) {
                $geoWidth = $geoWidthMiles;
                if ($geoWidthMiles < 0) {
                    $nNearest = abs(intval($geoWidthMiles));
                }
            } elseif (!is_null($geoWidthKilometers)) {
                $useMiles = false;
                $geoWidth = $geoWidthKilometers;
                if ($geoWidthKilometers < 0) {
                    $nNearest = abs(intval($geoWidthKilometers));
                }
            }

            $milesMultiplier = 69.0;
            $kilometersMultiplier = 111.111;

            $distanceMultiplier = $useMiles ? $milesMultiplier : $kilometersMultiplier;

            if (!is_null($nNearest)) {
                // The idea here is to find the search radius that gives at least $nNearest meetings, which is slightly
                // different and more useful for map searches than just returning the $nNearest meetings.
                $geoWidth = $this->calculateSearchRadius($meetings, $nNearest, $latitude, $longitude, $distanceMultiplier);
            }

            $meetings = $meetings->selectRaw(
                "*, $this->sqlDistanceFormula as distance_in_miles, $this->sqlDistanceFormula as distance_in_km",
                [$milesMultiplier, $latitude, $longitude, $latitude, $kilometersMultiplier, $latitude, $longitude, $latitude],
            );

            if (!is_null($geoWidth)) {
                $meetings = $meetings->whereRaw("$this->sqlDistanceFormula <= ?", [$distanceMultiplier, $latitude, $longitude, $latitude, $geoWidth]);
            }

            if ($sortResultsByDistance) {
                $meetings = $meetings->orderByRaw($this->sqlDistanceFormula, [$distanceMultiplier, $latitude, $longitude, $latitude]);
            }
        }

        // paging
        if (!is_null($pageSize)) {
            $meetings = $meetings->limit($pageSize);
            if (!is_null($pageNum)) {
                $meetings = $meetings->offset(max(0, $pageNum - 1));
            }
        }

        // sort and return
        if (is_null($sortKeys) || ($isGeographicSearch && $sortResultsByDistance)) {
            // there are no sorts, or a sort by distance is already applied
            return $meetings->get();
        }

        if (empty(array_diff($sortKeys, Meeting::$mainFields))) {
            // all sorts can be done in sql
            foreach ($sortKeys as $sortKey) {
                $meetings = $meetings->orderBy($sortKey);
            }
            return $meetings->get();
        }

        // we have to sort in memory
        $mainTableKeys = [];
        foreach ($sortKeys as $sortKey) {
            if (in_array($sortKey, Meeting::$mainFields)) {
                $mainTableKeys[$sortKey] = null;
            }
        }

        $dataByMeeting = [];
        $meetings = $meetings->get();
        foreach ($meetings as $meeting) {
            $dataByMeeting[$meeting->id_bigint] = $meeting->data
                ->mapWithKeys(fn($data, $_) => [$data->key => $data->data_string])
                ->toBase()
                ->merge(
                    $meeting->longdata->mapWithKeys(fn($data, $_) => [$data->key => $data->data_blob])->toBase()
                );
        }

        return $meetings->sortBy([function ($m1, $m2) use ($sortKeys, $mainTableKeys, $dataByMeeting) {
            foreach ($sortKeys as $sortKey) {
                if (isset($mainTableKeys[$sortKey])) {
                    $field1 = $m1->{$sortKey};
                    $field2 = $m2->{$sortKey};
                } else {
                    $field1 = $dataByMeeting[$m1->id_bigint]?->get($sortKey);
                    $field2 = $dataByMeeting[$m2->id_bigint]?->get($sortKey);
                }

                // push nulls and empty strings to the end...
                if ((is_null($field1) || $field1 == '') && !is_null($field2) && $field2 != '') {
                    return 1;
                } elseif (!is_null($field1) && $field1 != '' && (is_null($field2) || $field2 == '')) {
                    return -1;
                }

                $comparison = $field1 <=> $field2;
                if ($comparison != 0) {
                    return $comparison;
                }
            }
            return 0;
        }]);
    }

    private function calculateSearchRadius(Builder $baseQuery, $numMeetings, float $latitude, float $longitude, float $distanceMultiplier): float
    {
        $radiuses = [0.0625, 0.125, 0.1875, 0.25, 0.4375, 0.5, 0.5625, 0.75, 0.8125, 1.0, 1.25, 1.5, 1.75, 2.0, 2.25, 2.5, 2.75, 3.0, 3.25, 3.5, 3.75, 4.0, 4.25, 4.5, 4.75, 5.0, 5.5, 6.0, 6.5, 7.0, 7.5, 8.0, 8.5, 9.0, 9.5, 10.0, 11.0, 12.0, 13.0, 14.0, 15.0, 17.5, 20.0, 22.5, 25.0, 27.5, 30.0, 35.0, 40.0, 45.0, 50.0, 60.0, 70.0, 80.0, 90.0, 100.0, 150, 200];
        $counts = [];
        $low = 0;
        $high = count($radiuses) - 1;
        while ($low <= $high) {
            $mid = floor(($low + $high) / 2);
            $radius = $radiuses[$mid];
            $count = DB::query()
                ->fromSub(
                    $baseQuery->clone()
                        ->selectRaw(
                            "$this->sqlDistanceFormula as distance",
                            [$distanceMultiplier, $latitude, $longitude, $latitude]
                        )
                        ->whereRaw(
                            'latitude BETWEEN ? - (? / ?) AND ? + (? / ?)',
                            [$latitude, $radius, $distanceMultiplier, $latitude, $radius, $distanceMultiplier]
                        )
                        ->whereRaw(
                            'longitude BETWEEN ? - (? / (? * COS(RADIANS(?)))) AND ? + (? / (? * COS(RADIANS(?))))',
                            [$longitude, $radius, $distanceMultiplier, $latitude, $longitude, $radius, $distanceMultiplier, $latitude]
                        ),
                    'd'
                )
                ->where('d.distance', '<=', $radius)
                ->count();

            $counts[$mid] = $count;

            if ($count < $numMeetings) {
                // see if we have the next one yet
                if (isset($counts[$mid + 1])) {
                    if ($counts[$mid + 1] >= $numMeetings) {
                        // the next one was bigger, so that's the threshold, return it
                        return $radiuses[$mid + 1];
                    }
                }

                $low = $mid + 1;
            } else {
                // see if we have the previous one yet
                if (isset($counts[$mid - 1])) {
                    if ($counts[$mid - 1] < $numMeetings) {
                        // the previous one was smaller, so this is the threshold, return it
                        return $radius;
                    }
                }

                $high = $mid - 1;
            }
        }

        return $radius;
    }

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

        // The stock fields are all english, but we want to account for the possibility that someone added a new/translated
        // field in their server's native language, so we get those first.
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

            // Now we fill in all of the gaps with the english templates
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
        if (in_array($fieldName, Meeting::$mainFields)) {
            $meetingIdsByValue = Meeting::query()
                ->where('published', 1)
                ->get()
                ->mapToGroups(function ($meeting, $_) use ($fieldName, $specificFormats, $allFormats) {
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
                    } elseif ($fieldName == 'weekday_tinyint' && !is_null($value)) {
                        $value += 1;
                    }

                    return [strval($value) => (string)$meeting->id_bigint];
                })
                ->reject(function ($meetingIds, $value) use ($fieldName, $specificFormats) {
                    if (($fieldName == 'duration_time' || $fieldName == 'start_time') && empty($value)) {
                        return true;
                    }
                    return $fieldName == 'formats' && $specificFormats && $value == '';
                });
        } else {
            $meetingIdsByValue = MeetingData::query()
                ->where('key', $fieldName)
                ->whereNot('meetingid_bigint', 0)
                ->whereRelation('meeting', 'published', 1)
                ->where(function ($query) {
                    $query->where('visibility', null)->orWhereNot('visibility', 1);
                })
                ->get()
                ->merge(
                    MeetingLongData::query()
                        ->where('key', $fieldName)
                        ->whereNot('meetingid_bigint', 0)
                        ->whereRelation('meeting', 'published', 1)
                        ->where(function ($query) {
                            $query->where('visibility', null)->orWhereNot('visibility', 1);
                        })
                        ->get()
                )
                ->mapToGroups(function ($meetingData, $key) use ($fieldName) {
                    $value = $meetingData->data_string ?? $meetingData->data_blob ?? null;
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

    public function getMainFields(): Collection
    {
        return collect(Meeting::$mainFields);
    }

    public function getDataTemplates(): Collection
    {
        $serverLanguage = App::currentLocale();
        $fallbackLanguage = config('fallback_locale');
        $dataTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();

        $unique = collect([]);
        foreach ($dataTemplates as $dataTemplate) {
            $seenTemplate = $unique->get($dataTemplate->key);
            if (is_null($seenTemplate)) {
                $unique->put($dataTemplate->key, $dataTemplate);
                continue;
            }

            if ($seenTemplate->lang_enum == $serverLanguage) {
                continue;
            }

            if ($dataTemplate->lang_enum == $serverLanguage) {
                $unique->put($dataTemplate->key, $dataTemplate);
                continue;
            }

            if ($seenTemplate->lang_enum == $fallbackLanguage) {
                continue;
            }

            if ($dataTemplate->lang_enum == $fallbackLanguage) {
                $unique->put($dataTemplate->key, $dataTemplate);
                continue;
            }
        }

        return $unique;
    }

    public function getBoundingBox(): array
    {
        $nw = ['lat' => null, 'long' => null];
        $se = ['lat' => null, 'long' => null];

        $query = Meeting::query()
            ->select(['latitude', 'longitude'])
            ->whereNotNull(['latitude', 'longitude'])
            ->where('published', 1);

        foreach ($query->get() as $coords) {
            // The logic in this loop is copied entirely from the old code
            $longitude = max(-180.0, min(180.0, floatval($coords->longitude)));
            $latitude = max(-90.0, min(90.0, floatval($coords->latitude)));

            if ($longitude == 0 || $latitude == 0) {
                continue;
            }

            if (is_null($nw['long'])) {
                $nw['long'] = $longitude;
            } elseif (abs($longitude) > 90 && $longitude >= 0 && ($nw['long'] < 0)) {
                $nw['long'] = $longitude;
            } elseif (abs($longitude) > 90 && $longitude < 0 && $nw['long'] >= 0) {
                continue;
            } else {
                $nw['long'] = min($longitude, $nw['long']);
            }

            if (is_null($se['long'])) {
                $se['long'] = $longitude;
            } elseif (abs($longitude) > 90 && $longitude < 0 && $se['long'] >= 0) {
                $se['long'] = $longitude;
            } else if (abs($longitude) > 90 && $longitude >= 0 && $se['long'] < 0) {
                continue;
            } else {
                $se['long'] = max($longitude, $se['long']);
            }

            if (is_null($nw['lat'])) {
                $nw['lat'] = $latitude;
            } else {
                $nw['lat'] = max($latitude, $nw['lat']);
            }

            if (is_null($se['lat'])) {
                $se['lat'] = $latitude;
            } else {
                $se['lat'] = min($latitude, $se['lat']);
            }
        }

        return ['nw' => $nw, 'se' => $se];
    }

    public function create(array $values): Meeting
    {
        $values = collect($values);
        $mainValues = $values->reject(fn ($_, $fieldName) => !in_array($fieldName, Meeting::$mainFields))->toArray();
        $dataTemplates = $this->getDataTemplates();
        $dataValues = $values->reject(fn ($_, $fieldName) => !$dataTemplates->has($fieldName));

        return DB::transaction(function () use ($mainValues, $dataValues, $dataTemplates) {
            $meeting = Meeting::create($mainValues);
            foreach ($dataValues as $fieldName => $fieldValue) {
                $t = $dataTemplates->get($fieldName);
                if (strlen($fieldValue) > 255) {
                    MeetingLongData::create([
                        'meetingid_bigint' => $meeting->id_bigint,
                        'key' => $t->key,
                        'field_prompt' => $t->field_prompt,
                        'lang_enum' => $t->lang_enum,
                        'data_blob' => $fieldValue,
                        'visibility' => $t->visibility,
                    ]);
                } else {
                    MeetingData::create([
                        'meetingid_bigint' => $meeting->id_bigint,
                        'key' => $t->key,
                        'field_prompt' => $t->field_prompt,
                        'lang_enum' => $t->lang_enum,
                        'data_string' => $fieldValue,
                        'visibility' => $t->visibility,
                    ]);
                }
            }
            if (!legacy_config('aggregator_mode_enabled')) {
                $this->saveChange(null, $meeting);
            }
            return $meeting;
        });
    }

    public function update(int $id, array $values): bool
    {
        $values = collect($values);
        $mainValues = $values->reject(fn ($_, $fieldName) => !in_array($fieldName, Meeting::$mainFields))->toArray();
        $dataTemplates = $this->getDataTemplates();
        $dataValues = $values->reject(fn ($_, $fieldName) => !$dataTemplates->has($fieldName));

        return DB::transaction(function () use ($id, $mainValues, $dataValues, $dataTemplates) {
            $meeting = Meeting::find($id);
            $meeting->loadMissing(['data', 'longdata']);
            if (!is_null($meeting)) {
                Meeting::query()->where('id_bigint', $id)->update($mainValues);
                MeetingData::query()->where('meetingid_bigint', $id)->delete();
                MeetingLongData::query()->where('meetingid_bigint', $id)->delete();
                foreach ($dataValues as $fieldName => $fieldValue) {
                    $t = $dataTemplates->get($fieldName);
                    if (strlen($fieldValue) > 255) {
                        MeetingLongData::create([
                            'meetingid_bigint' => $meeting->id_bigint,
                            'key' => $t->key,
                            'field_prompt' => $t->field_prompt,
                            'lang_enum' => $t->lang_enum,
                            'data_blob' => $fieldValue,
                            'visibility' => $t->visibility,
                        ]);
                    } else {
                        MeetingData::create([
                            'meetingid_bigint' => $meeting->id_bigint,
                            'key' => $t->key,
                            'field_prompt' => $t->field_prompt,
                            'lang_enum' => $t->lang_enum,
                            'data_string' => $fieldValue,
                            'visibility' => $t->visibility,
                        ]);
                    }
                }
                if (!legacy_config('aggregator_mode_enabled')) {
                    $this->saveChange($meeting, Meeting::find($id));
                }
                return true;
            }
            return false;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $meeting = Meeting::find($id);
            if (!is_null($meeting)) {
                $meeting->loadMissing(['data', 'longdata']);
                MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
                MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
                Meeting::query()->where('id_bigint', $meeting->id_bigint)->delete();
                if (!legacy_config('aggregator_mode_enabled')) {
                    $this->saveChange($meeting, null);
                }
                return true;
            }
            return false;
        });
    }

    private function saveChange(?Meeting $beforeMeeting, ?Meeting $afterMeeting): void
    {
        $beforeObject = !is_null($beforeMeeting) ? $this->serializeForChange($beforeMeeting) : null;
        $afterObject = !is_null($afterMeeting) ? $this->serializeForChange($afterMeeting) : null;
        if (!is_null($beforeObject) && !is_null($afterObject) && $beforeObject == $afterObject) {
            // nothing actually changed, don't save a record
            return;
        }

        Change::create([
            // The default user_id_bigint is null.  This is only for unit testing.  In the context of an authenticated
            // http request there will be a user, which will have a numeric ID.
            'user_id_bigint' => request()?->user()?->id_bigint,
            'service_body_id_bigint' => $afterMeeting?->service_body_bigint ?? $beforeMeeting->service_body_bigint,
            'lang_enum' => $beforeMeeting?->lang_enum ?: $afterMeeting?->lang_enum ?: legacy_config('language') ?: App::currentLocale(),
            'object_class_string' => 'c_comdef_meeting',
            'before_id_bigint' => $beforeMeeting?->id_bigint,
            'before_lang_enum' => !is_null($beforeMeeting) ? $beforeMeeting?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'after_id_bigint' => $afterMeeting?->id_bigint,
            'after_lang_enum' => !is_null($afterMeeting) ? $afterMeeting?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'change_type_enum' => is_null($beforeMeeting) ? 'comdef_change_type_new' : (is_null($afterMeeting) ? 'comdef_change_type_delete' : 'comdef_change_type_change'),
            'before_object' => $beforeObject,
            'after_object' => $afterObject,
        ]);

        $changeLimit = legacy_config('change_depth_for_meetings');
        if (is_integer($changeLimit) && $changeLimit > 0) {
            $meetingId = $beforeMeeting?->id_bigint ?? $afterMeeting?->id_bigint;
            if (!is_null($meetingId)) {
                $keepIds = Change::query()
                    ->where('before_id_bigint', $meetingId)
                    ->orWhere('after_id_bigint', $meetingId)
                    ->orderByDesc('id_bigint')
                    ->limit($changeLimit)
                    ->pluck('id_bigint');

                Change::query()
                    ->where(function ($query) use ($meetingId) {
                        $query
                            ->where('before_id_bigint', $meetingId)
                            ->orWhere('after_id_bigint', $meetingId);
                    })
                    ->whereNotIn('id_bigint', $keepIds)
                    ->delete();
            }
        }
    }

    private function serializeForChange(Meeting $meeting): string
    {
        return serialize([
            'main_table_values' => serialize([
                'id_bigint' => $meeting->id_bigint,
                'email_contact' => $meeting->email_contact,
                'worldid_mixed' => $meeting->worldid_mixed,
                'service_body_bigint' => $meeting->service_body_bigint,
                'weekday_tinyint' => $meeting->weekday_tinyint,  // decrement?
                'venue_type' => $meeting->venue_type,
                'start_time' => $meeting->start_time,
                'lang_enum' => $meeting->lang_enum,
                'duration_time' => $meeting->duration_time,
                'time_zone' => $meeting->time_zone,
                'longitude' => $meeting->longitude,
                'latitude' => $meeting->latitude,
                'published' => $meeting->published,
                'formats' => $meeting->formats,
            ]),
            'data_table_values' => serialize(
                $meeting->data
                    ->map(fn ($data) => [
                        'meetingid_bigint' => $data->meetingid_bigint,
                        'lang_enum' => $data->lang_enum,
                        'field_prompt' => $data->field_prompt,
                        'visibility' => $data->visibility,
                        'key' => $data->key,
                        'data_string' => $data->data_string,
                        'data_bigint' => null,
                        'data_double' => null,
                    ])
                    ->sortBy('key')
                    ->values()
                    ->toArray()
            ),
            'longdata_table_values' => serialize(
                $meeting->longdata
                    ->map(fn ($data) => [
                        'meetingid_bigint' => $data->meetingid_bigint,
                        'lang_enum' => $data->lang_enum,
                        'field_prompt' => $data->field_prompt,
                        'visibility' => $data->visibility,
                        'key' => $data->key,
                        'data_blob' => $data->data_blob,
                    ])
                    ->sortBy('key')
                    ->values()
                    ->toArray()
            ),
        ]);
    }

    public function import(int $rootServerId, Collection $externalObjects): void
    {
        $sourceIds = $externalObjects->map(fn (ExternalMeeting $ex) => $ex->id);
        $meetingIds = Meeting::query()
            ->where('root_server_id', $rootServerId)
            ->whereNotIn('source_id', $sourceIds)
            ->pluck('id_bigint');

        Meeting::query()->whereIn('id_bigint', $meetingIds)->delete();
        MeetingData::query()->whereIn('meetingid_bigint', $meetingIds)->delete();
        MeetingLongData::query()->whereIn('meetingid_bigint', $meetingIds)->delete();

        $formatRepository = new FormatRepository();
        $allFormats = $formatRepository->search(rootServersInclude: [$rootServerId], showAll: true)->groupBy(['shared_id_bigint']);
        $formatSharedIdToSourceIdMap = $allFormats->mapWithKeys(fn ($formats, $sharedId) => [$sharedId => $formats->first()->source_id]);
        $formatSourceIdToSharedIdMap = $allFormats->mapWithKeys(fn ($formats, $sharedId) => [$formats->first()->source_id => $sharedId]);

        $serviceBodyRepository = new ServiceBodyRepository();
        $allServiceBodies = $serviceBodyRepository->search(rootServersInclude: [$rootServerId]);
        $serviceBodyIdToSourceIdMap = $allServiceBodies->mapWithKeys(fn ($sb, $_) => [$sb->id_bigint => $sb->source_id]);
        $serviceBodySourceIdToIdMap = $allServiceBodies->mapWithKeys(fn ($sb, $_) => [$sb->source_id => $sb->id_bigint]);

        $allMeetings = $this->getSearchResults(rootServersInclude: [$rootServerId]);
        $meetingsBySourceId = $allMeetings->mapWithKeys(fn ($meeting, $_) => [$meeting->source_id => $meeting]);

        foreach ($externalObjects as $external) {
            $external = $this->castExternal($external);
            $db = $meetingsBySourceId->get($external->id);

            $serviceBodyId = $serviceBodySourceIdToIdMap->get($external->serviceBodyId);
            if (is_null($serviceBodyId)) {
                if (!is_null($db)) {
                    $db->delete();
                }
                continue;
            }

            if (is_null($db)) {
                $values = $this->externalMeetingToValuesArray($rootServerId, $serviceBodyId, $external, $formatSourceIdToSharedIdMap);
                $this->create($values);
            } else if (!$external->isEqual($db, $serviceBodyIdToSourceIdMap, $formatSharedIdToSourceIdMap)) {
                $values = $this->externalMeetingToValuesArray($rootServerId, $serviceBodyId, $external, $formatSourceIdToSharedIdMap);
                $this->update($db->id_bigint, $values);
            }
        }
    }

    private function castExternal($obj): ExternalMeeting
    {
        return $obj;
    }

    private function externalMeetingToValuesArray(int $rootServerId, int $serviceBodyId, ExternalMeeting $externalMeeting, Collection $formatSourceIdToSharedIdMap): array
    {

        return [
            'root_server_id' => $rootServerId,
            'source_id' => $externalMeeting->id,
            'service_body_bigint' => $serviceBodyId,
            'formats' => collect($externalMeeting->formatIds)
                ->map(fn ($id) => $formatSourceIdToSharedIdMap->get($id))
                ->reject(fn ($id) => is_null($id))
                ->sort()
                ->unique()
                ->values()
                ->join(','),
            'venue_type' => $externalMeeting->venueType,
            'weekday_tinyint' => $externalMeeting->weekdayId - 1,
            'start_time' => $externalMeeting->startTime,
            'duration_time' => $externalMeeting->durationTime,
            'latitude' => $externalMeeting->latitude,
            'longitude' => $externalMeeting->longitude,
            'published' => $externalMeeting->published ? 1 : 0,
            'worldid_mixed' => $externalMeeting->worldId,
            'meeting_name' => $externalMeeting->name,
            'comments' => $externalMeeting->comments,
            'virtual_meeting_additional_info' => $externalMeeting->virtualMeetingAdditionalInfo,
            'virtual_meeting_link' => $externalMeeting->virtualMeetingLink,
            'phone_meeting_number' => $externalMeeting->phoneMeetingNumber,
            'location_nation' => $externalMeeting->locationNation,
            'location_postal_code_1' => $externalMeeting->locationPostalCode1,
            'location_province' => $externalMeeting->locationProvince,
            'location_sub_province' => $externalMeeting->locationSubProvince,
            'location_municipality' => $externalMeeting->locationMunicipality,
            'location_city_subsection' => $externalMeeting->locationCitySubsection,
            'location_neighborhood' => $externalMeeting->locationNeighborhood,
            'location_street' => $externalMeeting->locationStreet,
            'location_info' => $externalMeeting->locationInfo,
            'location_text' => $externalMeeting->locationText,
            'bus_lines' => $externalMeeting->busLines,
            'train_lines' => $externalMeeting->trainLines,
        ];
    }
}
