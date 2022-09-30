<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\FormatResource;
use App\Interfaces\FormatRepositoryInterface;
use App\Models\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormatController extends ResourceController
{
    private FormatRepositoryInterface $formatRepository;

    public function __construct(FormatRepositoryInterface $formatRepository)
    {
        $this->formatRepository = $formatRepository;
        $this->authorizeResource(Format::class);
    }

    public function index(Request $request)
    {
        $formats = $this->formatRepository->getAsTranslations();
        return FormatResource::collection($formats);
    }

    public function show(Format $format)
    {
        return new FormatResource($format);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP))],
            'translations' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach (collect($value)->groupBy('language') as $translations) {
                        if (count($translations) > 1) {
                            $fail(':attribute may have only one translation per language.');
                        }
                    }
                }
            ],
            'translations.*.key' => 'required|string|max:30',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]);

        $sharedFormatsValues = collect($validated['translations'])->map(function ($translation) use ($validated) {
            return [
                'format_type_enum' => !is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null,
                'worldid_mixed' => $validated['worldId'] ?? null,
                'lang_enum' => $translation['language'],
                'key_string' => $translation['key'],
                'name_string' => $translation['name'],
                'description_string' => $translation['description'],
            ];
        })->toArray();

        $format = $this->formatRepository->create($sharedFormatsValues);

        return new FormatResource($format);
    }

    public function update(Request $request, Format $format)
    {
        $validated = $request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP))],
            'translations' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    foreach (collect($value)->groupBy('language') as $translations) {
                        if (count($translations) > 1) {
                            $fail(':attribute may have only one translation per language.');
                        }
                    }
                }
            ],
            'translations.*.key' => 'required|string|max:30',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]);

        $sharedFormatsValues = collect($validated['translations'])->map(function ($translation) use ($validated) {
            return [
                'format_type_enum' => !is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null,
                'worldid_mixed' => $validated['worldId'] ?? null,
                'lang_enum' => $translation['language'],
                'key_string' => $translation['key'],
                'name_string' => $translation['name'],
                'description_string' => $translation['description'],
            ];
        })->toArray();

        $this->formatRepository->update($format->shared_id_bigint, $sharedFormatsValues);

        return response()->noContent();
    }

    public function partialUpdate(Request $request, Format $format)
    {
        $validated = $request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(array_keys(Format::TYPE_TO_COMDEF_TYPE_MAP))],
            'translations' => [
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    foreach (collect($value)->groupBy('language') as $translations) {
                        if (count($translations) > 1) {
                            $fail(':attribute may have only one translation per language.');
                        }
                    }
                }
            ],
            'translations.*.key' => 'required|string|max:30',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]);

        $sharedFormatsValues = [];
        if (array_key_exists('translations', $validated)) {
            foreach ($validated['translations'] as $translation) {
                $sharedFormatsValues[] = [
                    'format_type_enum' => array_key_exists('type', $validated) ? (!is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null) : $format->format_type_enum,
                    'worldid_mixed' => array_key_exists('worldId', $validated) ? $validated['worldId'] ?? null : $format->worldid_mixed,
                    'lang_enum' => $translation['language'],
                    'key_string' => $translation['key'],
                    'name_string' => $translation['name'],
                    'description_string' => $translation['description'],
                ];
            }
        } else {
            foreach ($format->translations as $translation) {
                $sharedFormatsValues[] = [
                    'format_type_enum' => array_key_exists('type', $validated) ? (!is_null($validated['type']) ? Format::TYPE_TO_COMDEF_TYPE_MAP[$validated['type']] : null) : $format->format_type_enum,
                    'worldid_mixed' => array_key_exists('worldId', $validated) ? $validated['worldId'] ?? null : $format->worldid_mixed,
                    'lang_enum' => $translation->lang_enum,
                    'key_string' => $translation->key_string,
                    'name_string' => $translation->name_string,
                    'description_string' => $translation->description_string,
                ];
            }
        }

        $this->formatRepository->update($format->shared_id_bigint, $sharedFormatsValues);

        return response()->noContent();
    }

    public function destroy(Format $format)
    {
        $this->formatRepository->delete($format->shared_id_bigint);
        return response()->noContent();
    }
}
