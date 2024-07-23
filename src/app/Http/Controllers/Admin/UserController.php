<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserResource;
use App\Http\Responses\JsonResponse;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends ResourceController
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->authorizeResource(User::class);
    }


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


    public function show(User $user)
    {
        return new UserResource($user);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:comdef_users,login_string',
            'password' => ['required', Password::min(12)],
            'type' => ['required', Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'email' => 'nullable|email',
            'ownerId' => 'nullable|present|int|exists:comdef_users,id_bigint',
        ]);

        $ownerId = $validated['ownerId'];
        if ($ownerId && $this->userRepository->getById($ownerId)?->isAdmin()) {
            $validated['ownerId'] = -1;
        }

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

    public function update(Request $request, User $user)
    {
        $validated = $this->validateForUpdate($request, $user);
        $values = $this->buildValuesArrayForUpdate($request, $user, $validated);
        $this->userRepository->update($user->id_bigint, $values);
        return response()->noContent();
    }

    public function partialUpdate(Request $request, User $user)
    {
        $request->merge(
            collect(User::FIELDS)
                ->mapWithKeys(function ($fieldName, $_) use ($request, $user) {
                    if ($fieldName == 'user_level_tinyint') {
                        return ['type' => $request->has('type') ? $request->input('type') : User::USER_LEVEL_TO_USER_TYPE_MAP[$user->user_level_tinyint]];
                    } elseif ($fieldName == 'login_string') {
                        return ['username' => $request->has('username') ? $request->input('username') : $user->login_string];
                    } elseif ($fieldName == 'name_string') {
                        return ['displayName' => $request->has('displayName') ? $request->input('displayName') : $user->name_string];
                    } elseif ($fieldName == 'description_string') {
                        return ['description' => $request->has('description') ? $request->input('description') : $user->description_string];
                    } elseif ($fieldName == 'email_address_string') {
                        return ['email' => $request->has('email') ? $request->input('email') : $user->email_address_string];
                    } elseif ($fieldName == 'password_string') {
                        return $request->has('password') ? ['password' => $request->input('password')] : [null => null];
                    } elseif ($fieldName == 'owner_id_bigint') {
                        return ['ownerId' => $request->has('ownerId') ? $request->input('ownerId') : ($user->owner_id_bigint === -1 ? null : $user->owner_id_bigint)];
                    } else {
                        return [null => null];
                    }
                })
                ->reject(fn ($_, $key) => empty($key))
                ->toArray()
        );
        $validated = $this->validateForUpdate($request, $user);
        $values = $this->buildValuesArrayForUpdate($request, $user, $validated);
        $this->userRepository->update($user->id_bigint, $values);
        return response()->noContent();
    }

    public function destroy(User $user)
    {
        if ($user->children()->exists() || $user->serviceBodies()->exists()) {
            return new JsonResponse([
                'message' => 'You cannot delete a user while other users or service bodies are assigned to it.'
            ], 409);
        }
        $this->userRepository->delete($user->id_bigint);
        return response()->noContent();
    }

    private function validateForUpdate(Request $request, User $user): Collection
    {
        $validated = collect($request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('comdef_users', 'login_string')->ignore($user->id_bigint, 'id_bigint')],
            'password' => [Password::min(12)],
            'type' => ['required', Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'required|string|max:255',
            'description' => 'nullable|string|max:1024',
            'email' => 'nullable|email',
            'ownerId' => 'nullable|present|int|exists:comdef_users,id_bigint',
        ]));

        $ownerId = $validated->get('ownerId');
        if ($ownerId && $this->userRepository->getById($ownerId)?->isAdmin()) {
            $validated->put('ownerId', -1);
        }

        return $validated;
    }

    private function buildValuesArrayForUpdate(Request $request, User $user, Collection $validated): array
    {
        $requestUser = $request->user();
        $isAdmin = $requestUser->isAdmin();
        $isOwner = $requestUser->isServiceBodyAdmin() && $requestUser->id_bigint == $user->owner_id_bigint;
        return collect(User::FIELDS)
            ->mapWithKeys(function ($fieldName, $_) use ($validated, $isAdmin, $isOwner) {
                if ($isAdmin) {
                    if ($fieldName == 'user_level_tinyint') {
                        return [$fieldName => User::USER_TYPE_TO_USER_LEVEL_MAP[$validated['type']]];
                    } elseif ($fieldName == 'owner_id_bigint') {
                        return [$fieldName => $validated['ownerId'] ?? -1];
                    }
                }
                if ($isAdmin || $isOwner) {
                    if ($fieldName == 'login_string') {
                        return [$fieldName => $validated['username']];
                    }
                }
                if ($fieldName == 'password_string' && $validated->has('password')) {
                    return [$fieldName => Hash::make($validated['password'])];
                } elseif ($fieldName == 'name_string') {
                    return [$fieldName => $validated['displayName']];
                } elseif ($fieldName == 'description_string') {
                    return [$fieldName => $validated['description'] ?? ''];
                } elseif ($fieldName == 'email_address_string') {
                    return [$fieldName => $validated['email'] ?? ''];
                } else {
                    return [null => null];
                }
            })
            ->reject(fn ($_, $key) => empty($key))
            ->toArray();
    }
}
