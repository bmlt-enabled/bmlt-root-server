<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Http\Resources\Legacy\TomatoRestApiFormatResource;
use App\Interfaces\FormatRepositoryInterface;
use Illuminate\Http\Request;
use Spatie\ValidationRules\Rules\Delimited;

class TomatoRestApiController extends Controller
{
    private FormatRepositoryInterface $formatRepository;

    public function __construct(FormatRepositoryInterface $formatRepository)
    {
        $this->formatRepository = $formatRepository;
    }

    public function formats(Request $request)
    {
        if (!legacy_config('is_aggregator_mode_enabled')) {
            abort(404);
        }

        $validated = $request->validate(['id__in' => [new Delimited('int')]]);
        if (empty($validated['id__in'])) {
            return TomatoRestApiFormatResource::collection([]);
        }

        $formatIds = array_map(fn ($v) => intval($v), explode(',', $validated['id__in']));
        $formats = $this->formatRepository->getAsTranslations(formatIds: $formatIds);
        return TomatoRestApiFormatResource::collection($formats);
    }

    public function format(int $formatId)
    {
        if (!legacy_config('is_aggregator_mode_enabled')) {
            abort(404);
        }

        $formats = $this->formatRepository->getAsTranslations(formatIds: [$formatId]);
        if ($formats->isEmpty()) {
            abort(404);
        }

        return new TomatoRestApiFormatResource($formats[0]);
    }
}
