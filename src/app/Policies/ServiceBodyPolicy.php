<?php

namespace App\Policies;

use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceBodyPolicy
{
    use DeniesDisabledUser, HandlesAuthorization;

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

        return $this->serviceBodyRepository->getUserServiceBodyIds($user->id_bigint)->contains($serviceBody->id_bigint);
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, ServiceBody $serviceBody)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            return $this->serviceBodyRepository->getUserServiceBodyIds($user->id_bigint)->contains($serviceBody->id_bigint);
        }

        return false;
    }

    public function partialUpdate(User $user, ServiceBody $serviceBody)
    {
        return $this->update($user, $serviceBody);
    }

    public function delete(User $user, ServiceBody $serviceBody)
    {
        return $user->isAdmin();
    }
}
