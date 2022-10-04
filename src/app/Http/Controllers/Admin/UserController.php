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
    public function show(User $user)
    {
        return new UserResource($user);
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
    public function update(Request $request, User $user)
    {
        $values = $this->validateInputsAndCreateValuesArray($request, $user);
        $this->userRepository->update($user->id_bigint, $values);
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
    public function partialUpdate(Request $request, User $user)
    {
        $fieldNames = ['username', 'password', 'type', 'displayName', 'description', 'email', 'ownerId'];
        $inputs = collect($fieldNames)
            ->mapWithKeys(function ($fieldName, $_) use ($request, $user) {
                if ($fieldName == 'username') {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $user->login_string];
                } elseif ($fieldName == 'password') {
                    return $request->has($fieldName) ? ['password' => $request->input($fieldName)] : [null => null];
                } elseif ($fieldName == 'type') {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : User::USER_LEVEL_TO_USER_TYPE_MAP[$user->user_level_tinyint]];
                } elseif ($fieldName == 'displayName') {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $user->name_string];
                } elseif ($fieldName == 'description') {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $user->description_string];
                } elseif ($fieldName == 'email') {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $user->email_address_string];
                } elseif ($fieldName == 'ownerId') {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : ($user->owner_id_bigint == -1 ? null : $user->owner_id_bigint)];
                } else {
                    return [null => null];
                }
            })
            ->reject(fn ($_, $key) => empty($key))
            ->toArray();
        $request->merge($inputs);
        $values = $this->validateInputsAndCreateValuesArray($request, $user, isPartialUpdate: true);
        $this->userRepository->update($user->id_bigint, $values);
        return response()->noContent();
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
    public function destroy(User $user)
    {
        $this->userRepository->delete($user->id_bigint);
        return response()->noContent();
    }

    private function validateInputsAndCreateValuesArray(Request $request, User $user, bool $isPartialUpdate = false): array
    {
        $validators = [
            'username' => ['required', 'string', 'max:255', Rule::unique('comdef_users', 'login_string')->ignore($user->id_bigint, 'id_bigint')],
            'password' => array_merge($isPartialUpdate ? [] : ['required'], [Password::min(12)]),
            'type' => ['required', Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'required|string|max:255',
            'description' => 'string|max:1024',
            'email' => 'email',
            'ownerId' => 'nullable|present|int|exists:comdef_users,id_bigint',
        ];

        $validated = collect($request->validate($validators));

        $requestUser = $request->user();
        $isAdmin = $requestUser->isAdmin();
        $isOwner = $requestUser->isServiceBodyAdmin() && $requestUser->id_bigint == $user->owner_id_bigint;
        return collect($validators)
            ->mapWithKeys(function ($_, $fieldName) use ($validated, $isAdmin, $isOwner) {
                if ($isAdmin) {
                    if ($fieldName == 'type') {
                        return ['user_level_tinyint' => User::USER_TYPE_TO_USER_LEVEL_MAP[$validated[$fieldName]]];
                    } elseif ($fieldName == 'ownerId') {
                        return ['owner_id_bigint' => $validated[$fieldName] ?? -1];
                    }
                }
                if ($isAdmin || $isOwner) {
                    if ($fieldName == 'username') {
                        return ['login_string' => $validated[$fieldName]];
                    }
                }
                if ($fieldName == 'password') {
                    return $validated->has($fieldName) ? ['password_string' => Hash::make($validated[$fieldName])] : [null => null];
                } elseif ($fieldName == 'displayName') {
                    return ['name_string' => $validated[$fieldName]];
                } elseif ($fieldName == 'description') {
                    return ['description_string' => $validated[$fieldName] ?? ''];
                } elseif ($fieldName == 'email') {
                    return ['email_address_string' => $validated[$fieldName] ?? ''];
                } else {
                    return [null => null];
                }
            })
            ->reject(fn ($_, $key) => empty($key))
            ->toArray();
    }
}
