<?php

namespace App\Policies;

use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceBodyPolicy
{
    use DeniesDeactivatedUser, HandlesAuthorization;

    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, ServiceBody $serviceBody)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->contains($serviceBody->id_bigint);
    }

    public function create(User $user)
    {
        if (legacy_config('aggregator_mode_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }

    public function update(User $user, ServiceBody $serviceBody)
    {
        if (legacy_config('aggregator_mode_enabled')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            return $this->serviceBodyRepository->getAdminServiceBodyIds($user->id_bigint)->contains($serviceBody->id_bigint);
        }

        return false;
    }

    public function partialUpdate(User $user, ServiceBody $serviceBody)
    {
        return $this->update($user, $serviceBody);
    }

    public function delete(User $user, ServiceBody $serviceBody)
    {
        if (legacy_config('aggregator_mode_enabled')) {
            return false;
        }

        return $user->isAdmin();
    }
}
