<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use DeniesDisabledUser, HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, User $resourceUser)
    {
        if ($user->id_bigint == $resourceUser->id_bigint) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            return $user->id_bigint == $resourceUser->owner_id_bigint;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $resourceUser)
    {
        if ($user->id_bigint == $resourceUser->id_bigint) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            return $user->id_bigint == $resourceUser->owner_id_bigint;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function delete(User $user, User $resourceUser)
    {
        return $user->isAdmin();
    }
}
