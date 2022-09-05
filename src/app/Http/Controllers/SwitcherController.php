<?php

namespace App\Http\Controllers;

use App\Http\Resources\FormatResource;
use App\Http\Resources\ServiceBodyResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Legacy\LegacyController;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;

class SwitcherController extends Controller
{
    private FormatRepositoryInterface $formatRepository;
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(
        FormatRepositoryInterface $formatRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository
    ) {
        $this->formatRepository = $formatRepository;
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function get(Request $request, string $dataFormat)
    {
        $switcher = $request->input('switcher');

        if (in_array($switcher, ['GetFormats', 'GetServiceBodies'])) {
            if ($dataFormat != 'json' && $dataFormat != 'jsonp') {
                abort(404, 'This endpoint only supports the \'json\' and \'jsonp\' data formats.');
            }

            if ($switcher == 'GetFormats') {
                $response = $this->getFormats($request);
            } else {
                $response = $this->getServiceBodies($request);
            }

            return $dataFormat == 'jsonp'
                ? $response->withCallback($request->input('callback', 'callback'))
                : $response;
        }

        return LegacyController::handle($request);
    }

    private function getFormats(Request $request): JsonResponse
    {
        $langEnums = $request->input('lang_enum', ['en']);
        if (!is_array($langEnums)) {
            $langEnums = [$langEnums];
        }

        $keyStrings = $request->input('key_strings', []);
        if (!is_array($keyStrings)) {
            $keyStrings = [$keyStrings];
        }

        $showAll = $request->input('show_all') == '1';

        $formats = $this->formatRepository->getFormats($langEnums, $keyStrings, $showAll);
        return FormatResource::collection($formats->get())->response();
    }

    private function getServiceBodies(Request $request): JsonResponse
    {
        $serviceBodyIds = $request->input('services', []);
        if (!is_array($serviceBodyIds)) {
            $serviceBodyIds = [$serviceBodyIds];
        }

        $includeIds = [];
        $excludeIds = [];
        foreach ($serviceBodyIds as $serviceBodyId) {
            $serviceBodyId = intval($serviceBodyId);
            if ($serviceBodyId >= 0) {
                $includeIds[] = $serviceBodyId;
            } else {
                $excludeIds[] = abs($serviceBodyId);
            }
        }

        $recurseChildren = $request->input('recurse') == '1';
        $recurseParents = $request->input('parents') == '1';

        $serviceBodies = $this->serviceBodyRepository->getServiceBodies($includeIds, $excludeIds, $recurseChildren, $recurseParents);
        return ServiceBodyResource::collection($serviceBodies->get())->response();
    }
}
