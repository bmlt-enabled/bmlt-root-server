<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(
 *     schema="ServiceBodyResponse",
 *     @OA\Property(property="id", type="integer", example="0"),
 *     @OA\Property(property="parentId", type="integer", example="0"),
 *     @OA\Property(property="name", type="string", example="string"),
 *     @OA\Property(property="description", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="adminUserId", type="integer", example="0"),
 *     @OA\Property(
 *        property="assignedUserIds",
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
 *                  "adminUserId": 0,
 *                  "assignedUserIds": {1},
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
class ServiceBodyControllerDoc extends ResourceController
{

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

    public function index()
    {
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
    public function show()
    {
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
     *     @OA\Property(property="adminUserId", type="integer", example="0"),
     *     @OA\Property(
     *        property="assignedUserIds",
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
     *              property="assignedUserIds",
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
    public function store()
    {
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
     *     @OA\Property(property="adminUserId", type="integer", example="0"),
     *     @OA\Property(
     *        property="assignedUserIds",
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
     *              property="assignedUserIds",
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

    public function update()
    {
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
     *        property="assignedUserIds",
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
    public function partialUpdate()
    {
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
    public function destroy()
    {
    }
}
