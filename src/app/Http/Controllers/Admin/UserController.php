<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;

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
        //
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
