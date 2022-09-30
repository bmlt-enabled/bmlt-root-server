<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    public const TYPE_MEETING_FORMAT = 'MEETING_FORMAT';
    public const TYPE_LOCATION_CODE = 'LOCATION';
    public const TYPE_COMMON_NEEDS = 'COMMON_NEEDS_OR_RESTRICTION';
    public const TYPE_OPEN_CLOSED = 'OPEN_OR_CLOSED';
    public const TYPE_LANGUAGE = 'LANGUAGE';

    public const TYPE_COMDEF_MEETING_FORMAT = 'FC1';
    public const TYPE_COMDEF_LOCATION_CODE = 'FC2';
    public const TYPE_COMDEF_COMMON_NEEDS = 'FC3';
    public const TYPE_COMDEF_OPEN_CLOSED = 'O';
    public const TYPE_COMDEF_LANGUAGE = 'LANG';

    public const TYPE_TO_COMDEF_TYPE_MAP = [
        self::TYPE_MEETING_FORMAT => self::TYPE_COMDEF_MEETING_FORMAT,
        self::TYPE_LOCATION_CODE => self::TYPE_COMDEF_LOCATION_CODE,
        self::TYPE_COMMON_NEEDS => self::TYPE_COMDEF_COMMON_NEEDS,
        self::TYPE_OPEN_CLOSED => self::TYPE_COMDEF_OPEN_CLOSED,
        self::TYPE_LANGUAGE => self::TYPE_COMDEF_LANGUAGE,
    ];

    public const COMDEF_TYPE_TO_TYPE_MAP = [
        self::TYPE_COMDEF_MEETING_FORMAT => self::TYPE_MEETING_FORMAT,
        self::TYPE_COMDEF_LOCATION_CODE => self::TYPE_LOCATION_CODE,
        self::TYPE_COMDEF_COMMON_NEEDS => self::TYPE_COMMON_NEEDS,
        self::TYPE_COMDEF_OPEN_CLOSED => self::TYPE_OPEN_CLOSED,
        self::TYPE_COMDEF_LANGUAGE => self::TYPE_LANGUAGE,
    ];

    protected $table = 'comdef_formats';
    public $timestamps = false;
    protected $fillable = [
        'shared_id_bigint',
        'key_string',
        'worldid_mixed',
        'lang_enum',
        'name_string',
        'description_string',
        'format_type_enum',
    ];

    public function getRouteKeyName()
    {
        return 'shared_id_bigint';
    }

    public function translations()
    {
        return $this
            ->hasMany(self::class, 'shared_id_bigint', 'shared_id_bigint')
            ->orderBy('lang_enum');
    }
}
