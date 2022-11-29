<?php

namespace App\Console\Commands;

use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\RootServerRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Change;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Models\RootServer;
use App\Models\ServiceBody;
use App\Repositories\External\ExternalFormat;
use App\Repositories\External\ExternalMeeting;
use App\Repositories\External\ExternalRootServer;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\External\InvalidObjectException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportRootServers extends Command
{
    protected $signature = 'aggregator:ImportRootServers {--list-url=https://raw.githubusercontent.com/bmlt-enabled/tomato/master/rootServerList.json}';

    protected $description = 'Import root servers';

    public function handle(
        RootServerRepositoryInterface $rootServerRepository,
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository
    ) {
        if (!legacy_config('aggregator_mode_enabled')) {
            // TODO print/log something
            return;
        }

        DB::transaction(fn () => $this->clearNonAggregatorData());
        DB::transaction(fn () => $this->importRootServersList($rootServerRepository));
        foreach ($rootServerRepository->search() as $rootServer) {
            DB::transaction(fn () => $this->importRootServer(
                $rootServer,
                $formatRepository,
                $meetingRepository,
                $serviceBodyRepository
            ));
        }

        $this->analyzeTables();
    }

    private function clearNonAggregatorData(): void
    {
        $meetingIds = Meeting::query()->whereNull('root_server_id')->pluck('id_bigint');
        Meeting::query()->whereIn('id_bigint', $meetingIds)->delete();
        MeetingData::query()->whereIn('meetingid_bigint', $meetingIds)->whereNot('meetingid_bigint', 0)->delete();
        MeetingLongData::query()->whereIn('meetingid_bigint', $meetingIds)->whereNot('meetingid_bigint', 0)->delete();
        ServiceBody::query()->whereNull('root_server_id')->delete();
        Format::query()->whereNull('root_server_id')->delete();
        Change::query()->delete();
    }

    private function importRootServersList(RootServerRepositoryInterface $rootServerRepository)
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

    private function importRootServer(
        RootServer $rootServer,
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository,
    ): void {
        $this->importServiceBodies($rootServer, $serviceBodyRepository);
        $this->importFormats($rootServer, $formatRepository);
        $this->importMeetings($rootServer, $meetingRepository);
    }

    private function importServiceBodies(RootServer $rootServer, ServiceBodyRepositoryInterface $serviceBodyRepository)
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

    private function importFormats(RootServer $rootServer, FormatRepositoryInterface $formatRepository)
    {
        try {
            $url = rtrim($rootServer->url, '/') . '/client_interface/json/?switcher=GetFormats';
            $response = $this->httpGet($url);
            $externalFormats = collect($response)
                ->map(function ($format) {
                    try {
                        return new ExternalFormat($format);
                    } catch (InvalidObjectException) {
                        // TODO: Log something and save an error to the database
                        return null;
                    }
                })
                ->reject(fn($e) => is_null($e));
            $formatRepository->import($rootServer->id, $externalFormats);
        } catch (\Exception $e) {
            // TODO Log something and save an error to the database
            return;
        }
    }

    private function importMeetings(RootServer $rootServer, MeetingRepositoryInterface $meetingRepository)
    {
        try {
            $url = rtrim($rootServer->url, '/') . '/client_interface/json/?switcher=GetSearchResults';
            $response = $this->httpGet($url);
            $externalMeetings = collect($response)
                ->map(function ($meeting) use ($rootServer) {
                    try {
                        return new ExternalMeeting($meeting);
                    } catch (InvalidObjectException) {
                        // TODO: Log something and save an error to the database
                        return null;
                    }
                })
                ->reject(fn($e) => is_null($e));
                $meetingRepository->import($rootServer->id, $externalMeetings);
        } catch (\Exception $e) {
            // TODO Log something and save an error to the database
            return;
        }
    }

    private function analyzeTables(): void
    {
        $prefix = DB::connection()->getTablePrefix();
        $tableNames = [
            $prefix . (new Meeting)->getTable(),
            $prefix . (new MeetingData)->getTable(),
            $prefix . (new MeetingLongData)->getTable(),
            $prefix . (new ServiceBody)->getTable(),
            $prefix . (new Format)->getTable(),
        ];
        foreach ($tableNames as $tableName) {
            DB::statement(DB::raw("ANALYZE TABLE $tableName;"));
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
