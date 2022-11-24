<?php

namespace App\Policies;

use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeetingPolicy
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

    public function view(User $user, Meeting $meeting)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->contains($meeting->service_body_bigint);
    }

    public function create(User $user)
    {
        if (legacy_config('is_aggregator_mode_enabled')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            $serviceBodyId = request()->input('serviceBodyId');
            if (!is_null($serviceBodyId)) {
                return $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->contains($serviceBodyId);
            }
        }

        return false;
    }

    public function update(User $user, Meeting $meeting)
    {
        if (legacy_config('is_aggregator_mode_enabled')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            return $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->contains($meeting->service_body_bigint);
        }

        return false;
    }

    public function partialUpdate(User $user, Meeting $meeting)
    {
        return $this->update($user, $meeting);
    }

    public function delete(User $user, Meeting $meeting)
    {
        if (legacy_config('is_aggregator_mode_enabled')) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isServiceBodyAdmin()) {
            return $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->contains($meeting->service_body_bigint);
        }

        return false;
    }
}
