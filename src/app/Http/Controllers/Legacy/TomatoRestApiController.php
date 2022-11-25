<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Http\Resources\Legacy\TomatoRestApiFormatResource;
use App\Http\Responses\JsonResponse;
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
        if (!legacy_config('aggregator_mode_enabled')) {
            return new JsonResponse(['message' => 'Endpoint is unavailable when aggregator mode is disabled.'], 404);
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
        if (!legacy_config('aggregator_mode_enabled')) {
            return new JsonResponse(['message' => 'Endpoint is unavailable when aggregator mode is disabled.'], 404);
        }

        $formats = $this->formatRepository->getAsTranslations(formatIds: [$formatId]);
        if ($formats->isEmpty()) {
            return new JsonResponse(['message' => 'Not Found'], 404);
        }

        return new TomatoRestApiFormatResource($formats[0]);
    }
}
