<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FieldValuesRepositoryInterface
{
    public function getFieldValues(string $fieldName, array $specificFormats, bool $allFormats): Collection;
}
