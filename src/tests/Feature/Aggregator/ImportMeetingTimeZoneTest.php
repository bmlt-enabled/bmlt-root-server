<?php

namespace Tests\Feature\Aggregator;

use App\FromFileConfig;
use App\Models\Meeting;
use App\Repositories\External\ExternalMeeting;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\MeetingRepository;
use App\Repositories\ServiceBodyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\TestCase;

class ImportMeetingTimeZoneTest extends TestCase
{
    use RefreshDatabase;

    // Coordinates in lower Manhattan -> America/New_York.
    private const NY_LAT = 40.7128;
    private const NY_LON = -74.0060;

    protected function setUp(): void
    {
        parent::setUp();
        FromFileConfig::set('aggregator_mode_enabled', true);
    }

    protected function tearDown(): void
    {
        FromFileConfig::reset();
        parent::tearDown();
    }

    private function externalServiceBody(): ExternalServiceBody
    {
        return new ExternalServiceBody([
            'id' => '171',
            'parent_id' => '0',
            'name' => 'Trans Umbrella Area',
            'description' => 'description',
            'type' => 'AS',
            'url' => 'http://transuana.org',
            'helpline' => 'helpline',
            'world_id' => 'AR6339',
        ]);
    }

    private function externalMeeting(array $overrides = []): ExternalMeeting
    {
        return new ExternalMeeting(array_merge([
            'id_bigint' => 1,
            'service_body_bigint' => 171,
            'weekday_tinyint' => 1,
            'venue_type' => Meeting::VENUE_TYPE_VIRTUAL,
            'start_time' => '20:00',
            'duration_time' => '01:00',
            'time_zone' => null,
            'lang_enum' => 'en',
            'email_contact' => null,
            'latitude' => self::NY_LAT,
            'longitude' => self::NY_LON,
            'meeting_name' => 'Test Meeting',
            'comments' => null,
            'published' => '1',
            'format_shared_id_list' => '',
        ], $overrides));
    }

    private int $rootServerId;

    private function import(ExternalMeeting $meeting): MeetingRepository
    {
        $this->rootServerId = $this->createRootServer(1)->id;
        $repository = new MeetingRepository();
        app(ServiceBodyRepository::class)->import($this->rootServerId, collect([$this->externalServiceBody()]));
        $repository->import($this->rootServerId, collect([$meeting]));
        return $repository;
    }

    private function storedMeeting(MeetingRepository $repository): Meeting
    {
        return $repository->getSearchResults()->first();
    }

    public function testDerivesTimeZoneForVirtualMeetingMissingIt(): void
    {
        $repository = $this->import($this->externalMeeting(['venue_type' => Meeting::VENUE_TYPE_VIRTUAL]));
        $this->assertEquals('America/New_York', $this->storedMeeting($repository)->time_zone);
    }

    public function testDerivesTimeZoneForHybridMeetingMissingIt(): void
    {
        $repository = $this->import($this->externalMeeting(['venue_type' => Meeting::VENUE_TYPE_HYBRID]));
        $this->assertEquals('America/New_York', $this->storedMeeting($repository)->time_zone);
    }

    public function testTreatsLiteralNullStringAsMissing(): void
    {
        $repository = $this->import($this->externalMeeting(['time_zone' => 'NULL']));
        $this->assertEquals('America/New_York', $this->storedMeeting($repository)->time_zone);
    }

    public function testDoesNotOverrideTimeZoneProvidedBySource(): void
    {
        $repository = $this->import($this->externalMeeting(['time_zone' => 'America/Denver']));
        $this->assertEquals('America/Denver', $this->storedMeeting($repository)->time_zone);
    }

    public function testDoesNotDeriveForInPersonMeeting(): void
    {
        $repository = $this->import($this->externalMeeting(['venue_type' => Meeting::VENUE_TYPE_IN_PERSON]));
        $this->assertEmpty($this->storedMeeting($repository)->time_zone);
    }

    public function testDoesNotDeriveWhenCoordinatesAreMissing(): void
    {
        $repository = $this->import($this->externalMeeting(['latitude' => null, 'longitude' => null]));
        $this->assertEmpty($this->storedMeeting($repository)->time_zone);
    }

    public function testReimportWithUnchangedSourceDoesNotUpdate(): void
    {
        $meeting = $this->externalMeeting();
        $repository = $this->import($meeting);
        $this->assertEquals('America/New_York', $this->storedMeeting($repository)->time_zone);

        $result = $repository->import($this->rootServerId, collect([$this->externalMeeting()]));
        $this->assertEquals(0, $result->numUpdated);
        $this->assertEquals('America/New_York', $this->storedMeeting($repository)->time_zone);
    }

    public function testSourceProvidedTimeZoneWinsOnLaterSync(): void
    {
        $repository = $this->import($this->externalMeeting());
        $this->assertEquals('America/New_York', $this->storedMeeting($repository)->time_zone);

        $result = $repository->import($this->rootServerId, collect([$this->externalMeeting(['time_zone' => 'America/Denver'])]));
        $this->assertEquals(1, $result->numUpdated);
        $this->assertEquals('America/Denver', $this->storedMeeting($repository)->time_zone);
    }

    public function testDerivationCanBeDisabledByConfig(): void
    {
        config(['aggregator.derive_missing_timezones' => false]);
        $repository = $this->import($this->externalMeeting());
        $this->assertEmpty($this->storedMeeting($repository)->time_zone);
    }
}
