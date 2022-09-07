<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface MeetingRepositoryInterface
{
    public function getFieldKeys(): Collection;
    public function getFieldValues(string $fieldName, array $specificFormats, bool $allFormats): Collection;
}
