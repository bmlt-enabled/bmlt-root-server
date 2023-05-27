<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormatType extends Model
{
    protected $table = 'comdef_format_types';
    public $timestamps = false;
    protected $fillable = [
        'key_string',
        'api_enum',
        'position'
    ];
    private static ?Collection $_formatTypes = null;
    private static function getFormatTypes()
    {
        if (is_null(FormatType::$_formatTypes)) {
            $_formatTypes = FormatType::query()->get();
        }
        return $_formatTypes;
    }
    public static function getApiEnumFromKey($key)
    {
        $ret = FormatType::getFormatTypes()->firstWhere('key_string', $key);
        if (is_null($ret)) {
            return null;
        }
        return $ret->api_enum;
    }
    public static function getKeyFromApiEnum($key)
    {
        $ret = FormatType::getFormatTypes()->firstWhere('api_enum', $key);
        if (is_null($ret)) {
            return null;
        }
        return $ret->key_string;
    }
    public static function getApiEnums()
    {
        return FormatType::getFormatTypes()->keyBy('api_enum')->keys()->all();
    }
}
