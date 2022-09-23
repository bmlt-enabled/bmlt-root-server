<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ServiceBodyResource;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            $serviceBodyIds = $this->serviceBodyRepository->getUserServiceBodyIds($user->id_bigint)->toArray();
            if (empty($serviceBodyIds)) {
                return ServiceBodyResource::collection([]);
            }
        }
        $serviceBodies = $this->serviceBodyRepository->search(includeIds: $serviceBodyIds);

        return ServiceBodyResource::collection($serviceBodies);
    }

    public function show(ServiceBody $serviceBody)
    {
        return new ServiceBodyResource($serviceBody);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parentId' => 'nullable|present|int|exists:comdef_service_bodies,id_bigint',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => ['required', Rule::in(ServiceBody::VALID_SB_TYPES)],
            'userId' => 'required|exists:comdef_users,id_bigint',
            'editorUserIds' => 'present|array',
            'editorUserIds.*' => 'int|exists:comdef_users,id_bigint',
            'url' => 'url|max:255',
            'helpline' => 'string|max:255',
            'email' => 'email|max:255',
            'worldId' => 'string|max:30',
        ]);

        $serviceBody = $this->serviceBodyRepository->create([
            'sb_owner' => $validated['parentId'] ?? null,
            'name_string' => $validated['name'],
            'description_string' => $validated['description'],
            'sb_type' => $validated['type'],
            'principal_user_bigint' => $validated['userId'],
            'editors_string' => collect($validated['editorUserIds'])->map(fn ($v) => strval($v))->join(','),
            'uri_string' => $validated['url'] ?? null,
            'sb_meeting_email' => $validated['email'] ?? '',
            'kml_file_uri_string' => $validated['helpline'] ?? null,
            'worldid_mixed' => $validated['worldId'] ?? null,
        ]);

        return new ServiceBodyResource($serviceBody);
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
