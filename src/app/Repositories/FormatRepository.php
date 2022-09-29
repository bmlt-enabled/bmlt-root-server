<?php

namespace App\Repositories;

use App\Interfaces\FormatRepositoryInterface;
use App\Models\Format;
use App\Models\Meeting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormatRepository implements FormatRepositoryInterface
{
    public function search(array $langEnums = null, array $keyStrings = null, bool $showAll = false, Collection $meetings = null): Collection
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

    public function create(array $sharedFormatsValues): Format
    {
        return DB::transaction(function () use ($sharedFormatsValues) {
            $sharedIdBigint = Format::query()->max('shared_id_bigint') + 1;
            $format = null;
            foreach ($sharedFormatsValues as $values) {
                $values['shared_id_bigint'] = $sharedIdBigint;
                $format = Format::create($values);
                //$this->>saveChange(null, $format);
            }
            return $format;
        });
    }

    public function update(int $sharedId, array $sharedFormatsValues): bool
    {
        return DB::transaction(function () use ($sharedId, $sharedFormatsValues) {
            $oldFormats = Format::query()
                ->where('shared_id_bigint', $sharedId)
                ->get()
                ->mapWithKeys(fn ($fmt, $_) => [$fmt->lang_enum => $fmt]);

            if ($oldFormats->isNotEmpty()) {
                Format::query()->where('shared_id_bigint', $sharedId)->delete();
                foreach ($sharedFormatsValues as $values) {
                    $values['shared_id_bigint'] = $sharedId;
                    $newFormat = Format::create($values);
                    $oldFormat = $oldFormats->get($newFormat->lang_enum);
                    if (!is_null($oldFormat)) {
                        //$this->saveChange($oldFormat, $newFormat);
                    } else {
                        //$this->saveChange(null, $newFormat);
                    }
                }
                return true;
            }

            return false;
        });
    }

    public function delete(int $sharedId): bool
    {
        return DB::transaction(function () use ($sharedId) {
            $formats = Format::query()->where('shared_id_bigint', $sharedId)->get();
            if ($formats->isNotEmpty()) {
                foreach ($formats as $format) {
                    $format->delete();
                    //$this->saveChange($format, null);
                }
                return true;
            }
            return false;
        });
    }

    public function getAsTranslations(): Collection
    {
        return Format::query()
            ->with(['translations'])
            ->whereIn('id', function ($query) {
                $query
                    ->selectRaw(DB::raw('MIN(id)'))
                    ->from('comdef_formats')
                    ->groupBy('shared_id_bigint');
            })
            ->get();
    }
}
