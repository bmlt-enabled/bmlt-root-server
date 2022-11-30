<?php

namespace App\Repositories;

use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Change;
use App\Models\RootServer;
use App\Models\ServiceBody;
use App\Repositories\External\ExternalServiceBody;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ServiceBodyRepository implements ServiceBodyRepositoryInterface
{
    public function search(
        array $includeIds = [],
        array $excludeIds = [],
        array $rootServersInclude = null,
        array $rootServersExclude = null,
        bool $recurseChildren = false,
        bool $recurseParents = false
    ): Collection {
        if ($recurseChildren) {
            $includeIds = array_merge($includeIds, $this->getChildren($includeIds));
            $excludeIds = array_merge($excludeIds, $this->getChildren($excludeIds));
        }

        if ($recurseParents) {
            $includeIds = array_merge($includeIds, $this->getParents($includeIds));
            $excludeIds = array_merge($excludeIds, $this->getParents($excludeIds));
        }

        $serviceBodies = ServiceBody::query();
        if (!empty($includeIds)) {
            $serviceBodies = $serviceBodies->whereIn('id_bigint', $includeIds);
        }

        if (!empty($excludeIds)) {
            $serviceBodies = $serviceBodies->whereNotIn('id_bigint', $excludeIds);
        }

        if (!is_null($rootServersInclude)) {
            $serviceBodies = $serviceBodies->whereIn('root_server_id', $rootServersInclude);
        }

        if (!is_null($rootServersExclude)) {
            $serviceBodies = $serviceBodies->whereNotIn('root_server_id', $rootServersExclude);
        }

        return $serviceBodies->get();
    }

    public function create(array $values): ServiceBody
    {
        return DB::transaction(function () use ($values) {
            $serviceBody = ServiceBody::create($values);
            if (!legacy_config('aggregator_mode_enabled')) {
                $this->saveChange(null, $serviceBody);
            }
            return $serviceBody;
        });
    }

    public function update(int $id, array $values): bool
    {
        return DB::transaction(function () use ($id, $values) {
            $serviceBody = ServiceBody::find($id);
            if (!is_null($serviceBody)) {
                ServiceBody::query()->where('id_bigint', $id)->update($values);
                if (!legacy_config('aggregator_mode_enabled')) {
                    $this->saveChange($serviceBody, ServiceBody::find($id));
                }
                return true;
            }
            return false;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $serviceBody = ServiceBody::find($id);
            if (!is_null($serviceBody)) {
                $serviceBody->delete();
                if (!legacy_config('aggregator_mode_enabled')) {
                    $this->saveChange($serviceBody, null);
                }
                return true;
            }
            return false;
        });
    }

    private function saveChange(?ServiceBody $beforeServiceBody, ?ServiceBody $afterServiceBody): void
    {
        $beforeObject = !is_null($beforeServiceBody) ? $this->serializeForChange($beforeServiceBody) : null;
        $afterObject = !is_null($afterServiceBody) ? $this->serializeForChange($afterServiceBody) : null;
        if (!is_null($beforeObject) && !is_null($afterObject) && $beforeObject == $afterObject) {
            // nothing actually changed, don't save a record
            return;
        }

        Change::create([
            'user_id_bigint' => request()->user()->id_bigint,
            'service_body_id_bigint' => $afterServiceBody?->id_bigint ?? $beforeServiceBody->id_bigint,
            'lang_enum' => $beforeServiceBody?->lang_enum ?: $afterServiceBody?->lang_enum ?: legacy_config('language') ?: App::currentLocale(),
            'object_class_string' => 'c_comdef_service_body',
            'before_id_bigint' => $beforeServiceBody?->id_bigint,
            'before_lang_enum' => !is_null($beforeServiceBody) ? $beforeServiceBody?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'after_id_bigint' => $afterServiceBody?->id_bigint,
            'after_lang_enum' => !is_null($afterServiceBody) ? $afterServiceBody?->lang_enum ?: legacy_config('language') ?: App::currentLocale() : null,
            'change_type_enum' => is_null($beforeServiceBody) ? 'comdef_change_type_new' : (is_null($afterServiceBody) ? 'comdef_change_type_delete' : 'comdef_change_type_change'),
            'before_object' => $beforeObject,
            'after_object' => $afterObject,
        ]);
    }

    private function serializeForChange(ServiceBody $serviceBody): string
    {
        return serialize([
            $serviceBody->id_bigint,
            $serviceBody->principal_user_bigint ?? '',
            $serviceBody->editors_string ?? '',
            $serviceBody->kml_file_uri_string ?? '',
            $serviceBody->uri_string ?? '',
            $serviceBody->worldid_mixed ?? '',
            $serviceBody->name_string,
            $serviceBody->description_string,
            $serviceBody->lang_enum ?? legacy_config('language') ?? App::currentLocale(),
            $serviceBody->sb_type ?? '',
            $serviceBody->sb_owner ?? '',
            $serviceBody->sb_owner_2 ?? '',
            $serviceBody->sb_meeting_email,
        ]);
    }

    public function getAdminServiceBodyIds(int $userId): Collection
    {
        $serviceBodyIds = ServiceBody::query()
            ->where('principal_user_bigint', $userId)
            ->get()
            ->map(fn ($sb) => $sb->id_bigint)
            ->toArray();

        foreach ($this->getChildren($serviceBodyIds) as $serviceBodyId) {
            $serviceBodyIds[] = $serviceBodyId;
        }

        return collect($serviceBodyIds)->unique();
    }

    public function getAssignedServiceBodyIds(int $userId): Collection
    {
        $serviceBodyIds = ServiceBody::query()
            ->where('principal_user_bigint', $userId)
            ->orWhere(function (Builder $query) use ($userId) {
                $query
                    ->orWhere('editors_string', "$userId")
                    ->orWhere('editors_string', 'LIKE', "$userId,%")
                    ->orWhere('editors_string', 'LIKE', "%,$userId,%")
                    ->orWhere('editors_string', 'LIKE', "%,$userId");
            })
            ->get()
            ->map(fn ($sb) => $sb->id_bigint)
            ->toArray();

        foreach ($this->getChildren($serviceBodyIds) as $serviceBodyId) {
            $serviceBodyIds[] = $serviceBodyId;
        }

        return collect($serviceBodyIds)->unique();
    }

    public function getChildren(array $parents): array
    {
        $ret = array_merge($parents);

        $children = $parents;
        while (!empty($children)) {
            $serviceBodies = ServiceBody::query()->whereIn('sb_owner', $children)->get();
            $children = [];
            foreach ($serviceBodies as $serviceBody) {
                if (in_array($serviceBody->id_bigint, $ret)) {
                    continue;
                }

                $ret[] = $serviceBody->id_bigint;
                $children[] = $serviceBody->id_bigint;
            }
        }

        return $ret;
    }

    public function getParents(array $children): array
    {
        $ret = [];

        $parents = $children;
        while (!empty($parents)) {
            $serviceBodies = ServiceBody::query()->whereIn('id_bigint', $parents)->get();
            $parents = [];
            foreach ($serviceBodies as $serviceBody) {
                if (in_array($serviceBody->id_bigint, $ret)) {
                    continue;
                }

                $ret[] = $serviceBody->id_bigint;

                if (!$serviceBody->sb_owner) {
                    continue;
                }

                $parents[] = $serviceBody->sb_owner;
            }
        }

        return $ret;
    }

    public function import(int $rootServerId, Collection $externalObjects): void
    {
        $rootServer = RootServer::query()->where('id', $rootServerId)->firstOrFail();
        $ignoreServiceBodyIds = collect(config('aggregator.ignore_service_bodies'))->get($rootServer->url, []);
        $externalObjects = $externalObjects->reject(fn ($ex) => in_array($ex->id, $ignoreServiceBodyIds));

        $sourceIds = $externalObjects->map(fn (ExternalServiceBody $ex) => $ex->id);
        ServiceBody::query()
            ->where('root_server_id', $rootServerId)
            ->whereNotIn('source_id', $sourceIds)
            ->delete();

        $bySourceId = ServiceBody::query()
            ->where('root_server_id', $rootServerId)
            ->get()
            ->mapWithKeys(fn ($sb, $_) => [$sb->source_id => $sb]);

        foreach ($externalObjects as $external) {
            $external = $this->castExternal($external);
            $db = $bySourceId->get($external->id);
            if (is_null($db)) {
                $values = $this->externalServiceBodyToValuesArray($rootServerId, $external);
                $bySourceId->put($external->id, $this->create($values));
            } else if (!$external->isEqual($db)) {
                $values = $this->externalServiceBodyToValuesArray($rootServerId, $external);
                $this->update($db->id_bigint, $values);
                $db->refresh();
                $bySourceId->put($external->id, $db);
            }
        }

        foreach ($externalObjects as $external) {
            $external = $this->castExternal($external);

            $parent = $bySourceId->get($external->parentId);
            $db = $bySourceId->get($external->id);

            if (is_null($parent)) {
                if ($db->sb_owner !== 0) {
                    $db->sb_owner = 0;
                    $db->save();
                }
                continue;
            }

            if (is_null($db)) {
                continue;
            }

            if ($db->sb_owner != $parent->id_bigint) {
                $db->sb_owner = $parent->id_bigint;
                $db->save();
            }
        }
    }

    private function castExternal($obj): ExternalServiceBody
    {
        return $obj;
    }

    private function externalServiceBodyToValuesArray(int $rootServerId, ExternalServiceBody $externalServiceBody): array
    {
        return [
            'root_server_id' => $rootServerId,
            'source_id' => $externalServiceBody->id,
            'name_string' => $externalServiceBody->name,
            'description_string' => $externalServiceBody->description,
            'sb_type' => $externalServiceBody->type,
            'uri_string' => $externalServiceBody->url,
            'kml_file_uri_string' => $externalServiceBody->helpline,
            'worldid_mixed' => $externalServiceBody->worldId,
            'sb_meeting_email' => '',
        ];
    }
}
