<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
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
            'owner_id_bigint' => !is_null($validated['ownerId']) ? $validated['ownerId'] : -1,
        ]);

        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        //
    }

    public function destroy(User $user)
    {
        $this->userRepository->delete($user->id_bigint);
        return response()->noContent();
    }
}
