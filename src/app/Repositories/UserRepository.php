<?php

namespace App\Repositories;

use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Change;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function getById(int $id)
    {
        return User::query()->where('id_bigint', $id)->first();
    }

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

    public function updatePassword(int $id, string $plaintextPassword)
    {
        $this->update($id, ['password_string' => Hash::make($plaintextPassword)]);
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
                // We have to explicitly remove the user from the service body because of the way editors
                // are stored. Unfortunately, they are stored as a comma delimited list of strings, making
                // it impossible to create a foreign key. Once the legacy code is deleted and we are able
                // to make schema changes, we'll fix this so that deletes cascade via a foreign key, making
                // this call to ->removeUser unnecessary.
                $this->serviceBodyRepository->removeUser($id);
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
        $beforeObject = !is_null($beforeUser) ? $this->serializeForChange($beforeUser) : null;
        $afterObject = !is_null($afterUser) ? $this->serializeForChange($afterUser) : null;
        if (!is_null($beforeObject) && !is_null($afterObject) && $beforeObject == $afterObject) {
            // nothing actually changed, don't save a record
            return;
        }

        Change::create([
            'user_id_bigint' => request()->user()?->id_bigint ?? $beforeUser?->id_bigint ?? $afterUser?->id_bigint,
            'service_body_id_bigint' => $afterUser?->id_bigint ?? $beforeUser->id_bigint,
            'lang_enum' => $beforeUser?->lang_enum ?: $afterUser?->lang_enum ?: legacy_config('language') ?: App::currentLocale(),
            'object_class_string' => 'c_comdef_user',
            'before_id_bigint' => $beforeUser?->id_bigint,
            'before_lang_enum' => !is_null($beforeUser) ? $beforeUser?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'after_id_bigint' => $afterUser?->id_bigint,
            'after_lang_enum' => !is_null($afterUser) ? $afterUser?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'change_type_enum' => is_null($beforeUser) ? 'comdef_change_type_new' : (is_null($afterUser) ? 'comdef_change_type_delete' : 'comdef_change_type_change'),
            'before_object' => $beforeObject,
            'after_object' => $afterObject,
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
