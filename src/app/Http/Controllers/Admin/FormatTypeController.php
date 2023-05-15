<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\FormatTypeResource;
use App\Interfaces\FormatTypeRepositoryInterface;
use App\Models\FormatType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class FormatTypeController extends ResourceController
{
    private FormatTypeRepositoryInterface $formatTypeRepository;

    public function __construct(FormatTypeRepositoryInterface $formatTypeRepository)
    {
        $this->formatTypeRepository = $formatTypeRepository;
        $this->authorizeResource(FormatType::class);
    }


    public function index(Request $request)
    {
        $formatTypes = $this->formatTypeRepository->getAsTranslations();
        return FormatTypeResource::collection($formatTypes);
    }


    public function show(FormatType $formatType)
    {
        return new FormatTypeResource($formatType);
    }


    public function store(Request $request)
    {
        $validated = $this->validateInputs($request);
        $sharedFormatsValues = $this->buildValuesArray($validated);
        $format = $this->formatTypeRepository->create($sharedFormatsValues);
        return new FormatTypeResource($formatType);
    }

    public function update(Request $request, FormatType $formatType)
    {
        $validated = $this->validateInputs($request);
        $sharedFormatsValues = $this->buildValuesArray($validated);
        $this->formatTypeRepository->update($formatType->shared_id_bigint, $sharedFormatsValues);
        return response()->noContent();
    }

    public function partialUpdate(Request $request, FormatType $formatType)
    {
        // TODO
        return response()->noContent();
    }


    public function destroy(Request $request, FormatType $formatType)
    {
        // TODO
        return response()->noContent();
    }

    private function validateInputs(Request $request)
    {
        return collect($request->validate([
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
            'translations.*.description' => 'required|string|max:255',
            'translations.*.language' => 'required|string|max:7',
        ]));
    }

    private function buildValuesArray(Collection $validated)
    {
        return collect($validated['translations'])->map(function ($translation) use ($validated) {
            return [
                'key_string' => $translation['key'],
                'description_string' => $translation['description'],
            ];
        })->toArray();
    }
}
