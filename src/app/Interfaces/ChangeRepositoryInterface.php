<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface ChangeRepositoryInterface
{
    public function getMeetingChanges(string $startDate = null, string $endDate = null, int $meetingId = null, int $serviceBodyId = null, array $changeTypes = null): Collection;
}
