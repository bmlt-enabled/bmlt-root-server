<?php

namespace App\Console\Commands;

use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\RootServerRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\RootServer;
use App\Models\ServiceBody;
use App\Repositories\External\ExternalRootServer;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\External\InvalidObjectException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportRootServers extends Command
{
    protected $signature = 'aggregate:ImportRootServers {--list-url=https://raw.githubusercontent.com/bmlt-enabled/tomato/master/rootServerList.json}';

    protected $description = 'Import root servers';

    public function handle(
        RootServerRepositoryInterface $rootServerRepository,
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository
    ) {
        if (!legacy_config('aggregator_mode_enabled')) {
            return;
        }

        DB::transaction(function () use ($rootServerRepository, $serviceBodyRepository) {
            ServiceBody::query()->whereNull('root_server_id')->delete();
            Format::query()->whereNull('root_server_id')->delete();
            Meeting::query()->whereNull('root_server_id')->delete();

            $this->importRootServers($rootServerRepository);
            foreach ($rootServerRepository->search() as $rootServer) {
                $this->importServiceBodies($serviceBodyRepository, $rootServer);
            }
        });
    }

    private function importRootServers(RootServerRepositoryInterface $rootServerRepository)
    {
        try {
            $url = $this->option('list-url');
            $response = $this->httpGet($url);
            $externalRootServers = collect($response)
                ->map(function ($rootServer) {
                    try {
                        return new ExternalRootServer($rootServer);
                    } catch (InvalidObjectException) {
                        return null;
                    }
                })
                ->reject(fn($e) => is_null($e));
            $rootServerRepository->import($externalRootServers);
        } catch (\Exception $e) {
            // TODO Log something and save an error to the database
            throw $e;
        }
    }

    private function importServiceBodies(ServiceBodyRepositoryInterface $serviceBodyRepository, RootServer $rootServer)
    {
        try {
            $url = rtrim($rootServer->url, '/') . '/client_interface/json/?switcher=GetServiceBodies';
            $response = $this->httpGet($url);
            $externalServiceBodies = collect($response)
                ->map(function ($serviceBody) {
                    try {
                        return new ExternalServiceBody($serviceBody);
                    } catch (InvalidObjectException) {
                        // TODO: Log something and save an error to the database
                        return null;
                    }
                })
                ->reject(fn($e) => is_null($e));
            $serviceBodyRepository->import($rootServer->id, $externalServiceBodies);
        } catch (\Exception $e) {
            // TODO Log something and save an error to the database
            return;
        }
    }

    private function httpGet(string $url): array
    {
        $response = Http::get($url);
        if (!$response->ok()) {
            throw new \Exception("Got bad status code {$response->status()} retrieving root servers.");
        }

        return $response->json();
    }
}
