<?php

namespace App\Policies;

use App\Interfaces\RootServerRepositoryInterface;
use App\Models\RootServer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RootServerPolicy
{
    use DeniesDisabledUser, HandlesAuthorization;

    private RootServerRepositoryInterface $rootServerRepository;

    public function __construct(RootServerRepositoryInterface $rootServerRepository)
    {
        $this->rootServerRepository = $rootServerRepository;
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, RootServer $rootServer)
    {
        return true;
    }

    public function create(User $user)
    {
        if (!legacy_config('is_aggregator_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }

    public function update(User $user, RootServer $rootServer)
    {
        if (!legacy_config('is_aggregator_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }

    public function partialUpdate(User $user, RootServer $rootServer)
    {
        return $this->update($user, $rootServer);
    }

    public function delete(User $user, RootServer $rootServer)
    {
        if (!legacy_config('is_aggregator_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }
}
