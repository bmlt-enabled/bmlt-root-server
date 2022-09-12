<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface ServiceBodyRepositoryInterface
{
    public function getServiceBodies(array $includeIds = [], array $excludeIds = [], bool $recurseChildren = false, bool $recurseParents = false): Collection;
    public function getServiceBodyIdsForUser(int $userId): Collection;
    public function getChildren(array $parents): array;
}
