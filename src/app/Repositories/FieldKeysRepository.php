<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use App\Interfaces\FieldKeysRepositoryInterface;
use App\Models\MeetingData;

class FieldKeysRepository implements FieldKeysRepositoryInterface
{
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
}
