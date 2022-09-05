<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FieldKeysRepositoryInterface
{
    public function getFieldKeys(): Collection;
}
