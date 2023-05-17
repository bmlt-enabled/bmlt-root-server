<?php

namespace App\Interfaces;

use App\Models\FormatType;
use Illuminate\Support\Collection;

interface FormatTypeRepositoryInterface
{
    public function getDescriptionFromKey($key);
    public function getKeyFromDescription($description);
    public function getDescriptions(): array;
}
