<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use App\Interfaces\FormatRepositoryInterface;
use App\Models\Format;
use App\Models\Meeting;

class FormatRepository implements FormatRepositoryInterface
{
    public function getFormats(array $langEnums = null, array $keyStrings = null, bool $showAll = false, Collection $meetings = null): Collection
    {
        $formats = Format::query();

        if (!is_null($langEnums)) {
            $formats = $formats->whereIn('lang_enum', $langEnums);
        }

        if (!$showAll || !is_null($meetings)) {
            $formats = $formats->whereIn('shared_id_bigint', $this->getUsedFormatIds($meetings));
        }

        if ($keyStrings) {
            $formats = $formats->whereIn('key_string', $keyStrings);
        }

        return $formats->get();
    }

    private function getUsedFormatIds(Collection $meetings = null): array
    {
        $uniqueFormatIds = [];

        if (is_null($meetings)) {
            $meetings = Meeting::query();
        }

        foreach ($meetings->pluck('formats') as $formatIds) {
            if ($formatIds) {
                $formatIds = explode(",", $formatIds);
                foreach ($formatIds as $formatId) {
                    $uniqueFormatIds[$formatId] = null;
                }
            }
        }

        return array_keys($uniqueFormatIds);
    }
}
