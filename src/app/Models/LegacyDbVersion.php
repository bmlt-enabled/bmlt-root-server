<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyDbVersion extends Model
{
    protected $table = 'comdef_db_version';
    public $timestamps = false;
}
