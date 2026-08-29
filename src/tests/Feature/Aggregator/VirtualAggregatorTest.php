<?php

namespace Tests\Feature\Aggregator;

use App\Aggregator\LocaleResolver;
use App\Aggregator\TimezoneResolver;
use App\FromFileConfig;
use App\Models\RootServer;
use App\Repositories\External\ExternalFormat;
use App\Repositories\External\ExternalMeeting;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\FormatRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\ServiceBodyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\TestCase;

class VirtualAggregatorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        FromFileConfig::reset();
        LocaleResolver::reset();
        TimezoneResolver::reset();
        parent::tearDown();
    }

    private function externalServiceBody(): ExternalServiceBody
    {
        return new ExternalServiceBody([
            'id' => '171',
            'parent_id' => '0',
            'name' => 'Area',
            'description' => 'description',
            'type' => 'AS',
            'url' => 'http://example.org',
            'helpline' => 'helpline',
            'world_id' => 'AR6339',
        ]);
    }

    private function externalFormat(string $id): ExternalFormat
    {
        return new ExternalFormat([
            'id' => $id,
            'key_string' => 'VM',
            'name_string' => 'Virtual',
            'description_string' => 'Virtual Meeting',
            'lang' => 'en',
            'format_type_enum' => 'FC3',
            'world_id' => 'VM',
        ]);
    }

    private function externalMeeting(int $id, int $venueType, array $overrides = []): ExternalMeeting
    {
        return new ExternalMeeting(array_merge([
            'id_bigint' => $id,
            'service_body_bigint' => 171,
            'weekday_tinyint' => 1,
            'venue_type' => $venueType,
            'start_time' => '20:00',
            'duration_time' => '01:00',
            'meeting_name' => "Meeting $id",
            'published' => '1',
            'format_shared_id_list' => '',
        ], $overrides));
    }

    private function importDeps(RootServer $rootServer): void
    {
        app(ServiceBodyRepository::class)->import($rootServer->id, collect([$this->externalServiceBody()]));
        (new FormatRepository())->import($rootServer->id, collect([$this->externalFormat('500')]));
    }

    public function testVirtualOnlyImportsVirtualAndHybridOnly()
    {
        FromFileConfig::set('aggregator_mode_enabled', true);
        config(['aggregator.virtual_only' => true]);
        $rootServer = $this->createRootServer(1);
        $this->importDeps($rootServer);

        $repository = new MeetingRepository();
        $repository->import($rootServer->id, collect([
            $this->externalMeeting(1, 1), // in person - excluded
            $this->externalMeeting(2, 2), // virtual - kept
            $this->externalMeeting(3, 3), // hybrid - kept
        ]));

        $venueTypes = $repository->getSearchResults()->pluck('venue_type')->sort()->values()->all();
        $this->assertEquals([2, 3], $venueTypes);
    }

    public function testVirtualOnlyDeletesExistingInPersonMeetings()
    {
        FromFileConfig::set('aggregator_mode_enabled', true);
        config(['aggregator.virtual_only' => true]);
        $rootServer = $this->createRootServer(2);
        $this->importDeps($rootServer);
        $sbId = app(ServiceBodyRepository::class)->search(rootServersInclude: [$rootServer->id])->first()->id_bigint;

        $existing = $this->createMeeting(['service_body_bigint' => $sbId, 'venue_type' => 1]);
        $existing->root_server_id = $rootServer->id;
        $existing->source_id = 1;
        $existing->save();

        (new MeetingRepository())->import($rootServer->id, collect([$this->externalMeeting(2, 2)]));

        $all = (new MeetingRepository())->getSearchResults();
        $this->assertEquals(1, $all->count());
        $this->assertEquals(2, $all->first()->venue_type);
    }

    public function testTimezoneResolverFindsNearestZoneFromCoordinates()
    {
        // Roughly New York City.
        $meeting = $this->externalMeeting(1, 2, ['latitude' => '40.7143', 'longitude' => '-74.006']);
        $this->assertEquals('America/New_York', (new TimezoneResolver())->resolve($meeting));
    }

    public function testTimezoneResolverReturnsNullWithoutCoordinates()
    {
        $meeting = $this->externalMeeting(1, 2);
        $this->assertNull((new TimezoneResolver())->resolve($meeting));
    }

    public function testLocaleResolverReadsNativeLang()
    {
        $rootServer = $this->createRootServer(3);
        $rootServer->server_info = json_encode(['nativeLang' => 'es', 'langs' => 'es,en']);
        $rootServer->save();

        $this->assertEquals('es', (new LocaleResolver())->infer($rootServer->id));
    }

    public function testImportFillsMissingTimezoneAndLocale()
    {
        FromFileConfig::set('aggregator_mode_enabled', true);
        config(['aggregator.geocode_timezones' => true, 'aggregator.infer_locale' => true]);
        $rootServer = $this->createRootServer(4);
        $rootServer->server_info = json_encode(['nativeLang' => 'de', 'langs' => 'de']);
        $rootServer->save();
        $this->importDeps($rootServer);

        // Virtual meeting with coordinates but no time_zone / lang_enum from the source.
        (new MeetingRepository())->import($rootServer->id, collect([
            $this->externalMeeting(1, 2, ['latitude' => '40.7143', 'longitude' => '-74.006']),
        ]));

        $db = (new MeetingRepository())->getSearchResults()->first();
        $this->assertEquals('America/New_York', $db->time_zone);
        $this->assertEquals('de', $db->lang_enum);
    }
}
