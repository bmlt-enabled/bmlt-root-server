<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\Change;
use App\Models\User;
use Illuminate\Support\Facades\App;
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
            $this->saveChange(null, $user);
            return $user;
        });
    }

    public function update(int $id, array $values): bool
    {
        return DB::transaction(function () use ($id, $values) {
            $user = User::find($id);
            if (!is_null($user)) {
                $user::query()->where('id_bigint', $id)->update($values);
                $this->saveChange($user, User::find($id));
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
                $this->saveChange($user, null);
                return true;
            }
            return false;
        });
    }

    private function saveChange(?User $beforeUser, ?User $afterUser): void
    {
        Change::create([
            'user_id_bigint' => request()->user()->id_bigint,
            'service_body_id_bigint' => $afterUser?->id_bigint ?? $beforeUser->id_bigint,
            'lang_enum' => $beforeUser?->lang_enum ?: $afterUser?->lang_enum ?: legacy_config('language') ?: App::currentLocale(),
            'object_class_string' => 'c_comdef_user',
            'before_id_bigint' => $beforeUser?->id_bigint,
            'before_lang_enum' => !is_null($beforeUser) ? $beforeUser?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'after_id_bigint' => $afterUser?->id_bigint,
            'after_lang_enum' => !is_null($afterUser) ? $afterUser?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'change_type_enum' => is_null($beforeUser) ? 'comdef_change_type_new' : (is_null($afterUser) ? 'comdef_change_type_delete' : 'comdef_change_type_change'),
            'before_object' => !is_null($beforeUser) ? $this->serializeForChange($beforeUser) : null,
            'after_object' => !is_null($afterUser) ? $this->serializeForChange($afterUser) : null,
        ]);
    }

    private function serializeForChange(User $user): string
    {
        return serialize([
            $user->id_bigint,
            $user->user_level_tinyint,
            $user->email_address_string,
            $user->login_string,
            $user->password_string,
            $user->last_access_datetime,
            $user->name_string,
            $user->description_string,
            $user->owner_id_bigint,
            $user->lang_enum,
        ]);
    }
}
