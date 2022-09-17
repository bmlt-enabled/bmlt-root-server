<?php

namespace App\Interfaces;

interface MigrationRepositoryInterface
{
    public function getLastMigration(): array;
    public function migrationExists($migrationName): bool;
}
