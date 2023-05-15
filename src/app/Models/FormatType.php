<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormatType extends Model
{
    protected $table = 'comdef_formattypes';
    public $timestamps = false;
    protected $fillable = [
        'root_server_id',
        'source_id',
        'key_string',
        'lang_enum',
        'description_string',
    ];

    public function rootServer()
    {
        return $this->belongsTo(RootServer::class, 'root_server_id');
    }

    public function translations()
    {
        return $this
            ->hasMany(self::class, 'key_string', 'key_string')
            ->orderBy('lang_enum');
    }
}
