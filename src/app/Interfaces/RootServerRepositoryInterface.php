<?php

namespace App\Interfaces;

use App\Models\RootServer;
use Illuminate\Support\Collection;

interface RootServerRepositoryInterface
{
    public function search(): Collection;
    public function create(array $values): RootServer;
    public function update(int $id, array $values): bool;
    public function delete(int $id): bool;
}
