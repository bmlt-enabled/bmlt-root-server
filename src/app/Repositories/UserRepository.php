<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function getByUsername(string $username)
    {
        return User::query()->where('login_string', $username)->first();
    }
}
