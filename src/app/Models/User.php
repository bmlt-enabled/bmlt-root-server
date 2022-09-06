<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'comdef_users';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;

    public function getAuthPassword()
    {
        return $this->password_string;
    }
}
