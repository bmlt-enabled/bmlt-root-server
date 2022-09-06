<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingData extends Model
{
    protected $table = 'comdef_meetings_data';
    public $timestamps = false;
    protected $fillable = [
        'meetingid_bigint',
        'key',
        'field_prompt',
        'lang_enum',
        'data_string',
        'visibility',
    ];
}
