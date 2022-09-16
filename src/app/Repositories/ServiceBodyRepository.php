<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;

class ServiceBodyRepository implements ServiceBodyRepositoryInterface
{
    public function getServiceBodies(
        array $includeIds = [],
        array $excludeIds = [],
        bool $recurseChildren = false,
        bool $recurseParents = false
    ): Collection {
        if ($recurseChildren) {
            $includeIds = array_merge($includeIds, $this->getChildren($includeIds));
            $excludeIds = array_merge($excludeIds, $this->getChildren($excludeIds));
        }

        if ($recurseParents) {
            $includeIds = array_merge($includeIds, $this->getParents($includeIds));
            $excludeIds = array_merge($excludeIds, $this->getParents($excludeIds));
        }

        $serviceBodies = ServiceBody::query();
        if (!empty($includeIds)) {
            $serviceBodies = $serviceBodies->whereIn('id_bigint', $includeIds);
        }

        if (!empty($excludeIds)) {
            $serviceBodies = $serviceBodies->whereNotIn('id_bigint', $excludeIds);
        }

        return $serviceBodies->get();
    }

    public function getServiceBodyIdsForUser(int $userId): Collection
    {
        $serviceBodyIds = ServiceBody::query()
            ->where('principal_user_bigint', $userId)
            ->orWhere(function (Builder $query) use ($userId) {
                $query
                    ->orWhere('editors_string', "$userId")
                    ->orWhere('editors_string', 'LIKE', "$userId,%")
                    ->orWhere('editors_string', 'LIKE', "%,$userId,%")
                    ->orWhere('editors_string', 'LIKE', "%,$userId");
            })
            ->get()
            ->map(fn ($sb) => $sb->id_bigint)
            ->toArray();

        foreach ($this->getChildren($serviceBodyIds) as $serviceBodyId) {
            $serviceBodyIds[] = $serviceBodyId;
        }

        return collect($serviceBodyIds);
    }

    public function getChildren(array $parents): array
    {
        $ret = [];

        $children = $parents;
        while (!empty($children)) {
            $serviceBodies = ServiceBody::query()->whereIn('sb_owner', $children)->get();
            $children = [];
            foreach ($serviceBodies as $serviceBody) {
                if (in_array($serviceBody->id_bigint, $ret)) {
                    continue;
                }

                $ret[] = $serviceBody->id_bigint;
                $children[] = $serviceBody->id_bigint;
            }
        }

        return $ret;
    }

    public function getParents(array $children): array
    {
        $ret = [];

        $parents = $children;
        while (!empty($parents)) {
            $serviceBodies = ServiceBody::query()->whereIn('id_bigint', $parents)->get();
            $parents = [];
            foreach ($serviceBodies as $serviceBody) {
                if (in_array($serviceBody->id_bigint, $ret)) {
                    continue;
                }

                $ret[] = $serviceBody->id_bigint;

                if (!$serviceBody->sb_owner) {
                    continue;
                }

                $parents[] = $serviceBody->sb_owner;
            }
        }

        return $ret;
    }
}
