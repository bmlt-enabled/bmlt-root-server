<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getByUsername(string $username)
    {
        return User::query()->where('login_string', $username)->first();
    }

    public function search(array $includeIds = null, array $includeOwnerIds = null)
    {
        $users = User::query();

        if (!is_null($includeIds)) {
            $users = $users->orWhereIn('id_bigint', $includeIds);
        }

        if (!is_null($includeOwnerIds)) {
            $users = $users->orWhereIn('owner_id_bigint', $includeOwnerIds);
        }

        return $users->get();
    }

    public function create(array $values): User
    {
        return DB::transaction(function () use ($values) {
            $user = User::create($values);
            //$this->saveChange(null, $user);
            return $user;
        });
    }

    public function update(int $id, array $values): bool
    {
        return DB::transaction(function () use ($id, $values) {
            $user = User::find($id);
            if (!is_null($user)) {
                $user::query()->where('id_bigint', $id)->update($values);
                // $this->saveChange($user, User::find($id));
                return true;
            }
            return false;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = User::find($id);
            if (!is_null($user)) {
                $user->children()->update(['owner_id_bigint' => -1]);
                $user->delete();
                // $this->saveChange($user, null);
                return true;
            }
            return false;
        });
    }
}
