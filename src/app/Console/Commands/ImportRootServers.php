<?php

namespace App\Console\Commands;

use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\RootServerRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Repositories\External\ExternalRootServer;
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
        return DB::transaction(function () use ($rootServerRepository) {
            $this->importRootServers($rootServerRepository);
        });
    }

    private function importRootServers(RootServerRepositoryInterface $rootServerRepository)
    {
        $rootServerListUrl = $this->option('list-url');

        $response = Http::get($rootServerListUrl);
        if (!$response->ok()) {
            $status = $response->status();
            throw new \Exception("Got bad status code $status retrieving root servers.");
        }

        $externalRootServers = collect($response->json())->map(fn ($r) => new ExternalRootServer($r));

        $rootServerRepository->import($externalRootServers);
    }
}
