<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\FormatResource;
use App\Interfaces\FormatRepositoryInterface;
use App\Models\Format;
use App\Models\FormatType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $validated = $this->validateInputs($request);
        $sharedFormatsValues = $this->buildValuesArray($validated);
        $format = $this->formatRepository->create($sharedFormatsValues);
        return new FormatResource($format);
    }

    public function update(Request $request, Format $format)
    {
        $validated = $this->validateInputs($request);
        $sharedFormatsValues = $this->buildValuesArray($validated);
        $this->formatRepository->update($format->shared_id_bigint, $sharedFormatsValues);
        return response()->noContent();
    }
    public function partialUpdate(Request $request, Format $format)
    {
        $request->merge(
            collect(['worldId', 'type', 'translations'])
                ->mapWithKeys(function ($fieldName, $_) use ($request, $format) {
                    if ($fieldName == 'worldId') {
                        return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $format->worldid_mixed];
                    } elseif ($fieldName == 'type') {
                        return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : (!is_null($format->format_type_enum) ? FormatType::getApiEnumFromKey($format->format_type_enum): null)];
                    } else {
                        return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $format->translations->map(function ($translation) {
                            return [
                                'key' => $translation->key_string,
                                'name' => $translation->name_string,
                                'description' => $translation->description_string,
                                'language' => $translation->lang_enum,
                            ];
                        })->toArray()];
                    }
                })
                ->toArray()
        );

        $validated = $this->validateInputs($request);
        $sharedFormatsValues = $this->buildValuesArray($validated);
        $this->formatRepository->update($format->shared_id_bigint, $sharedFormatsValues);
        return response()->noContent();
    }


    public function destroy(Request $request, Format $format)
    {
        $request->merge(['id' => $format->shared_id_bigint]);
        $request->validate(['id' => [Rule::notIn([
            $this->formatRepository->getVirtualFormat()->shared_id_bigint,
            $this->formatRepository->getTemporarilyClosedFormat()->shared_id_bigint,
            $this->formatRepository->getHybridFormat()->shared_id_bigint,
        ])]]);
        $this->formatRepository->delete($format->shared_id_bigint);
        return response()->noContent();
    }

    private function validateInputs(Request $request)
    {
        return collect($request->validate([
            'worldId' => 'nullable|string|max:30',
            'type' => ['nullable', Rule::in(FormatType::getApiEnums())],
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
            'translations.*.key' => ['required', 'string', 'max:10', Rule::notIn(['VM', 'HY', 'TC'])],
            'translations.*.name' => 'required|string|max:255',
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]));
    }

    private function buildValuesArray(Collection $validated)
    {
        return collect($validated['translations'])->map(function ($translation) use ($validated) {
            return [
                'format_type_enum' => isset($validated['type']) ? FormatType::getKeyFromApiEnum($validated['type']) : null,
                'worldid_mixed' => $validated['worldId'] ?? null,
                'lang_enum' => $translation['language'],
                'key_string' => $translation['key'],
                'name_string' => $translation['name'],
                'description_string' => $translation['description'],
            ];
        })->toArray();
    }
}
