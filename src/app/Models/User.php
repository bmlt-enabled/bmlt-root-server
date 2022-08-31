<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'comdef_users';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
}
