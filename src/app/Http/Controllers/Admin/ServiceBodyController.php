<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ServiceBodyResource;
use App\Http\Responses\JsonResponse;
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
            'sb_owner' => $validated['parentId'] ?? 0,
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
        if ($request->method() == 'PUT') {
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
        } else {
            $validated = $request->validate([
                'parentId' => 'nullable|int|exists:comdef_service_bodies,id_bigint',
                'name' => 'string|max:255',
                'description' => 'string',
                'type' => Rule::in(ServiceBody::VALID_SB_TYPES),
                'userId' => 'exists:comdef_users,id_bigint',
                'editorUserIds' => 'array',
                'editorUserIds.*' => 'int|exists:comdef_users,id_bigint',
                'url' => 'url|max:255',
                'helpline' => 'string|max:255',
                'email' => 'email|max:255',
                'worldId' => 'string|max:30',
            ]);
        }

        $values = collect($validated)->mapWithKeys(function ($value, $key) use ($request) {
            if ($request->user()->isAdmin()) {
                if ($key == 'parentId') {
                    return ['sb_owner' => $value ?? 0];
                } elseif ($key == 'type') {
                    return ['sb_type' => $value];
                } elseif ($key == 'userId') {
                    return ['principal_user_bigint' => $value];
                }
            }
            if ($key == 'name') {
                return ['name_string' => $value];
            } elseif ($key == 'description') {
                return ['description_string' => $value];
            } elseif ($key == 'editorUserIds') {
                return ['editors_string' => collect($value)->map(fn ($v) => strval($v))->join(',')];
            } elseif ($key == 'url') {
                return ['uri_string' => $value ?? null];
            } elseif ($key == 'email') {
                return ['sb_meeting_email' => $value ?? ''];
            } elseif ($key == 'helpline') {
                return ['kml_file_uri_string' => $value ?? null];
            } elseif ($key == 'worldId') {
                return ['worldid_mixed' => $value ?? null];
            } else {
                return [null => null];
            }
        })->reject(fn ($_, $key) => empty($key))->toArray();

        if (!empty($values)) {
            $this->serviceBodyRepository->update($serviceBody->id_bigint, $values);
        }

        return response()->noContent();
    }

    public function destroy(ServiceBody $serviceBody)
    {
        if ($serviceBody->children()->exists() || $serviceBody->meetings()->exists()) {
            return new JsonResponse([
                'message' => 'You cannot delete a service body while other service bodies or meetings are assigned to it.'
            ], 409);
        }

        $serviceBody->delete();

        return response()->noContent();
    }
}
