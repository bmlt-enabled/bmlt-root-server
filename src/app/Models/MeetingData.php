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

    public const STOCK_FIELDS = [
        'contact_phone_2',
        'contact_email_1',
        'contact_phone_1',
        'contact_email_2',
        'contact_name_1',
        'contact_name_2',
        'comments',
        'virtual_meeting_additional_info',
        'location_city_subsection',
        'virtual_meeting_link',
        'phone_meeting_number',
        'location_nation',
        'location_postal_code_1',
        'location_province',
        'location_sub_province',
        'location_municipality',
        'location_neighborhood',
        'location_street',
        'location_info',
        'location_text',
        'meeting_name',
        'bus_lines',
        'train_lines',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meetingid_bigint');
    }
}
