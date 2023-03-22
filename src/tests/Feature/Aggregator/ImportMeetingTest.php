<?php

namespace Tests\Feature\Aggregator;

use App\LegacyConfig;
use App\Models\Meeting;
use App\Repositories\External\ExternalFormat;
use App\Repositories\External\ExternalMeeting;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\FormatRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\ServiceBodyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\TestCase;

class ImportMeetingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        LegacyConfig::reset();
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

    private function externalFormat(string $id, string $language): ExternalFormat
    {
        return new ExternalFormat([
            'id' => $id,
            'key_string' => 'HY',
            'name_string' => 'Hybrid',
            'description_string' => 'Meets virtually and in person',
            'lang' => $language,
            'format_type_enum' =>'FC2',
            'world_id' => 'HYBR',
        ]);
    }

    private function externalMeeting(ExternalServiceBody $serviceBody, array $formats): ExternalMeeting
    {
        return new ExternalMeeting([
            'id_bigint' => 1,
            'service_body_bigint' => $serviceBody->id,
            'weekday_tinyint' => 1,
            'venue_type' => 1,
            'start_time' => '08:00',
            'duration_time' => '01:00',
            'latitude' => '-123',
            'longitude' => '123',
            'meeting_name' => 'Test Name',
            'comments' => 'Test Comments',
            'virtual_meeting_additional_info' => 'Addl Info',
            'virtual_meeting_link' => 'VM Link',
            'phone_meeting_number' => 'Phone',
            'location_city_subsection' => 'City Subsection',
            'location_nation' => 'Nation',
            'location_postal_code_1' => 'Postal Code',
            'location_province' => 'Province',
            'location_sub_province' => 'Sub Province',
            'location_municipality' => 'Municipality',
            'location_neighborhood' => 'Neighborhood',
            'location_street' => 'Street',
            'location_info' => 'Info',
            'location_text' => 'Text',
            'bus_lines' => 'Bus Lines',
            'train_lines' => 'Train Lines',
            'worldid_mixed' => 'World ID',
            'published' => '1',
            'format_shared_id_list' => collect($formats)->map(fn ($f) => $f->id)->join(',')
        ]);
    }

    private function create(int $rootServerId, int $sourceId, $serviceBodyId, array $formatIds): Meeting
    {
        $meeting = $this->createMeeting(
            [
                'service_body_bigint' => $serviceBodyId,
                'weekday_tinyint' => 2,
                'venue_type' => 2,
                'start_time' => '09:00',
                'duration_time' => '02:00',
                'latitude' => '-323',
                'longitude' => '4',
                'worldid_mixed' => 'World ID changed',
                'published' => '1',
            ],
            [
                'meeting_name' => 'Test Name changed',
                'comments' => 'Test Comments changed',
                'virtual_meeting_additional_info' => 'Addl Info changed',
                'virtual_meeting_link' => 'VM Link changed',
                'phone_meeting_number' => 'Phone changed',
                'location_city_subsection' => 'City Subsection changed',
                'location_nation' => 'Nation changed',
                'location_postal_code_1' => 'Postal Code changed',
                'location_province' => 'Province changed',
                'location_sub_province' => 'Sub Province changed',
                'location_municipality' => 'Municipality changed',
                'location_neighborhood' => 'Neighborhood changed',
                'location_street' => 'Street changed',
                'location_info' => 'Info changed',
                'location_text' => 'Text changed',
                'bus_lines' => 'Bus Lines changed',
                'train_lines' => 'Train Lines changed',
            ]
        );
        $meeting->root_server_id = $rootServerId;
        $meeting->source_id = $sourceId;
        $meeting->save();
        return $meeting;
    }

    public function testCreate()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer1 = $this->createRootServer(1);

        $extSb = $this->externalServiceBody();
        $extF1 = $this->externalFormat('500', 'en');
        $extF2 = $this->externalFormat('501', 'en');
        $externalMeeting = $this->externalMeeting($extSb, [$extF1, $extF2]);

        $sbRepository = new ServiceBodyRepository();
        $sbRepository->import($rootServer1->id, collect([$extSb]));
        $fmtRepository = new FormatRepository();
        $fmtRepository->import($rootServer1->id, collect([$extF1, $extF2]));
        $mtgRepository = new MeetingRepository();
        $mtgRepository->import($rootServer1->id, collect([$externalMeeting]));

        $sb = $sbRepository->search()->first();
        $f1 = $fmtRepository->search()->firstWhere('source_id', $extF1->id);
        $f2 = $fmtRepository->search()->firstWhere('source_id', $extF2->id);
        $allMeetings = $mtgRepository->getSearchResults();
        $this->assertEquals(1, $allMeetings->count());

        $db = $allMeetings->first();
        $this->assertEquals($rootServer1->id, $db->root_server_id);
        $this->assertTrue($externalMeeting->isEqual($db, collect([$sb->id_bigint => $sb->source_id]), collect([$f1->shared_id_bigint => $f1->source_id, $f2->shared_id_bigint => $f2->source_id])));
    }

    public function testUpdate()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer1 = $this->createRootServer(1);
        $rootServer2 = $this->createRootServer(2);

        $extSb = $this->externalServiceBody();
        $extF1 = $this->externalFormat('500', 'en');
        $extF2 = $this->externalFormat('501', 'en');
        $externalMeeting = $this->externalMeeting($extSb, [$extF1, $extF2]);

        $sbRepository = new ServiceBodyRepository();
        $sbRepository->import($rootServer1->id, collect([$extSb]));
        $sbRepository->import($rootServer2->id, collect([$extSb]));
        $fmtRepository = new FormatRepository();
        $fmtRepository->import($rootServer1->id, collect([$extF1, $extF2]));
        $fmtRepository->import($rootServer2->id, collect([$extF1, $extF2]));

        $r1sb = $sbRepository->search(rootServersInclude: [$rootServer1->id])->first();
        $r1f1 = $fmtRepository->search(rootServersInclude: [$rootServer1->id], showAll: true)->firstWhere('source_id', $extF1->id);
        $r1f2 = $fmtRepository->search(rootServersInclude: [$rootServer1->id], showAll: true)->firstWhere('source_id', $extF2->id);
        $r2sb = $sbRepository->search(rootServersInclude: [$rootServer2->id])->first();
        $r2f1 = $fmtRepository->search(rootServersInclude: [$rootServer2->id], showAll: true)->firstWhere('source_id', $extF1->id);
        $r2f2 = $fmtRepository->search(rootServersInclude: [$rootServer2->id], showAll: true)->firstWhere('source_id', $extF2->id);

        $this->create($rootServer1->id, $externalMeeting->id, $r1sb->id_bigint, [$r1f1->shared_id_bigint, $r1f2->shared_id_bigint]);
        $this->create($rootServer2->id, $externalMeeting->id, $r2sb->id_bigint, [$r2f1->shared_id_bigint, $r2f2->shared_id_bigint]);

        $repository = new MeetingRepository();
        $repository->import($rootServer1->id, collect([$externalMeeting]));

        $all = $repository->getSearchResults();
        $this->assertEquals(2, $all->count());

        $db = $all->firstWhere('root_server_id', $rootServer1->id);
        $this->assertNotNull($db);
        $this->assertEquals($rootServer1->id, $db->root_server_id);
        $this->assertTrue($externalMeeting->isEqual($db, collect([$r1sb->id_bigint => $r1sb->source_id]), collect([$r1f1->shared_id_bigint => $r1f1->source_id, $r1f2->shared_id_bigint => $r1f2->source_id])));

        $db = $all->firstWhere('root_server_id', $rootServer2->id);
        $this->assertNotNull($db);
        $this->assertFalse($externalMeeting->isEqual($db, collect([$r2sb->id_bigint => $r2sb->source_id]), collect([$r2f1->shared_id_bigint => $r2f1->source_id, $r2f2->shared_id_bigint => $r2f2->source_id])));
        $this->assertEquals($rootServer2->id, $db->root_server_id);
    }

    // TODO test removing service body removes meeting
    // TODO test updating meeting service body
    // TODO test updating meeting formats
    // TODO test deleting meeting
}
