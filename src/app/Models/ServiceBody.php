<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBody extends Model
{
    public const SB_TYPE_GROUP = 'GR';
    public const SB_TYPE_COOP = 'CO';
    public const SB_TYPE_GSU = 'GS';
    public const SB_TYPE_LSU = 'LS';
    public const SB_TYPE_AREA = 'AS';
    public const SB_TYPE_METRO = 'MA';
    public const SB_TYPE_REGION = 'RS';
    public const SB_TYPE_ZONE = 'ZF';
    public const SB_TYPE_WORLD = 'WS';
    public const VALID_SB_TYPES = [
        self::SB_TYPE_GROUP,
        self::SB_TYPE_COOP,
        self::SB_TYPE_GSU,
        self::SB_TYPE_LSU,
        self::SB_TYPE_AREA,
        self::SB_TYPE_METRO,
        self::SB_TYPE_REGION,
        self::SB_TYPE_ZONE,
        self::SB_TYPE_WORLD,
    ];

    public const FIELDS = [
        'sb_owner',
        'name_string',
        'description_string',
        'sb_type',
        'uri_string',
        'kml_file_uri_string',
        'principal_user_bigint',
        'worldid_mixed',
        'sb_meeting_email',
        'editors_string',
    ];

    protected $table = 'comdef_service_bodies';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = self::FIELDS;

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'service_body_bigint');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'sb_owner');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'sb_owner');
    }
}
