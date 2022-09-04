<?php

namespace App\Interfaces;

interface ServiceBodyRepositoryInterface
{
    public function getServiceBodies(
        array $includeIds = [],
        array $excludeIds = [],
        bool $recurseChildren = false,
        bool $recurseParents = false
    );
}
