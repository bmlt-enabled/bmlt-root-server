<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\RootServerResource;
use App\Http\Responses\JsonResponse;
use App\Interfaces\RootServerRepositoryInterface;
use App\Models\RootServer;
use Illuminate\Http\Request;

class RootServerController extends ResourceController
{
    private RootServerRepositoryInterface $rootServerRepository;

    public function __construct(RootServerRepositoryInterface $rootServerRepository)
    {
        $this->rootServerRepository = $rootServerRepository;
    }

    public function index(Request $request)
    {
        if (!legacy_config('is_aggregator_mode_enabled')) {
            return new JsonResponse(['message' => 'Endpoint is unavailable when aggregator mode is disabled.'], 404);
        }

        $rootServers = $this->rootServerRepository->search();
        return RootServerResource::collection($rootServers);
    }

    public function show(RootServer $rootServer)
    {
        if (!legacy_config('is_aggregator_mode_enabled')) {
            return new JsonResponse(['message' => 'Endpoint is unavailable when aggregator mode is disabled.'], 404);
        }

        return new RootServerResource($rootServer);
    }
}
