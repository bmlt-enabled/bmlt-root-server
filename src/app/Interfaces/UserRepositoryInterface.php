<?php

namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getByUsername(string $username);
    public function search(array $includeIds = null, array $includeOwnerIds = null);
    public function create(array $values): User;
    public function delete(int $id): bool;
}
