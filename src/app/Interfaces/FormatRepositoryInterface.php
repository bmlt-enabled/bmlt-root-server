<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FormatRepositoryInterface
{
    public function getFormats(array $langEnums, array $keyStrings, bool $showAll): Collection;
}
