<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    protected $table = 'comdef_changes';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
}
