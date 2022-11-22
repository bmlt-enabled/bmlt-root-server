<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\RootServerResource;
use App\Interfaces\RootServerRepositoryInterface;
use App\Models\RootServer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RootServerController extends ResourceController
{
    private RootServerRepositoryInterface $rootServerRepository;

    public function __construct(RootServerRepositoryInterface $rootServerRepository)
    {
        $this->rootServerRepository = $rootServerRepository;
        $this->authorizeResource(RootServer::class);
    }

    public function index(Request $request)
    {
        $rootServers = $this->rootServerRepository->search();
        return RootServerResource::collection($rootServers);
    }

    public function show(RootServer $rootServer)
    {
        return new RootServerResource($rootServer);
    }


    public function store(Request $request)
    {
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArray($validated);
        $rootServer = $this->rootServerRepository->create($values);
        return new RootServerResource($rootServer);
    }

    public function update(Request $request, RootServer $rootServer)
    {
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArray($validated);
        $this->rootServerRepository->update($rootServer->id, $values);
        return response()->noContent();
    }

    public function partialUpdate(Request $request, RootServer $rootServer)
    {
        $request->merge(
            collect(['sourceId', 'name', 'url'])
                ->mapWithKeys(function ($fieldName, $_) use ($request, $rootServer) {
                    if ($fieldName == 'sourceId') {
                        return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $rootServer->source_id];
                    } else {
                        return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $rootServer->{$fieldName}];
                    }
                })
                ->toArray()
        );
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArray($validated);
        $this->rootServerRepository->update($rootServer->id, $values);
        return response()->noContent();
    }

    public function destroy(RootServer $rootServer)
    {
        $this->rootServerRepository->delete($rootServer->id);
        return response()->noContent();
    }

    private function validateInputs(Request $request): Collection
    {
        return collect($request->validate([
            'sourceId' => 'required|int|unique:root_servers,source_id|min:1',
            'name' => 'required|string|unique:root_servers,name|max:255',
            'url' => 'required|url|unique:root_severs,url|max:255',
        ]));
    }

    private function buildValuesArray(Collection $validated)
    {
        return [
            'source_id' => $validated['sourceId'],
            'name' => $validated['name'],
            'url'=> $validated['url'],
        ];
    }
}
