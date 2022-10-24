<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="UserBase",
 *     @OA\Property(property="username", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="displayName", type="string", example="string"),
 *     @OA\Property(property="description", type="string", example="string"),
 *     @OA\Property(property="email", type="string", example="string"),
 *     @OA\Property(property="ownerId", type="string", example="0")
 * ),
 * @OA\Schema(schema="User", required={"id", "username", "type", "displayName", "description", "email", "ownerId"},
 *     @OA\Property(property="id", type="integer", example="0"),
 *     allOf={ @OA\Schema(ref="#/components/schemas/UserBase") }
 * ),
 * @OA\Schema(schema="UserCreate", required={"username", "password", "type", "displayName"},
 *     @OA\Property(property="password", type="string", example="string"),
 *     allOf={ @OA\Schema(ref="#/components/schemas/UserBase") }
 * ),
 * @OA\Schema(schema="UserUpdate", required={"username", "type", "displayName"},
 *     @OA\Property(property="password", type="string", example="string"),
 *     allOf={ @OA\Schema(ref="#/components/schemas/UserBase") }
 * ),
 * @OA\Schema(schema="UserPartialUpdate",
 *     @OA\Property(property="password", type="string", example="string"),
 *     allOf={ @OA\Schema(ref="#/components/schemas/UserBase") }
 * ),
 * @OA\Schema(schema="UserCollection", type="array",
 *     @OA\Items(ref="#/components/schemas/User")
 * ),
 */
class UserController extends Controller
{

    /**
     * @OA\Get(path="/api/v1/users", summary="Retrieves users", description="Retrieve users for authenticated user.", operationId="getUsers", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/UserCollection")
     *     ),
     *     @OA\Response(response=401, description="Returns when not authenticated",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(path="/api/v1/users/{userId}", summary="Retrieves a single user", description="Retrieve single user.", operationId="getUser", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of user", in="path", name="userId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Returns when not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no user exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Post(path="/api/v1/users", summary="Creates a user", description="Creates a user.", operationId="createUser", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\RequestBody(required=true, description="Pass in user object",
     *         @OA\JsonContent(ref="#/components/schemas/UserCreate"),
     *     ),
     *     @OA\Response(response=201, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no user exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
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
     * @OA\Put(path="/api/v1/users/{userId}", summary="Update single user", description="Updates a user.", operationId="updateUser", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of user", in="path", name="userId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in user object",
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdate"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no user exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
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
     * @OA\Patch(path="/api/v1/users/{userId}", summary="Patches a user", description="Patches a user by id.", operationId="partialUpdateUser", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of user", in="path", name="userId", required=true, example="1",
     *        @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in fields you want to update.",
     *         @OA\JsonContent(ref="#/components/schemas/UserPartialUpdate"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401,description="Returns when not authenticated",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no user exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
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
     * @OA\Delete(path="/api/v1/users/{userId}", summary="Deletes a user", description="Deletes a user by id", operationId="deleteUser", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of user", in="path", name="userId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401,description="Returns when not authenticated",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no user exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function destroy()
    {
    }
}
