<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    protected $table = 'comdef_formats';
    public $timestamps = false;
    protected $fillable = [
        'root_server_id',
        'source_id',
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

    public function rootServer()
    {
        return $this->belongsTo(RootServer::class, 'root_server_id');
    }

    public function translations()
    {
        return $this
            ->hasMany(self::class, 'shared_id_bigint', 'shared_id_bigint')
            ->orderBy('lang_enum');
    }
}
