<?php

namespace App\Interfaces;

use App\Models\Format;
use Illuminate\Support\Collection;

interface FormatRepositoryInterface
{
    public function search(array $langEnums = null, array $keyStrings = null, bool $showAll = false, Collection $meetings = null): Collection;
    public function getAsTranslations(): Collection;
    public function create(array $sharedFormatsValues): Format;
    public function update(int $sharedId, array $sharedFormatsValues): bool;
    public function delete(int $sharedId): bool;
}
