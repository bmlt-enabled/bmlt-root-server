<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\ServiceBodyResource;
use App\Http\Responses\JsonResponse;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ServiceBodyController extends ResourceController
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
            $serviceBodyIds = $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->toArray();
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
        $validated = $this->validateInputs($request);
        $values = [
            'sb_owner' => $validated['parentId'] ?? 0,
            'name_string' => $validated['name'],
            'description_string' => $validated['description'] ?? '',
            'sb_type' => $validated['type'],
            'principal_user_bigint' => $validated['adminUserId'],
            'editors_string' => collect($validated['assignedUserIds'])->map(fn ($v) => strval($v))->join(','),
            'uri_string' => $validated['url'] ?? null,
            'sb_meeting_email' => $validated['email'] ?? '',
            'kml_file_uri_string' => $validated['helpline'] ?? null,
            'worldid_mixed' => $validated['worldId'] ?? null,
        ];
        $serviceBody = $this->serviceBodyRepository->create($values);
        return new ServiceBodyResource($serviceBody);
    }

    public function update(Request $request, ServiceBody $serviceBody)
    {
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArrayForUpdate($request, $validated);
        $this->serviceBodyRepository->update($serviceBody->id_bigint, $values);
        return response()->noContent();
    }

    public function partialUpdate(Request $request, ServiceBody $serviceBody)
    {
        $request->merge(
            collect(ServiceBody::FIELDS)
                ->mapWithKeys(function ($fieldName, $_) use ($request, $serviceBody) {
                    if ($fieldName == 'sb_owner') {
                        return ['parentId' => $request->has('parentId') ? $request->input('parentId') : ($serviceBody->owner_id_bigint == 0 ? null : $serviceBody->owner_id_bigint)];
                    } elseif ($fieldName == 'sb_type') {
                        return ['type' => $request->has('type') ? $request->input('type') : $serviceBody->sb_type];
                    } elseif ($fieldName == 'principal_user_bigint') {
                        return ['adminUserId' => $request->has('adminUserId') ? $request->input('adminUserId') : $serviceBody->principal_user_bigint];
                    } elseif ($fieldName == 'name_string') {
                        return ['name' => $request->has('name') ? $request->input('name') : $serviceBody->name_string];
                    } elseif ($fieldName == 'description_string') {
                        return ['description' => $request->has('description') ? $request->input('description') : $serviceBody->description_string];
                    } elseif ($fieldName == 'editors_string') {
                        return ['assignedUserIds' => $request->has('assignedUserIds') ? $request->input('assignedUserIds') : (empty($serviceBody->editors_string) ? [] : array_map(fn ($v) => intval($v), explode(',', $serviceBody->editors_string)))];
                    } elseif ($fieldName == 'uri_string') {
                        return ['url' => $request->has('url') ? $request->input('url') : $serviceBody->uri_string];
                    } elseif ($fieldName == 'sb_meeting_email') {
                        return ['email' => $request->has('email') ? $request->input('email') : $serviceBody->sb_meeting_email];
                    } elseif ($fieldName == 'kml_file_uri_string') {
                        return ['helpline' => $request->has('helpline') ? $request->input('helpline') : $serviceBody->kml_file_uri_string];
                    } elseif ($fieldName == 'worldid_mixed') {
                        return ['worldId' => $request->has('worldId') ? $request->input('worldId') : $serviceBody->worldid_mixed];
                    } else {
                        return [null => null];
                    }
                })
                ->reject(fn ($_, $key) => empty($key))
                ->toArray()
        );
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArrayForUpdate($request, $validated);
        $this->serviceBodyRepository->update($serviceBody->id_bigint, $values);
        return response()->noContent();
    }

    public function destroy(ServiceBody $serviceBody)
    {
        if ($serviceBody->children()->exists() || $serviceBody->meetings()->exists()) {
            return new JsonResponse([
                'message' => 'You cannot delete a service body while other service bodies or meetings are assigned to it.'
            ], 409);
        }

        $this->serviceBodyRepository->delete($serviceBody->id_bigint);

        return response()->noContent();
    }

    private function validateInputs(Request $request): Collection
    {
        return collect($request->validate([
            'parentId' => 'nullable|present|int|exists:comdef_service_bodies,id_bigint',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(ServiceBody::VALID_SB_TYPES)],
            'adminUserId' => 'required|exists:comdef_users,id_bigint',
            'assignedUserIds' => 'present|array',
            'assignedUserIds.*' => 'int|exists:comdef_users,id_bigint',
            'url' => 'nullable|url|max:255',
            'helpline' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'worldId' => 'nullable|string|max:30',
        ]));
    }

    private function buildValuesArrayForUpdate(Request $request, Collection $validated): array
    {
        $requestUser = $request->user();
        $isAdmin = $requestUser->isAdmin();
        return collect(ServiceBody::FIELDS)
            ->mapWithKeys(function ($fieldName, $_) use ($validated, $isAdmin) {
                if ($isAdmin) {
                    if ($fieldName == 'sb_owner') {
                        return [$fieldName => $validated['parentId'] ?? 0];
                    } elseif ($fieldName == 'sb_type') {
                        return [$fieldName => $validated['type']];
                    } elseif ($fieldName == 'principal_user_bigint') {
                        return [$fieldName => $validated['adminUserId']];
                    }
                }
                if ($fieldName == 'name_string') {
                    return ['name_string' => $validated['name']];
                } elseif ($fieldName == 'description_string') {
                    return [$fieldName => $validated['description'] ?? ''];
                } elseif ($fieldName == 'editors_string') {
                    return [$fieldName => collect($validated['assignedUserIds'])->map(fn ($v) => strval($v))->join(',')];
                } elseif ($fieldName == 'uri_string') {
                    return [$fieldName => $validated['url'] ?? null];
                } elseif ($fieldName == 'sb_meeting_email') {
                    return [$fieldName => $validated['email'] ?? ''];
                } elseif ($fieldName == 'kml_file_uri_string') {
                    return [$fieldName => $validated['helpline'] ?? null];
                } elseif ($fieldName == 'worldid_mixed') {
                    return [$fieldName => $validated['worldId'] ?? null];
                } else {
                    return [null => null];
                }
            })
            ->reject(fn ($_, $key) => empty($key))
            ->toArray();
    }
}
