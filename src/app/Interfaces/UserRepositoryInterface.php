<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function getByUsername(string $username);
    public function search(array $includeIds = null, array $includeOwnerIds = null);
    public function delete(int $id): bool;
}
