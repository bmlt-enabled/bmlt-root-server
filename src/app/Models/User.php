<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;

    public const USER_LEVEL_ADMIN = 1;
    public const USER_LEVEL_SERVICE_BODY_ADMIN = 2;
    public const USER_LEVEL_DISABLED = 4;
    public const USER_LEVEL_OBSERVER = 5;

    protected $table = 'comdef_users';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = [
        'user_level_tinyint',
        'name_string',
        'description_string',
        'email_address_string',
        'login_string',
        'password_string',
    ];

    public function getAuthPassword()
    {
        return $this->password_string;
    }

    public function isAdmin(): bool
    {
        return $this->user_level_tinyint == self::USER_LEVEL_ADMIN;
    }

    public function isServiceBodyAdmin(): bool
    {
        return $this->user_level_tinyint == self::USER_LEVEL_SERVICE_BODY_ADMIN;
    }

    public function isDisabled(): bool
    {
        return $this->user_level_tinyint == self::USER_LEVEL_DISABLED;
    }
}
