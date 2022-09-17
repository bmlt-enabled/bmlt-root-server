<?php

namespace App\Repositories;

use App\Interfaces\MigrationRepositoryInterface;
use App\Models\Migration;

class MigrationRepository implements MigrationRepositoryInterface
{
    public function getLastMigration(): array
    {
        return Migration::query()->orderByDesc('id')->first()->attributesToArray();
    }

    public function migrationExists($migrationName): bool
    {
        return Migration::where('migration', $migrationName)->exists();
    }
}
