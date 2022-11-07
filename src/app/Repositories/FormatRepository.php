<?php

namespace App\Repositories;

use App\Interfaces\FormatRepositoryInterface;
use App\Models\Change;
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

    public function getVirtualFormat(): Format
    {
        return Format::query()
            ->where('key_string', 'VM')
            ->where('lang_enum', 'en')
            ->firstOrFail();
    }

    public function getHybridFormat(): Format
    {
        return Format::query()
            ->where('key_string', 'HY')
            ->where('lang_enum', 'en')
            ->firstOrFail();
    }

    public function getTemporarilyClosedFormat(): Format
    {
        return Format::query()
            ->where('key_string', 'TC')
            ->where('lang_enum', 'en')
            ->firstOrFail();
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
                $this->saveChange(null, $format);
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

            Format::query()->where('shared_id_bigint', $sharedId)->delete();

            // save changes for deleted formats
            foreach ($oldFormats as $oldFormat) {
                $isDeleted = collect($sharedFormatsValues)
                    ->filter(fn ($values) => $values['lang_enum'] == $oldFormat->lang_enum)
                    ->isEmpty();
                if ($isDeleted) {
                    $this->saveChange($oldFormat, null);
                }
            }

            foreach ($sharedFormatsValues as $values) {
                $values['shared_id_bigint'] = $sharedId;
                $newFormat = Format::create($values);
                $oldFormat = $oldFormats->get($newFormat->lang_enum);
                if (is_null($oldFormat)) {
                    $this->saveChange(null, $newFormat);
                } else {
                    $this->saveChange($oldFormat, $newFormat);
                }
            }

            return true;
        });
    }

    public function delete(int $sharedId): bool
    {
        return DB::transaction(function () use ($sharedId) {
            $formats = Format::query()->where('shared_id_bigint', $sharedId)->get();
            if ($formats->isNotEmpty()) {
                foreach ($formats as $format) {
                    $format->delete();
                    $this->saveChange($format, null);
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

    private function saveChange(?Format $beforeFormat, ?Format $afterFormat): void
    {
        $beforeObject = !is_null($beforeFormat) ? $this->serializeForChange($beforeFormat) : null;
        $afterObject = !is_null($afterFormat) ? $this->serializeForChange($afterFormat) : null;
        if (!is_null($beforeObject) && !is_null($afterObject) && $beforeObject == $afterObject) {
            // nothing actually changed, don't save a record
            return;
        }

        Change::create([
            'user_id_bigint' => request()->user()->id_bigint,
            'service_body_id_bigint' => $afterFormat?->shared_id_bigint ?? $beforeFormat->shared_id_bigint,
            'lang_enum' => $beforeFormat?->lang_enum ?: $afterFormat?->lang_enum,
            'object_class_string' => 'c_comdef_format',
            'before_id_bigint' => $beforeFormat?->shared_id_bigint,
            'before_lang_enum' => $beforeFormat?->lang_enum,
            'after_id_bigint' => $afterFormat?->shared_id_bigint,
            'after_lang_enum' => $afterFormat?->lang_enum,
            'change_type_enum' => is_null($beforeFormat) ? 'comdef_change_type_new' : (is_null($afterFormat) ? 'comdef_change_type_delete' : 'comdef_change_type_change'),
            'before_object' => $beforeObject,
            'after_object' => $afterObject,
        ]);
    }

    private function serializeForChange(Format $format): string
    {
        return serialize([
            $format->shared_id_bigint,
            $format->format_type_enum,
            $format->key_string,
            $format->icon_blob,
            $format->worldid_mixed,
            $format->lang_enum,
            $format->name_string,
            $format->description_string,
        ]);
    }
}
