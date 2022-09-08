<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FormatRepositoryInterface
{
    public function getFormats(array $langEnums = ['en'], array $keyStrings = null, bool $showAll = false): Collection;
}
