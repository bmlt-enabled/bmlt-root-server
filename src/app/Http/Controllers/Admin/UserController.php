<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
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


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('comdef_users', 'login_string')->ignore($user->id_bigint, 'id_bigint')],
            'password' => ['required', Password::min(12)],
            'type' => ['required', Rule::in(array_values(User::USER_LEVEL_TO_USER_TYPE_MAP))],
            'displayName' => 'required|string|max:255',
            'description' => 'nullable|present|string|max:1024',
            'email' => 'nullable|present|email',
            'ownerId' => 'nullable|present|int|exists:comdef_users,id_bigint',
        ]);

        $this->handleUpdate($request, $user, $validated);

        return response()->noContent();
    }


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


    public function destroy(User $user)
    {
        $this->userRepository->delete($user->id_bigint);
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
}
