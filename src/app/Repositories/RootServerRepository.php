<?php

namespace App\Repositories;

use App\Interfaces\RootServerRepositoryInterface;
use App\Models\RootServer;
use Illuminate\Support\Collection;

class RootServerRepository implements RootServerRepositoryInterface
{
    public function search(): Collection
    {
        return RootServer::all();
    }

    public function create(array $values): RootServer
    {
        return RootServer::create($values);
    }

    public function update(int $id, array $values): bool
    {
        $rootServer = RootServer::find($id);
        if (!is_null($rootServer)) {
            RootServer::query()->where('id', $id)->update($values);
            return true;
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $rootServer = RootServer::find($id);
        if (!is_null($rootServer)) {
            $rootServer->delete();
            return true;
        }
        return false;
    }
}
