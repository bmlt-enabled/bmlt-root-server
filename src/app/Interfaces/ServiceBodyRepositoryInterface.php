<?php

namespace App\Interfaces;

use App\Models\ServiceBody;
use Illuminate\Support\Collection;

interface ServiceBodyRepositoryInterface
{
    public function search(array $includeIds = [], array $excludeIds = [], bool $recurseChildren = false, bool $recurseParents = false): Collection;
    public function create(array $serviceBody): ServiceBody;
    public function getUserServiceBodyIds(int $userId): Collection;
    public function getChildren(array $parents): array;
    public function getParents(array $children): array;
}
