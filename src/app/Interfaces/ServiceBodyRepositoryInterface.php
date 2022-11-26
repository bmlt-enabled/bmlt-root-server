<?php

namespace App\Interfaces;

use App\Models\ServiceBody;
use Illuminate\Support\Collection;

interface ServiceBodyRepositoryInterface
{
    public function search(
        array $includeIds = [],
        array $excludeIds = [],
        array $rootServersInclude = null,
        array $rootServersExclude = null,
        bool $recurseChildren = false,
        bool $recurseParents = false
    ): Collection;
    public function create(array $values): ServiceBody;
    public function update(int $id, array $values): bool;
    public function delete(int $id): bool;
    public function getAssignedServiceBodyIds(int $userId): Collection;
    public function getAdminServiceBodyIds(int $userId): Collection;
    public function getChildren(array $parents): array;
    public function getParents(array $children): array;
}
