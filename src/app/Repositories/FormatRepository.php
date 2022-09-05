<?php

namespace App\Repositories;

use App\Interfaces\FormatRepositoryInterface;
use App\Models\Format;
use App\Models\Meeting;

class FormatRepository implements FormatRepositoryInterface
{
    public function getFormats(
        array $langEnums = ['en'],
        array $keyStrings = null,
        bool $showAll = false,
    ) {
        $formats = Format::query()->whereIn('lang_enum', $langEnums);

        if (!$showAll) {
            $formats = $formats->whereIn('shared_id_bigint', $this->getUsedFormatIds());
        }

        if ($keyStrings) {
            $formats = $formats->whereIn('key_string', $keyStrings);
        }

        return $formats;
    }

    private function getUsedFormatIds(): array
    {
        $uniqueFormatIds = [];

        $results = Meeting::query()->pluck('formats');
        foreach ($results as $formatIds) {
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
