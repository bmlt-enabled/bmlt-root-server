<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(
 *     schema="UserResponse",
 *     @OA\Property(property="id", type="integer", example="0"),
 *     @OA\Property(property="username", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="displayName", type="string", example="string"),
 *     @OA\Property(property="description", type="string", example="string"),
 *     @OA\Property(property="email", type="string", example="string"),
 *     @OA\Property(property="ownerId", type="string", example="0")
 * ),
 * @OA\Schema(
 *     schema="UsersResponse",
 *             type="array",
 *                example={{
 *                  "id": 1,
 *                  "username": "string",
 *                  "type": "string",
 *                  "displayName": "string",
 *                  "description": "string",
 *                  "email": "string",
 *                  "ownerId": 0
 *                }},
 *                @OA\Items(ref="#/components/schemas/UserResponse"),
 * ),
 * @OA\Schema(
 *     schema="UserErrorUnauthenticated",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(
 *     schema="UserErrorUnauthorized",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * ),
 * @OA\Schema(
 *     schema="NoUserExists",
 *      description="Returns when no user exists.",
 *      @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\User]"),
 * )
 */
class UserControllerDoc extends ResourceController
{

    /**
     * @OA\Get(
     * path="/api/v1/users",
     * summary="Retrieve users",
     * description="Retrieve users for authenticated user.",
     * operationId="getUsers",
     * tags={"users"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UsersResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated",
     *      @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     *   )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(
     * path="/api/v1/users/{userId}",
     * summary="Retrieve a single user",
     * description="Retrieve single user.",
     * operationId="getSingleUser",
     * tags={"users"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of user",
     *    in="path",
     *    name="userId",
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
     *     @OA\JsonContent(ref="#/components/schemas/UserResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Returns when no user exists.",
     *      @OA\JsonContent(ref="#/components/schemas/NoUserExists")
     *   )
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Post(
     * path="/api/v1/users",
     * summary="Create User",
     * description="Cretaes a user.",
     * operationId="createUser",
     * tags={"users"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in user object",
     *    @OA\JsonContent(
     *       required={"username","password"},
     *     @OA\Property(property="username", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(property="password", type="string", example="string"),
     *     @OA\Property(property="displayName", type="string", example="string"),
     *     @OA\Property(property="description", type="string", example="string"),
     *     @OA\Property(property="email", type="string", example="string"),
     *     @OA\Property(property="ownerId", type="string", example="0")
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Returns when POST is successful.",
     *    @OA\JsonContent(ref="#/components/schemas/UserResponse")
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The username field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="username",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The username field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="password",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The password field is required.",
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
     *    @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     * )
     * )
     */
    public function store()
    {
    }

    /**
     * @OA\Put(
     * path="/api/v1/users/{userId}",
     * summary="Update single user",
     * description="Updates a single user.",
     * operationId="updateUser",
     * tags={"users"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of user",
     *    in="path",
     *    name="userId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in user object",
     *    @OA\JsonContent(
     *       required={"username","password"},
     *     @OA\Property(property="username", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(property="password", type="string", example="string"),
     *     @OA\Property(property="displayName", type="string", example="string"),
     *     @OA\Property(property="description", type="string", example="string"),
     *     @OA\Property(property="email", type="string", example="string"),
     *     @OA\Property(property="ownerId", type="string", example="0")
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
     *        @OA\Property(property="message", type="string", example="The username field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="username",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The username field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="password",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The password field is required.",
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
     *    @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     * )
     * )
     */
    public function update()
    {
    }

    /**
     * @OA\Patch(
     * path="/api/v1/users/{userId}",
     * summary="Patches a single user",
     * description="Patches a single user by id",
     * operationId="patchUser",
     * tags={"users"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of user",
     *    in="path",
     *    name="userId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in user attributes",
     *    @OA\JsonContent(
     *     @OA\Property(property="email", type="string", example="string"),
     *    ),
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated",
     *    @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized",
     *    @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthorized")
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoUserExists")
     *  )
     * )
     */
    public function partialUpdate()
    {
    }

    /**
     * @OA\Delete(
     * path="/api/v1/users/{userId}",
     * summary="Deletes a single user",
     * description="Deletes a single user by id",
     * operationId="deleteUSer",
     * tags={"users"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of user",
     *    in="path",
     *    name="userId",
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
     *    description="Returns when not authenticated",
     *    @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized",
     *    @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthorized")
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoUserExists")
     *  )
     * )
     */
    public function destroy()
    {
    }
}
