<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBody extends Model
{
    protected $table = 'comdef_service_bodies';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = [
        'sb_owner',
        'name_string',
        'description_string',
        'sb_type',
        'uri_string',
        'kml_file_uri_string',
        'principal_user_bigint',
        'worldid_mixed',
        'sb_meeting_email',
    ];
}
