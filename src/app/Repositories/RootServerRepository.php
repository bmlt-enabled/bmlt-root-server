<?php

namespace App\Repositories;

use App\Interfaces\RootServerRepositoryInterface;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Models\RootServer;
use App\Repositories\External\ExternalRootServer;
use Illuminate\Support\Collection;

class RootServerRepository implements RootServerRepositoryInterface
{
    public function search(bool $eagerStatistics = false): Collection
    {
        $rootServers = RootServer::query();
        if ($eagerStatistics) {
            $rootServers = $rootServers->with(['statistics' => function ($query) {
                $query->where('is_latest', true);
            }]);
        }

        return $rootServers->get();
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
        $ignoreRootServerUrls = config('aggregator.ignore_root_servers');
        $externalObjects = $externalObjects->reject(fn (ExternalRootServer $ex) => in_array($ex->url, $ignoreRootServerUrls));

        $sourceIds = $externalObjects->map(fn (ExternalRootServer $ex) => $ex->id);
        RootServer::query()->whereNotIn('source_id', $sourceIds)->delete();

        // TODO test these
        MeetingData::query()
            ->whereNot('meetingid_bigint', 0)
            ->whereNotIn('meetingid_bigint', function ($query) {
                $query->select('id_bigint')->from((new Meeting)->getTable());
            })->delete();
        MeetingLongData::query()
            ->whereNot('meetingid_bigint', 0)
            ->whereNotIn('meetingid_bigint', function ($query) {
                $query->select('id_bigint')->from((new Meeting)->getTable());
            })->delete();

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
