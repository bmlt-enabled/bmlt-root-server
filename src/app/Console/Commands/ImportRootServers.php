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
use Carbon\Carbon;
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
            $this->error('aggregator mode is not enabled');
            return;
        }

        DB::transaction(fn () => $this->deleteNonAggregatorData());
        DB::transaction(fn () => $this->importRootServersList($rootServerRepository));
        foreach ($rootServerRepository->search() as $rootServer) {
            try {
                DB::transaction(fn() => $this->importRootServer(
                    $rootServer,
                    $rootServerRepository,
                    $formatRepository,
                    $meetingRepository,
                    $serviceBodyRepository
                ));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->analyzeTables();
        }
    }

    private function deleteNonAggregatorData(): void
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
            $this->error($e->getMessage());
            throw $e;
        }
    }

    private function importRootServer(
        RootServer $rootServer,
        RootServerRepositoryInterface $rootServerRepository,
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository,
    ): void {
        $this->info("importing root server $rootServer->id:$rootServer->url");
        $this->importServiceBodies($rootServer, $serviceBodyRepository);
        $this->importFormats($rootServer, $formatRepository);
        $this->importMeetings($rootServer, $meetingRepository);
        $rootServerRepository->update($rootServer->id, ['last_successful_import' => Carbon::now()]);
    }

    private function importServiceBodies(RootServer $rootServer, ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->info('importing service bodies');
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
    }

    private function importFormats(RootServer $rootServer, FormatRepositoryInterface $formatRepository)
    {
        $this->info('importing formats');
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
    }

    private function importMeetings(RootServer $rootServer, MeetingRepositoryInterface $meetingRepository)
    {
        $this->info('importing meetings');
        $url = rtrim($rootServer->url, '/') . '/client_interface/json/?switcher=GetSearchResults';
        $response = $this->httpGet($url);
        $externalMeetings = collect($response)
            ->map(function ($meeting) {
                try {
                    return new ExternalMeeting($meeting);
                } catch (InvalidObjectException) {
                    // TODO: Log something and save an error to the database
                    return null;
                }
            })
            ->reject(fn($e) => is_null($e));
        $meetingRepository->import($rootServer->id, $externalMeetings);
    }

    private function analyzeTables(): void
    {
        $this->info('analyzing tables');
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
        $headers = ['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0 +aggregator'];
        $response = Http::withHeaders($headers)->get($url);
        if (!$response->ok()) {
            throw new \Exception("Got bad status code {$response->status()} from $url");
        }

        return $response->json();
    }
}
