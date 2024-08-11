<?php

namespace App\Console\Commands;

use App\Interfaces\RootServerRepositoryInterface;
use App\Models\Change;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Models\RootServer;
use App\Models\ServiceBody;
use App\Repositories\External\ExternalRootServer;
use App\Repositories\External\InvalidObjectException;
use App\Repositories\RootServerRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PrimeDatabaseFromLegacyTomato extends Command
{
    protected $signature = 'aggregator:PrimeDatabaseFromLegacyTomato';

    protected $description = 'Primes database from legacy tomato instance';

    public function handle(RootServerRepository $rootServerRepository)
    {
        if (!legacy_config('aggregator_mode_enabled')) {
            $this->error('aggregator mode is not enabled');
            return;
        }

        $this->deleteAllData();
        $this->analyzeTables();

        $tomatoIdToRootServerIdMap = $this->importRootServers($rootServerRepository);
        $this->analyzeTables();

        $this->importServiceBodies($tomatoIdToRootServerIdMap);
        $this->analyzeTables();

        $this->importFormats($tomatoIdToRootServerIdMap);
        $this->analyzeTables();

        $this->importMeetings($tomatoIdToRootServerIdMap);
        $this->analyzeTables();
    }

    private function deleteAllData(): void
    {
        $this->info('deleting existing data');
        RootServer::query()->delete();
        Meeting::query()->truncate();
        MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
        MeetingLongData::query()->whereNot('meetingid_bigint', 0)->delete();
        ServiceBody::query()->truncate();
        Format::query()->truncate();
        Change::query()->truncate();
    }

    private function importRootServers(RootServerRepositoryInterface $rootServerRepository): Collection
    {
        $this->info('importing root servers');
        $tomatoRootServers = collect($this->httpGet('https://tomato.bmltenabled.org/rest/v1/rootservers/'));
        $rootServerRepository->import(
            $tomatoRootServers
                ->map(fn ($o) => [
                    'id' => $o['source_id'],
                    'name' => $o['name'],
                    'rootURL' => rtrim($o['root_server_url']) . '/',
                ])
                ->map(function ($rootServer) {
                    try {
                        return new ExternalRootServer($rootServer);
                    } catch (InvalidObjectException) {
                        return null;
                    }
                })
                ->reject(fn($e) => is_null($e))
        );

        return $rootServerRepository->search()
            ->mapWithKeys(fn ($rs, $_) => [$this->urlToId($tomatoRootServers->where('source_id', $rs->source_id)->firstOrFail()['url']) => $rs->id]);
    }

    private function importServiceBodies(Collection $tomatoIdToRootServerIdMap)
    {
        $this->info('importing service bodies');
        $url = "https://tomato.bmltenabled.org/rest/v1/servicebodies/";
        while (true) {
            $response = $this->httpGet($url);
            $values = collect($response['results'])
                ->map(fn ($o) => [
                    'id_bigint' => $this->urlToId($o['url']),
                    'root_server_id' => $tomatoIdToRootServerIdMap[$this->urlToId($o['root_server'])],
                    'source_id' => $o['source_id'],
                    'name_string' => $o['name'],
                    'description_string' => $o['description'],
                    'sb_meeting_email' => '',
                ])
                ->toArray();

            ServiceBody::insert($values);

            $url = $response['next'];
            if (!$url) {
                break;
            }
        }
    }

    private function importFormats(Collection $tomatoIdToRootServerIdMap)
    {
        $this->info('importing formats');
        $url = "https://tomato.bmltenabled.org/rest/v1/formats/";
        while (true) {
            $values = [];
            $response = $this->httpGet($url);
            foreach ($response['results'] as $o) {
                foreach ($o['translatedformats'] as $t) {
                    $values[] = [
                        'shared_id_bigint' => $this->urlToId($o['url']),
                        'root_server_id' => $tomatoIdToRootServerIdMap[$this->urlToId($o['root_server'])],
                        'source_id' => $o['source_id'],
                        'lang_enum' => $t['language'],
                    ];
                }
            }

            Format::insert($values);

            $url = $response['next'];
            if (!$url) {
                break;
            }
        }
    }

    private function importMeetings(Collection $tomatoIdToRootServerIdMap)
    {
        $this->info('importing meetings');
        $url = "https://tomato.bmltenabled.org/rest/v1/meetings/";
        while (true) {
            $response = $this->httpGet($url);
            $values = collect($response['results'])
                ->map(fn ($o) => [
                    'id_bigint' => $this->urlToId($o['url']),
                    'root_server_id' => $tomatoIdToRootServerIdMap[$this->urlToId($o['root_server'])],
                    'source_id' => $o['source_id'],
                    'service_body_bigint' => 0,
                    'published' => 1,
                ])
                ->toArray();

            Meeting::insert($values);

            $url = $response['next'];
            if (!$url) {
                break;
            }
        }
    }

    private function urlToId($url): int
    {
        return intval(collect(explode('/', rtrim($url, '/')))->last());
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
            DB::statement("ANALYZE TABLE $tableName;");
        }
    }

    private function httpGet(string $url): array
    {
        $response = Http::get($url);
        if (!$response->ok()) {
            throw new \Exception("Got bad status code {$response->status()} from $url");
        }

        return $response->json();
    }
}
