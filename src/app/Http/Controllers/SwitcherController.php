<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceBodyResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Legacy\LegacyController;
use App\Interfaces\ServiceBodyRepositoryInterface;

class SwitcherController extends Controller
{
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function get(Request $request, string $dataFormat)
    {
        $switcher = $request->input('switcher');

        if ($switcher == 'GetServiceBodies') {
            if ($dataFormat != 'json' && $dataFormat != 'jsonp') {
                abort(404, 'GetServiceBodies only supports the \'json\' and \'jsonp\' data formats.');
            }

            $response = $this->getServiceBodies($request);
            return $dataFormat == 'jsonp'
                ? $response->withCallback($request->input('callback', 'callback'))
                : $response;
        }

        return LegacyController::handle($request);
    }

    public function getServiceBodies(Request $request): JsonResponse
    {
        $serviceBodyIds = $request->input('services');
        if (!is_array($serviceBodyIds)) {
            $serviceBodyIds = is_null($serviceBodyIds) ? [] : [$serviceBodyIds];
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
