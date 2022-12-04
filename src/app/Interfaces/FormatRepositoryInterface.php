<?php

namespace App\Interfaces;

use App\Models\Format;
use Illuminate\Support\Collection;

interface FormatRepositoryInterface
{
    public function search(
        array $formatsInclude = null,
        array $formatsExclude = null,
        array $rootServersInclude = null,
        array $rootServersExclude = null,
        array $langEnums = null,
        array $keyStrings = null,
        bool $showAll = false,
        Collection $meetings = null,
        bool $eagerRootServers = false
    ): Collection;
    public function getAsTranslations(array $formatIds = null): Collection;
    public function getVirtualFormat(): Format;
    public function getHybridFormat(): Format;
    public function getTemporarilyClosedFormat(): Format;
    public function create(array $sharedFormatsValues): Format;
    public function update(int $sharedId, array $sharedFormatsValues): bool;
    public function delete(int $sharedId): bool;
    public function import(int $rootServerId, Collection $externalObjects): void;
}
