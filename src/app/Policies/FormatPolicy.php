<?php

namespace App\Policies;

use App\Models\Format;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormatPolicy
{
    use DeniesDeactivatedUser, HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Format $format)
    {
        return true;
    }

    public function create(User $user)
    {
        if (legacy_config('aggregator_mode_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }

    public function update(User $user, Format $format)
    {
        if (legacy_config('aggregator_mode_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }

    public function partialUpdate(User $user, Format $format)
    {
        return $this->update($user, $format);
    }

    public function delete(User $user, Format $format)
    {
        if (legacy_config('aggregator_mode_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }
}
