<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Meeting extends Model
{
    protected $table = 'comdef_meetings_main';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = [
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
        'published',
        'email_contact',
    ];

    public static $mainFields = [
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

    public function getName()
    {
        return $this->data->where('key', 'meeting_name')->pluck('data_string')->first();
    }

    public function data()
    {
        return $this->hasMany(MeetingData::class, 'meetingid_bigint');
    }

    public function longData()
    {
        return $this->hasMany(MeetingLongData::class, 'meetingid_bigint');
    }

    private ?string $calculatedFormatKeys = null;
    private function setCalculatedFormatKeys(string $formatKeyStrings)
    {
        $this->calculatedFormatKeys = $formatKeyStrings;
    }

    public function getCalculatedFormatKeys(): string
    {
        return $this->calculatedFormatKeys ?? '';
    }

    private ?string $calculatedFormatSharedIds = null;
    private function setCalculatedFormatSharedIds(string $formatSharedIds)
    {
        $this->calculatedFormatSharedIds = $formatSharedIds;
    }

    public function getCalculatedFormatSharedIds(): string
    {
        return $this->calculatedFormatSharedIds ?? '';
    }

    public function calculateFormatsFields(Collection $formatsById)
    {
        if (is_null($this->formats) || $this->formats == '') {
            return;
        }

        $formatIds = explode(',', $this->formats);

        $calculatedFormats = [];
        foreach ($formatIds as $formatId) {
            $format = $formatsById->get(intval($formatId));
            if ($format) {
                $calculatedFormats[$format->shared_id_bigint] = $format->key_string;
            }
        }

        $calculatedFormatIds = array_keys($calculatedFormats);
        sort($calculatedFormatIds);
        $this->setCalculatedFormatSharedIds(implode(',', $calculatedFormatIds));

        uksort($calculatedFormats, function ($id1, $id2) {
            // force closed and open formats to the beginning of the list
            // 4 is closed, 17 is open
            if ($id1 == 4 || $id1 == 17) {
                return -1;
            } elseif ($id2 == 4 || $id2 == 17) {
                return 1;
            } elseif ($id1 == $id2) {
                return 0;
            }
            return $id1 < $id2 ? -1 : 1;
        });

        $calculatedFormatKeys = array_unique(array_values($calculatedFormats));
        $this->setCalculatedFormatKeys(implode(',', $calculatedFormatKeys));
    }
}
