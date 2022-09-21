<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ServiceBodyResource;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;
use Illuminate\Http\Request;

class ServiceBodyController extends Controller
{
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
        $this->authorizeResource(ServiceBody::class, 'serviceBody');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $serviceBodyIds = [];
        if (!$user->isAdmin()) {
            $serviceBodyIds = $this->serviceBodyRepository->getServiceBodyIdsForUser($user->id_bigint)->toArray();
            if (empty($serviceBodyIds)) {
                return ServiceBodyResource::collection([]);
            }
        }
        $serviceBodies = $this->serviceBodyRepository->getServiceBodies(includeIds: $serviceBodyIds);

        return ServiceBodyResource::collection($serviceBodies);
    }

    public function show(ServiceBody $serviceBody)
    {
        return new ServiceBodyResource($serviceBody);
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, ServiceBody $serviceBody)
    {
        //
    }

    public function destroy(ServiceBody $serviceBody)
    {
        //
    }
}
