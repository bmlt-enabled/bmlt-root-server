<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = 'comdef_meetings_main';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
}
