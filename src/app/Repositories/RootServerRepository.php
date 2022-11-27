<?php

namespace App\Repositories;

use App\Interfaces\RootServerRepositoryInterface;
use App\Models\RootServer;
use App\Repositories\External\ExternalRootServer;
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

    public function import(Collection $externalObjects): void
    {
        $sourceIds = $externalObjects->map(fn (ExternalRootServer $ex) => $ex->id);
        RootServer::query()->whereNotIn('source_id', $sourceIds)->delete();

        foreach ($externalObjects as $externalRoot) {
            $externalRoot = $this->castExternalRootServer($externalRoot);
            $dbRoot = RootServer::query()->firstWhere('source_id', $externalRoot->id);
            $values = ['source_id' => $externalRoot->id, 'name' => $externalRoot->name, 'url' => $externalRoot->url];
            if (is_null($dbRoot)) {
                $this->create($values);
            } else if (!$externalRoot->isEqual($dbRoot)) {
                $this->update($dbRoot->id, $values);
            }
        }
    }

    private function castExternalRootServer($obj): ExternalRootServer
    {
        return $obj;
    }
}
