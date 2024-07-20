<?php

namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function getById(int $id);
    public function getByUsername(string $username);
    public function search(array $includeIds = null, array $includeOwnerIds = null);
    public function create(array $values): User;
    public function update(int $id, array $values): bool;
    public function delete(int $id): bool;
    public function updatePassword(int $id, string $plaintextPassword);
}
