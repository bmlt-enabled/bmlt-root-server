<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function getByUsername(string $username);
}
