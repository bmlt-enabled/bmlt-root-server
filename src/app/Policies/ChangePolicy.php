<?php

namespace App\Policies;

use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChangePolicy
{
    use DeniesDeactivatedUser, HandlesAuthorization;

    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function viewAny(User $user, Meeting $meeting)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->contains($meeting->service_body_bigint);
    }
}
