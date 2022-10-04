<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\ServiceBodyResource;
use App\Http\Responses\JsonResponse;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\ServiceBody;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="ServiceBodyResponse",
 *     @OA\Property(property="id", type="integer", example="0"),
 *     @OA\Property(property="parentId", type="integer", example="0"),
 *     @OA\Property(property="name", type="string", example="string"),
 *     @OA\Property(property="description", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="userId", type="integer", example="0"),
 *     @OA\Property(
 *        property="editorUserIds",
 *        type="array",
 *        @OA\Items(
 *           type="integer",
 *           example="0",
 *        )
 *     ),
 *     @OA\Property(property="url", type="string", example="string"),
 *     @OA\Property(property="helpline", type="string", example="string"),
 *     @OA\Property(property="email", type="string", example="string"),
 *     @OA\Property(property="worldId", type="string", example="string")
 * ),
 * @OA\Schema(
 *     schema="ServiceBodiesResponse",
 *             type="array",
 *                example={{
 *                  "id": 0,
 *                  "parentId": 0,
 *                  "name": "string",
 *                  "description": "string",
 *                  "type": "string",
 *                  "userId": 0,
 *                  "editorUserIds": {1},
 *                  "url": "string",
 *                  "helpline": "string",
 *                  "email": "string",
 *                  "worldId": "string",
 *                }},
 *                @OA\Items(ref="#/components/schemas/ServiceBodyResponse"),
 * ),
 * @OA\Schema(
 *     schema="ServiceErrorUnauthenticated",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(
 *     schema="ServiceErrorUnauthorized",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * )
 * @OA\Schema(
 *     schema="NoServiceBodyExists",
 *      description="Returns when no user exists.",
 *      @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\ServiceBody]"),
 * )
 */
class ServiceBodyController extends ResourceController
{
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
        $this->authorizeResource(ServiceBody::class, 'serviceBody');
    }

    /**
     * @OA\Get(
     * path="/api/v1/servicebodies",
     * summary="Retrieve service bodies",
     * description="Retrieve service bodies for authenticated user.",
     * operationId="getServiceBodies",
     * tags={"servicebodies"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/ServiceBodiesResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/ServiceErrorUnauthenticated")
     *   )
     * )
     */

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

    /**
     * @OA\Get(
     * path="/api/v1/servicebodies/{serviceBodyId}",
     * summary="Retrieve a single service body",
     * description="Retrieve a single service body by id.",
     * operationId="getServiceBody",
     * tags={"servicebodies"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of service body",
     *    in="path",
     *    name="serviceBodyId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/ServiceBodyResponse")
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ServiceErrorUnauthenticated"),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ServiceErrorUnauthorized"),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *  )
     * )
     */
    public function show(ServiceBody $serviceBody)
    {
        return new ServiceBodyResource($serviceBody);
    }

    /**
     * @OA\Post(
     * path="/api/v1/servicebodies",
     * summary="Create Service Body",
     * description="Cretaes a service body.",
     * operationId="createServiceBody",
     * tags={"servicebodies"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in service body object",
     *    @OA\JsonContent(
     *       required={"name","description"},
     *     @OA\Property(property="parentId", type="integer", example="0"),
     *     @OA\Property(property="name", type="string", example="string"),
     *     @OA\Property(property="description", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(property="userId", type="integer", example="0"),
     *     @OA\Property(
     *        property="editorUserIds",
     *        type="array",
     *        @OA\Items(
     *           type="integer",
     *           example="0",
     *        )
     *     ),
     *     @OA\Property(property="url", type="string", example="string"),
     *     @OA\Property(property="helpline", type="string", example="string"),
     *     @OA\Property(property="email", type="string", example="string"),
     *     @OA\Property(property="worldId", type="string", example="string")
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Returns when POST is successful.",
     *    @OA\JsonContent(ref="#/components/schemas/ServiceBodyResponse")
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The parent id field must be present. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="parentId",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The parent id field must be present.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="editorUserIds",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The editor user ids field must be present.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/ServiceErrorUnauthenticated")
     * )
     * )
     */
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

    /**
     * @OA\Put(
     * path="/api/v1/servicebodies/{serviceBodyId}",
     * summary="Update single Service Body",
     * description="Updates a single service body.",
     * operationId="updateServiceBody",
     * tags={"servicebodies"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of service body",
     *    in="path",
     *    name="serviceBodyId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in service body object.",
     *    @OA\JsonContent(
     *     @OA\Property(property="parentId", type="integer", example="0"),
     *     @OA\Property(property="name", type="string", example="string"),
     *     @OA\Property(property="description", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(property="userId", type="integer", example="0"),
     *     @OA\Property(
     *        property="editorUserIds",
     *        type="array",
     *        @OA\Items(
     *           type="integer",
     *           example="0",
     *        )
     *     ),
     *     @OA\Property(property="url", type="string", example="string"),
     *     @OA\Property(property="helpline", type="string", example="string"),
     *     @OA\Property(property="email", type="string", example="string"),
     *     @OA\Property(property="worldId", type="string", example="string")
     *    ),
     * ),
     * @OA\Response(
     *    response=204,
     *    description="Returns when PUT is successful."
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The parent id field must be present. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="parentId",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The parent id field must be present.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="editorUserIds",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The editor user ids field must be present.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/ServiceErrorUnauthenticated")
     * )
     * )
     */

    public function update(Request $request, ServiceBody $serviceBody)
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

        $this->handleUpdate($request, $serviceBody, $validated);

        return response()->noContent();
    }

    /**
     * @OA\Patch(
     * path="/api/v1/servicebodies/{serviceBodyId}",
     * summary="Patches a single service body",
     * description="Patches a single service body by id.",
     * operationId="patchServiceBody",
     * tags={"servicebodies"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of service body",
     *    in="path",
     *    name="serviceBodyId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in service body attributes.",
     *    @OA\JsonContent(
     *     @OA\Property(
     *        property="editorUserIds",
     *        type="array",
     *        @OA\Items(
     *           type="integer",
     *           example="0",
     *        )
     *     ),
     *    ),
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ServiceErrorUnauthenticated"),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ServiceErrorUnauthorized"),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *  )
     * )
     */
    public function partialUpdate(Request $request, ServiceBody $serviceBody)
    {
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

        $this->handleUpdate($request, $serviceBody, $validated);

        return response()->noContent();
    }

    private function handleUpdate(Request $request, ServiceBody $serviceBody, array $validated)
    {
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
    }

    /**
     * @OA\Delete(
     * path="/api/v1/servicebodies/{serviceBodyId}",
     * summary="Deletes a single service body",
     * description="Deletes a single service body by id.",
     * operationId="deleteServiceBody",
     * tags={"servicebodies"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of service body",
     *    in="path",
     *    name="serviceBodyId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ServiceErrorUnauthenticated"),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ServiceErrorUnauthorized"),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\ServiceBody]"),
     *     )
     *  ),
     *  @OA\Response(
     *     response=409,
     *     description="Returns when service body has dependant resources.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="You cannot delete a service body while other service bodies or meetings are assigned to it."),
     *     )
     *  )
     * )
     */
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
}
