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

    public const USER_TYPE_DISABLED = 'disabled';
    public const USER_TYPE_ADMIN = 'admin';
    public const USER_TYPE_SERVICE_BODY_ADMIN = 'serviceBodyAdmin';
    public const USER_TYPE_OBSERVER = 'observer';

    public const USER_LEVEL_TO_USER_TYPE_MAP = [
        self::USER_LEVEL_DISABLED => self::USER_TYPE_DISABLED,
        self::USER_LEVEL_ADMIN => self::USER_TYPE_ADMIN,
        self::USER_LEVEL_SERVICE_BODY_ADMIN => self::USER_TYPE_SERVICE_BODY_ADMIN,
        self::USER_LEVEL_OBSERVER => self::USER_TYPE_OBSERVER,
    ];

    public const USER_TYPE_TO_USER_LEVEL_MAP = [
        self::USER_TYPE_DISABLED => self::USER_LEVEL_DISABLED,
        self::USER_TYPE_ADMIN => self::USER_LEVEL_ADMIN,
        self::USER_TYPE_SERVICE_BODY_ADMIN => self::USER_LEVEL_SERVICE_BODY_ADMIN,
        self::USER_TYPE_OBSERVER => self::USER_LEVEL_OBSERVER,
    ];

    public const FIELDS = [
        'user_level_tinyint',
        'name_string',
        'description_string',
        'email_address_string',
        'login_string',
        'password_string',
        'owner_id_bigint',
    ];

    protected $table = 'comdef_users';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = self::FIELDS;

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

    public function children()
    {
        return $this->hasMany(self::class, 'owner_id_bigint');
    }
}
