<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormatType extends Model
{
    protected $table = 'comdef_format_types';
    public $timestamps = false;
    protected $fillable = [
        'key_string',
        'description_string',
    ];

}
