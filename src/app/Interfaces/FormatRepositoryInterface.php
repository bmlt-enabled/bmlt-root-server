<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface FormatRepositoryInterface
{
    public function getFormats(array $langEnums = null, array $keyStrings = null, bool $showAll = false, Collection $meetings = null): Collection;
}
