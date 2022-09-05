<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface ServiceBodyRepositoryInterface
{
    public function getServiceBodies(array $includeIds, array $excludeIds, bool $recurseChildren, bool $recurseParents): Collection;
}
