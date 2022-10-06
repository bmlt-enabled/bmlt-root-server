<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="ServiceBodyBase",
 *     @OA\Property(property="parentId", type="integer", example="0"),
 *     @OA\Property(property="name", type="string", example="string"),
 *     @OA\Property(property="description", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="adminUserId", type="integer", example="0"),
 *     @OA\Property(property="assignedUserIds", type="array",
 *        @OA\Items(type="integer", example="0",)
 *     ),
 *     @OA\Property(property="url", type="string", example="string"),
 *     @OA\Property(property="helpline", type="string", example="string"),
 *     @OA\Property(property="email", type="string", example="string"),
 *     @OA\Property(property="worldId", type="string", example="string")
 * ),
 * @OA\Schema(schema="ServiceBody",
 *     allOf={ @OA\Schema(ref="#/components/schemas/ServiceBodyBase") },
 *     @OA\Property(property="id", type="integer", example="0"),
 * ),
 * @OA\Schema(schema="CreateServiceBody", required={"parentId","name","description","type","adminUserId","assignedUserIds"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/ServiceBodyBase") },
 * ),
 * @OA\Schema(schema="UpdateServiceBody", required={"parentId","name","description","type","adminUserId","assignedUserIds"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/ServiceBodyBase") },
 * ),
 * @OA\Schema(schema="PartialUpdateServiceBody",
 *     allOf={ @OA\Schema(ref="#/components/schemas/ServiceBodyBase") }
 * ),
 * @OA\Schema(schema="ServiceBodyCollection", type="array",
 *     @OA\Items(ref="#/components/schemas/ServiceBody")
 * ),
 * @OA\Schema(schema="NoServiceBodyExists", description="Returns when no user exists.",
 *     @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\ServiceBody]"),
 * ),
 */
class ServiceBodyController extends Controller
{

    /**
     * @OA\Get(path="/api/v1/servicebodies", summary="Retrieves service bodies", description="Retrieve service bodies for authenticated user.", operationId="getServiceBodies", tags={"servicebodies"}, security={{"oauth2":{}}},
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceBodyCollection")
     *     ),
     *     @OA\Response(response=401, description="Returns when not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(path="/api/v1/servicebodies/{serviceBodyId}", summary="Retrieves a service body", description="Retrieve a single service body by id.", operationId="getServiceBody", tags={"servicebodies"}, security={{"oauth2":{}}},
     *     @OA\Parameter(description="ID of service body", in="path", name="serviceBodyId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceBody")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     *     @OA\Response(response=404, description="Returns when no service body exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *     ),
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Post(path="/api/v1/servicebodies", summary="Creates a service body", description="Creates a service body.", operationId="createServiceBody", tags={"servicebodies"}, security={{"oauth2":{}}},
     *     @OA\RequestBody(required=true, description="Pass in service body object",
     *         @OA\JsonContent(ref="#/components/schemas/CreateServiceBody"),
     *     ),
     *     @OA\Response(response=201, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceBody")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     *     ),
     *     @OA\Response(response=404, description="Returns when no service body exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function store()
    {
    }

    /**
     * @OA\Put(path="/api/v1/servicebodies/{serviceBodyId}", summary="Updates a Service Body", description="Updates a single service body.", operationId="updateServiceBody", tags={"servicebodies"}, security={{"oauth2":{}}},
     *     @OA\Parameter(description="ID of service body", in="path", name="serviceBodyId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in service body object",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateServiceBody"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     *     ),
     *     @OA\Response(response=404, description="Returns when no service body exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */

    public function update()
    {
    }

    /**
     * @OA\Patch(path="/api/v1/servicebodies/{serviceBodyId}", summary="Patches a service body", description="Patches a single service body by id.", operationId="patchServiceBody", tags={"servicebodies"}, security={{"oauth2":{}}},
     *     @OA\Parameter(description="ID of service body", in="path", name="serviceBodyId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in fields you want to update.",
     *         @OA\JsonContent(ref="#/components/schemas/PartialUpdateServiceBody"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     *     ),
     *     @OA\Response(response=404, description="Returns when no service body exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function partialUpdate()
    {
    }
    /**
     * @OA\Delete(path="/api/v1/servicebodies/{serviceBodyId}", summary="Deletes a service body", description="Deletes a service body by id.", operationId="deleteServiceBody", tags={"servicebodies"}, security={{"oauth2":{}}},
     *     @OA\Parameter(description="ID of service body", in="path", name="serviceBodyId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     *     ),
     *     @OA\Response(response=404, description="Returns when no service body exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NoServiceBodyExists")
     *     ),
     * )
     */
    public function destroy()
    {
    }
}
