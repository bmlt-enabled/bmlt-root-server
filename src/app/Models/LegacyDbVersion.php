<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyDbVersion extends Model
{
    protected $table = 'comdef_db_version';
    protected $primaryKey = null;
    public $timestamps = false;
}
