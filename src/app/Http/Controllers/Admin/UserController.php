<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
class UserController extends ResourceController
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->authorizeResource(User::class);
    }

    /**
     * @OA\Get(
     * path="/api/v1/users",
     * summary="Retrieve users",
     * description="Retrieve users for authenticated user",
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
    public function index(Request $request)
    {
        $user = $request->user();

        $userIds = null;
        $ownerIds = null;
        if (!$user->isAdmin()) {
            $userIds = [$user->id_bigint];
            if ($user->isServiceBodyAdmin()) {
                $ownerIds = [$user->id_bigint];
            }
        }
        $users = $this->userRepository->search(includeIds: $userIds, includeOwnerIds: $ownerIds);

        return UserResource::collection($users);
    }

    /**
     * @OA\Get(
     * path="/api/v1/users/{userId}",
     * summary="Retrieve a single user",
     * description="Retrieve single user",
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
     *      description="Returns when not authenticated",
     *      @OA\JsonContent(ref="#/components/schemas/UserErrorUnauthenticated")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Returns when no user exists.",
     *      @OA\JsonContent(ref="#/components/schemas/UsersResponse")
     *   )
     * )
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * @OA\Post(
     * path="/api/v1/users",
     * summary="Create User",
     * description="Cretaes a user",
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
     *    description="Returns when POST is successful",
     *    @OA\JsonContent(ref="#/components/schemas/UserResponse")
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error",
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
     *    description="Returns when user is not authenticated",
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:comdef_users,login_string',
            'password' => ['required', Password::min(12)],
            'type' => ['required', Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'required|string|max:255',
            'description' => 'string|max:1024',
            'email' => 'email',
            'ownerId' => 'nullable|present|int|exists:comdef_users,id_bigint',
        ]);

        $user = $this->userRepository->create([
            'login_string' => $validated['username'],
            'password_string' => Hash::make($validated['password']),
            'user_level_tinyint' => User::USER_TYPE_TO_USER_LEVEL_MAP[$validated['type']],
            'name_string' => $validated['displayName'],
            'description_string' => $validated['description'] ?? '',
            'email_address_string' => $validated['email'] ?? '',
            'owner_id_bigint' => $validated['ownerId'] ?? -1,
        ]);

        return new UserResource($user);
    }

    /**
     * @OA\Put(
     * path="/api/v1/users/{userId}",
     * summary="Update single user",
     * description="Updates a single user",
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
     *    description="Returns when PUT is successful"
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error",
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
     *    description="Returns when user is not authenticated",
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
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('comdef_users', 'login_string')->ignore($user->id_bigint, 'id_bigint')],
            'password' => ['required', Password::min(12)],
            'type' => ['required', Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'required|string|max:255',
            'description' => 'string|max:1024',
            'email' => 'email',
            'ownerId' => 'nullable|present|int|exists:comdef_users,id_bigint',
        ]);

        $this->handleUpdate($request, $user, $validated);

        return response()->noContent();
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
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated."),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\User]"),
     *     )
     *  )
     * )
     */
    public function partialUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['string', 'max:255', Rule::unique('comdef_users', 'login_string')->ignore($user->id_bigint, 'id_bigint')],
            'password' => [Password::min(12)],
            'type' => [Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'string|max:255',
            'description' => 'string|max:1024',
            'email' => 'email',
            'ownerId' => 'nullable|int|exists:comdef_users,id_bigint',
        ]);

        $this->handleUpdate($request, $user, $validated);

        return response()->noContent();
    }

    private function handleUpdate(Request $request, User $user, array $validated)
    {
        $requestUser = $request->user();
        $isAdmin = $requestUser->isAdmin();
        $isOwner = $requestUser->isServiceBodyAdmin() && $requestUser->id_bigint == $user->owner_id_bigint;
        $values = collect($validated)->mapWithKeys(function ($value, $key) use ($isAdmin, $isOwner) {
            if ($isAdmin) {
                if ($key == 'type') {
                    return ['user_level_tinyint' => User::USER_TYPE_TO_USER_LEVEL_MAP[$value]];
                } elseif ($key == 'ownerId') {
                    return ['owner_id_bigint' => $value ?? -1];
                }
            }
            if ($isAdmin || $isOwner) {
                if ($key == 'username') {
                    return ['login_string' => $value];
                }
            }
            if ($key == 'password') {
                return ['password_string' => Hash::make($value)];
            } elseif ($key == 'displayName') {
                return ['name_string' => $value];
            } elseif ($key == 'description') {
                return ['description_string' => $value ?? ''];
            } elseif ($key == 'email') {
                return ['email_address_string' => $value ?? ''];
            } else {
                return [null => null];
            }
        })->reject(fn ($_, $key) => empty($key))->toArray();

        if (!empty($values)) {
            $this->userRepository->update($user->id_bigint, $values);
        }
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
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated."),
     *    )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no user exists.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\User]"),
     *     )
     *  )
     * )
     */

    public function destroy(User $user)
    {
        $this->userRepository->delete($user->id_bigint);
        return response()->noContent();
    }
}
