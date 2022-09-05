<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
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
}
