<?php

namespace Tests\Feature;

use App\Http\Resources\Query\MeetingResource;
use App\LegacyConfig;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Models\RootServer;
use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GetSearchResultsTest extends TestCase
{
    use RefreshDatabase;

    private static $mainFieldDefaults = [
        'worldid_mixed' => 'worldid_mixed_default',
        'service_body_bigint' => 1,
        'weekday_tinyint' => 1,
        'venue_type' => 1,
        'start_time' => '19:00:00',
        'duration_time' => '01:00:00',
        'time_zone' => '',
        'formats' => '17,29,30', // O,To,Tr
        'lang_enum' => 'en',
        'longitude' => -79.793701171875,
        'latitude' => 36.065752051707,
        'published' => 1,
    ];

    private static $dataFieldDefaults = [
        'meeting_name' => 'NA Meeting',
    ];

    private function createRootServer(int $sourceId, string $name = 'test', string $url = 'https://test.com'): RootServer
    {
        return RootServer::create([
            'source_id' => $sourceId,
            'name' => $name,
            'url' => $url
        ]);
    }

    private function createMeeting(array $mainFields = [], array $dataFields = [], array $longDataFields = [])
    {
        static $dataFieldTemplates;
        if (!isset($dataFieldTemplates)) {
            $dataFieldTemplates = MeetingData::query()
                ->where('meetingid_bigint', 0)
                ->get()
                ->mapWithKeys(fn ($value, $_) => [$value->key => $value]);
        }

        $meeting = Meeting::create(array_merge(self::$mainFieldDefaults, $mainFields));

        $dataFields = array_merge(self::$dataFieldDefaults, $dataFields);
        foreach (array_keys($longDataFields) as $fieldName) {
            unset($dataFields[$fieldName]);
        }

        foreach ($dataFields as $fieldName => $fieldValue) {
            $fieldTemplate = $dataFieldTemplates->get($fieldName);
            if (is_null($fieldTemplate)) {
                throw new \Exception("unknown field '$fieldName' specified in test meeting");
            }

            $meeting->data()->create([
                'key' => $fieldName,
                'field_prompt' => $fieldTemplate->field_prompt,
                'lang_enum' => 'en',
                'data_string' => $fieldValue,
                'visibility' => $fieldTemplate->visibility,
            ]);
        }

        foreach ($longDataFields as $fieldName => $fieldValue) {
            $fieldTemplate = $dataFieldTemplates->get($fieldName);
            if (is_null($fieldTemplate)) {
                throw new \Exception("unknown field '$fieldName' specified in test meeting");
            }

            $meeting->longdata()->create([
                'key' => $fieldName,
                'field_prompt' => $fieldTemplate->field_prompt,
                'lang_enum' => 'en',
                'data_blob' => $fieldValue,
                'visibility' => $fieldTemplate->visibility,
            ]);
        }

        return $meeting;
    }

    private function createZone(string $name, string $description, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $principalUserId = null, array $assignedUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'ZF', 0, $uri, $helpline, $worldId, $email, $principalUserId, $assignedUserIds);
    }

    private function createRegion(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $principalUserId = null, array $assignedUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'RS', $sbOwner, $uri, $helpline, $worldId, $email, $principalUserId, $assignedUserIds);
    }

    private function createArea(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $principalUserId = null, array $assignedUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'AS', $sbOwner, $uri, $helpline, $worldId, $email, $principalUserId, $assignedUserIds);
    }

    private function createServiceBody(string $name, string $description, string $sbType, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $principalUserId = null, array $assignedUserIds = null)
    {
        return ServiceBody::create([
            'sb_owner' => $sbOwner,
            'name_string' => $name,
            'description_string' => $description,
            'sb_type' => $sbType,
            'uri_string' => $uri,
            'kml_file_uri_string' => $helpline,
            'worldid_mixed' => $worldId,
            'sb_meeting_email' => $email ?? '',
            'principal_user_bigint' => $principalUserId,
            'editors_string' => !is_null($assignedUserIds) ? implode(',', $assignedUserIds) : null,
        ]);
    }

    private function createFormat1(string $langEnum = 'en')
    {
        return $this->createFormat(901, 'A', 'Open1', 'desc1', $langEnum, 'worldid');
    }

    private function createFormat2(string $langEnum = 'en')
    {
        return $this->createFormat(902, 'B', 'Closed2', 'desc2', $langEnum, 'worldid');
    }

    private function createFormat3(string $langEnum = 'en')
    {
        return $this->createFormat(903, 'C', 'Closed3', 'desc3', $langEnum, 'worldid');
    }

    private function createFormat(int $sharedId, string $keyString, string $nameString, string $description = null, string $langEnum = 'en', string $worldId = null, string $formatTypeEnum = 'FC')
    {
        return Format::create([
            'shared_id_bigint' => $sharedId,
            'key_string' => $keyString,
            'name_string' => $nameString,
            'lang_enum' => $langEnum,
            'description_string' => $description,
            'worldid_mixed' => $worldId,
            'format_type_enum' => $formatTypeEnum,
        ]);
    }

    private string $userPassword = 'goodpassword';

    private function createServerAdminUser()
    {
        return User::create([
            'user_level_tinyint' => User::USER_LEVEL_ADMIN,
            'name_string' => 'test',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'test',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    private function createServiceBodyAdminUser()
    {
        return User::create([
            'user_level_tinyint' => User::USER_LEVEL_SERVICE_BODY_ADMIN,
            'name_string' => 'test',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'test',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        MeetingResource::resetStaticVariables();
        parent::tearDown();
    }

    public function testMeetingForeignKeys()
    {
        $zone = $this->createZone('zone', 'zone');
        $region = $this->createRegion('region', 'region', $zone->id_bigint);
        $area = $this->createRegion('region', 'region', $region->id_bigint);
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint]);
        $areaModel = $meeting->serviceBody;
        $this->assertEquals($area->id_bigint, $areaModel->id_bigint);
        $regionModel = $areaModel->parent;
        $this->assertEquals($region->id_bigint, $regionModel->id_bigint);
        $zoneModel = $regionModel->parent;
        $this->assertEquals($zone->id_bigint, $zoneModel->id_bigint);
    }

    public function testJsonp()
    {
        $response = $this->get('/client_interface/jsonp/?switcher=GetSearchResults&callback=asdf');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
        $content = $response->content();
        $this->assertStringStartsWith('/**/asdf([', $content);
        $this->assertStringEndsWith(']);', $content);
    }

    // meeting_ids
    //
    //
    public function testMeetingIdsNone()
    {
        $meeting1 = $this->createMeeting();
        $badId = $meeting1->id_bigint + 1;
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_ids[]=$badId")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testMeetingIdsOne()
    {
        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_ids=$meeting1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testMeetingIdsTwo()
    {
        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();
        $meeting3 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_ids[]=$meeting1->id_bigint&meeting_ids[]=$meeting2->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testMultipleLeadingSlashes()
    {
        $meeting1 = $this->createMeeting();
        $badId = $meeting1->id_bigint + 1;
        $this->get("///client_interface/json/?switcher=GetSearchResults&meeting_ids[]=$meeting1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    // weekdays
    //
    //
    public function testWeekdayNone()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 5]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&weekdays=1")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testWeekdayIncludeOne()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 5]);
        $meeting2 = $this->createMeeting(['weekday_tinyint' => 3]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&weekdays=6")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint), 'weekday_tinyint' => '6']);
    }

    public function testWeekdayIncludeTwo()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 5]);
        $meeting2 = $this->createMeeting(['weekday_tinyint' => 3]);
        $meeting3 = $this->createMeeting(['weekday_tinyint' => 1]);
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&weekdays[]=6&weekdays[]=4")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('6', $meeting['weekday_tinyint']);
        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertEquals('4', $meeting['weekday_tinyint']);
    }

    public function testWeekdayExcludeOne()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 5]);
        $meeting2 = $this->createMeeting(['weekday_tinyint' => 3]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&weekdays=-6")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint), 'weekday_tinyint' => '4']);
    }

    public function testWeekdayExcludeTwo()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 5]);
        $meeting2 = $this->createMeeting(['weekday_tinyint' => 3]);
        $meeting3 = $this->createMeeting(['weekday_tinyint' => 1]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&weekdays[]=-6&weekdays[]=-4")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint), 'weekday_tinyint' => '2']);
    }

    // venue_types
    //
    //
    public function testVenueTypeNone()
    {
        $meeting1 = $this->createMeeting(['venue_type' => 2]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&venue_types=1")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testVenueTypeIncludeOne()
    {
        $meeting1 = $this->createMeeting(['venue_type' => 1]);
        $meeting2 = $this->createMeeting(['venue_type' => 2]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&venue_types=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testVenueTypeIncludeTwo()
    {
        $meeting1 = $this->createMeeting(['venue_type' => 1]);
        $meeting2 = $this->createMeeting(['venue_type' => 2]);
        $meeting3 = $this->createMeeting(['venue_type' => 3]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&venue_types[]=1&venue_types[]=2")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testVenueTypeExcludeOne()
    {
        $meeting1 = $this->createMeeting(['venue_type' => 1]);
        $meeting2 = $this->createMeeting(['venue_type' => 2]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&venue_types=-1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testVenueTypeExcludeTwo()
    {
        $meeting1 = $this->createMeeting(['venue_type' => 1]);
        $meeting2 = $this->createMeeting(['venue_type' => 2]);
        $meeting3 = $this->createMeeting(['venue_type' => 3]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&venue_types[]=-1&venue_types[]=-2")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint)]);
    }

    // services
    //
    //
    public function testServicesNone()
    {
        $meeting1 = $this->createMeeting(['service_body_bigint' => 1]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services=2")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testServicesIncludeOne()
    {
        $meeting1 = $this->createMeeting(['service_body_bigint' => 1]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => 2]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testServicesIncludeTwo()
    {
        $meeting1 = $this->createMeeting(['service_body_bigint' => 1]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => 2]);
        $meeting3 = $this->createMeeting(['service_body_bigint' => 3]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=1&services[]=2")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testServicesExcludeOne()
    {
        $meeting1 = $this->createMeeting(['service_body_bigint' => 1]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => 2]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services=-1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testServicesExcludeTwo()
    {
        $meeting1 = $this->createMeeting(['service_body_bigint' => 1]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => 2]);
        $meeting3 = $this->createMeeting(['service_body_bigint' => 3]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=-1&services[]=-2")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint)]);
    }

    public function testServicesIncludeRecursiveArea()
    {
        $zone = $this->createZone('zone', 'zone');
        $region1 = $this->createRegion('region1', 'region1', $zone->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', $zone->id_bigint);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=$area1->id_bigint&recursive=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testServicesIncludeRecursiveOneLevel()
    {
        $zone = $this->createZone('zone', 'zone');
        $region1 = $this->createRegion('region1', 'region1', $zone->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', $zone->id_bigint);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=$region1->id_bigint&recursive=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testServicesIncludeRecursiveTwoLevels()
    {
        $zone = $this->createZone('zone', 'zone');
        $region1 = $this->createRegion('region1', 'region1', $zone->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', $zone->id_bigint);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=zone->id_bigint&recursive=1")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testServicesExcludeRecursiveOneLevel()
    {
        $zone = $this->createZone('zone', 'zone');
        $region1 = $this->createRegion('region1', 'region1', $zone->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', $zone->id_bigint);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=-$region1->id_bigint&recursive=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testServicesExcludeRecursiveTwoLevels()
    {
        $zone = $this->createZone('zone', 'zone');
        $region1 = $this->createRegion('region1', 'region1', $zone->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', $zone->id_bigint);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&services[]=-$zone->id_bigint&recursive=1")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    // formats
    //
    //
    public function testFormatsNone()
    {
        $format1 = $this->createFormat1();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $badId = $format1->shared_id_bigint + 1;
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=$badId")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testFormatsIncludeOneOne()
    {
        $format1 = $this->createFormat1();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=$format1->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$format1->shared_id_bigint",
                'formats' => $format1->key_string,
            ]);
    }

    public function testFormatsIncludeOneTwo()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=$format1->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$format1->shared_id_bigint,$format2->shared_id_bigint",
                'formats' => "$format1->key_string,$format2->key_string",
            ]);
    }

    public function testFormatsIncludeOneThree()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=$format2->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$format1->shared_id_bigint,$format2->shared_id_bigint",
                'formats' => "$format1->key_string,$format2->key_string",
            ]);
    }

    public function testFormatsIncludeOneFour()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint,$format3->shared_id_bigint"]);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=$format2->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$format1->shared_id_bigint,$format2->shared_id_bigint,$format3->shared_id_bigint",
                'formats' => "$format1->key_string,$format2->key_string,$format3->key_string",
            ]);
    }

    public function testFormatsExcludeOneOne()
    {
        $format1 = $this->createFormat1();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => '']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=-$format1->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting2->id_bigint),
                'format_shared_id_list' => '',
                'formats' => '',
            ]);
    }

    public function testFormatsExcludeOneTwo()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => '']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=-$format1->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting2->id_bigint),
                'format_shared_id_list' => '',
                'formats' => '',
            ]);
    }

    public function testFormatsExcludeOneThree()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => '']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=-$format2->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting2->id_bigint),
                'format_shared_id_list' => '',
                'formats' => '',
            ]);
    }

    public function testFormatsExcludeOneFour()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $format3 = $this->createFormat3();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint,$format3->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => '']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=-$format2->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting2->id_bigint),
                'format_shared_id_list' => '',
                'formats' => '',
            ]);
    }

    public function testFormatsIncludeTwoAnd()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting3 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats[]=$format1->shared_id_bigint&formats[]=$format2->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$format1->shared_id_bigint,$format2->shared_id_bigint",
                'formats' => "$format1->key_string,$format2->key_string",
            ]);
    }

    public function testFormatsIncludeTwoOr()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting3 = $this->createMeeting();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&formats[]=$format1->shared_id_bigint&formats[]=$format2->shared_id_bigint&formats_comparison_operator=OR")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals("$format1->shared_id_bigint,$format2->shared_id_bigint", $meeting['format_shared_id_list']);
        $this->assertEquals("$format1->key_string,$format2->key_string", $meeting['formats']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertEquals("$format1->shared_id_bigint", $meeting['format_shared_id_list']);
        $this->assertEquals("$format1->key_string", $meeting['formats']);
    }

    public function testFormatsExcludeTwo()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$format2->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting3 = $this->createMeeting(['formats' => '']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&formats[]=-$format1->shared_id_bigint&formats[]=-$format2->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting3->id_bigint),
                'format_shared_id_list' => '',
                'formats' => '',
            ]);
    }

    public function testFormatsOpenAtBeginning()
    {
        $openFormat = Format::query()->where('shared_id_bigint', 17)->first();
        $format1 = $this->createFormat1();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$openFormat->shared_id_bigint"]);
        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$openFormat->shared_id_bigint,$format1->shared_id_bigint",
                'formats' => "$openFormat->key_string,$format1->key_string",
            ]);
    }

    public function testFormatsClosedAtBeginning()
    {
        $closedFormat = Format::query()->where('shared_id_bigint', 4)->first();
        $format1 = $this->createFormat1();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint,$closedFormat->shared_id_bigint"]);
        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'format_shared_id_list' => "$closedFormat->shared_id_bigint,$format1->shared_id_bigint",
                'formats' => "$closedFormat->key_string,$format1->key_string",
            ]);
    }

    // meeting_key/meeting_key_value
    //
    //
    public function testMeetingKeyValueMissingValue()
    {
        $meeting1 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testMeetingKeyValueIdBigint()
    {
        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=id_bigint&meeting_key_value=$meeting1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testMeetingKeyValueWeekdayTinyInt()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 6]);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=weekday_tinyint&meeting_key_value=7")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['weekday_tinyint' => '7']);
    }

    public function testMeetingKeyValueIntFields()
    {
        $fieldNames = ['service_body_bigint', 'venue_type'];
        foreach ($fieldNames as $fieldName) {
            $fieldValue = self::$mainFieldDefaults[$fieldName] + 1;
            $meeting1 = $this->createMeeting([$fieldName => $fieldValue]);
            $meeting2 = $this->createMeeting();

            $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=$fieldName&meeting_key_value=$fieldValue")
                ->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment([$fieldName => strval($fieldValue)]);
        }
    }

    public function testMeetingKeyValueWorldIdMixed()
    {
        $meeting1 = $this->createMeeting(['worldid_mixed' => 'testvalue']);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=worldid_mixed&meeting_key_value=testvalue")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['worldid_mixed' => 'testvalue']);
    }

    public function testMeetingKeyValueTimeFieldsValid()
    {
        $fieldNames = ['start_time', 'duration_time'];
        foreach ($fieldNames as $fieldName) {
            $meeting1 = $this->createMeeting([$fieldName => '02:00']);
            $meeting2 = $this->createMeeting();

            $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=$fieldName&meeting_key_value=02:00:00")
                ->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment([$fieldName => '02:00:00']);
        }
    }

    public function testMeetingKeyValueTimeFieldsInvalid()
    {
        $fieldNames = ['start_time', 'duration_time'];
        foreach ($fieldNames as $fieldName) {
            $meeting1 = $this->createMeeting([$fieldName => '02:00']);
            $meeting2 = $this->createMeeting();

            $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=$fieldName&meeting_key_value=blah")
                ->assertStatus(200)
                ->assertJsonCount(0);
        }
    }

    public function testMeetingKeyValueTimeZone()
    {
        $meeting1 = $this->createMeeting(['time_zone' => 'testvalue']);
        $meeting2 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=time_zone&meeting_key_value=testvalue")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['time_zone' => 'testvalue']);
    }

    public function testMeetingKeyValueInvalidFloatFields()
    {
        $fieldNames = ['longitude', 'latitude'];
        foreach ($fieldNames as $fieldName) {
            $meeting1 = $this->createMeeting([$fieldName => -1.2345]);

            $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=$fieldName&meeting_key_value=-1.2345")
                ->assertStatus(200)
                ->assertJsonCount(0);
        }
    }

    public function testMeetingKeyValueFormats()
    {
        $meeting1 = $this->createMeeting(['formats' => '4']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=formats&meeting_key_value=4")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testMeetingKeyValueDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $meeting1 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'testvalue']);
                $meeting2 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'badtestvalue']);
                $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=$fieldTemplate->key&meeting_key_value=testvalue")
                    ->assertStatus(200)
                    ->assertJsonCount(1)
                    ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
            } finally {
                Meeting::query()->whereIn('id_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingLongData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
            }
        }
    }

    public function testMeetingKeyValueLongDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $meeting1 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'testvalue']);
                $meeting2 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'badtestvalue']);
                $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=$fieldTemplate->key&meeting_key_value=testvalue")
                    ->assertStatus(200)
                    ->assertJsonCount(1)
                    ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
            } finally {
                Meeting::query()->whereIn('id_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingLongData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
            }
        }
    }

    // StartsAfter
    //
    //
    public function testStartsAfterHour()
    {
        $meeting1 = $this->createMeeting(['start_time' => '16:00']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsAfterH=15")
            ->assertStatus(200)
            ->assertJsonCount(1);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsAfterH=16")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testStartsAfterHourAndMinute()
    {
        $meeting1 = $this->createMeeting(['start_time' => '16:15']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsAfterH=16&StartsAfterM=14")
            ->assertStatus(200)
            ->assertJsonCount(1);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsAfterH=16&StartsAfterM=15")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    // StartsBefore
    //
    //
    public function testStartsBeforeHour()
    {
        $meeting1 = $this->createMeeting(['start_time' => '16:00']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsBeforeH=16")
            ->assertStatus(200)
            ->assertJsonCount(0);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsBeforeH=17")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testStartsBeforeHourAndMinute()
    {
        $meeting1 = $this->createMeeting(['start_time' => '16:15']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsBeforeH=16&StartsBeforeM=15")
            ->assertStatus(200)
            ->assertJsonCount(0);
        $this->get("/client_interface/json/?switcher=GetSearchResults&StartsBeforeH=16&StartsBeforeM=16")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    // EndsBefore
    //
    //
    public function testEndsBeforeHour()
    {
        $meeting1 = $this->createMeeting(['start_time' => '16:00', 'duration_time' => '01:00']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&EndsBeforeH=16")
            ->assertStatus(200)
            ->assertJsonCount(0);
        $this->get("/client_interface/json/?switcher=GetSearchResults&EndsBeforeH=17")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testEndsBeforeHourAndMinute()
    {
        $meeting1 = $this->createMeeting(['start_time' => '16:00', 'duration_time' => '01:15']);
        $this->get("/client_interface/json/?switcher=GetSearchResults&EndsBeforeH=17&EndsBeforeM=14")
            ->assertStatus(200)
            ->assertJsonCount(0);
        $this->get("/client_interface/json/?switcher=GetSearchResults&EndsBeforeH=17&EndsBeforeM=15")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    // geographic searches
    //
    //
    public function testGeoWidthOneMile()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.065752051707, 'longitude' => -79.793701171875]);  // on the dot
        $meeting2 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $meeting3 = $this->createMeeting(['latitude' => 36.0869819, 'longitude' => -79.9023415]);  // within 10 miles
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&geo_width=1&long_val=-79.793701171875&lat_val=36.065752051707")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('0', $meeting['distance_in_km']);
        $this->assertEquals('0', $meeting['distance_in_miles']);
    }

    public function testGeoWidthTenKm()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.065752051707, 'longitude' => -79.793701171875]);  // on the dot
        $meeting2 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $meeting3 = $this->createMeeting(['latitude' => 36.0869819, 'longitude' => -79.9023415]);  // within 10 miles
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&geo_width_km=10&long_val=-79.793701171875&lat_val=36.065752051707")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)])
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('0', $meeting['distance_in_km']);
        $this->assertEquals('0', $meeting['distance_in_miles']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertStringStartsWith('2.85', $meeting['distance_in_km']);
        $this->assertStringStartsWith('1.77', $meeting['distance_in_miles']);
    }

    public function testGeoWidthTenMiles()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.065752051707, 'longitude' => -79.793701171875]);  // on the dot
        $meeting2 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $meeting3 = $this->createMeeting(['latitude' => 36.0869819, 'longitude' => -79.9023415]);  // within 10 miles
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&geo_width=10&long_val=-79.793701171875&lat_val=36.065752051707")
            ->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint)])
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('0', $meeting['distance_in_km']);
        $this->assertEquals('0', $meeting['distance_in_miles']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertStringStartsWith('2.85', $meeting['distance_in_km']);
        $this->assertStringStartsWith('1.77', $meeting['distance_in_miles']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting3->id_bigint)->first();
        $this->assertStringStartsWith('10.03', $meeting['distance_in_km']);
        $this->assertStringStartsWith('6.23', $meeting['distance_in_miles']);
    }

    public function testGeoWidthSortByDistance()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $meeting2 = $this->createMeeting(['latitude' => 36.065752051707, 'longitude' => -79.793701171875]);  // on the dot
        $meeting3 = $this->createMeeting(['latitude' => 36.0869819, 'longitude' => -79.9023415]);  // within 10 miles
        $data = $this->get("/client_interface/json/?switcher=GetSearchResults&geo_width=10&long_val=-79.793701171875&lat_val=36.065752051707&sort_results_by_distance=1")
            ->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint)])
            ->json();
        $this->assertEquals(strval($meeting2->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
    }

    public function testGeoWidthAutoRadius()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.065752051707, 'longitude' => -79.793701171875]);  // on the dot
        $meeting2 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $meeting3 = $this->createMeeting(['latitude' => 36.0869819, 'longitude' => -79.9023415]);  // within 10 miles
        $meeting4 = $this->createMeeting(['latitude' => 36.0869819, 'longitude' => -79.9023415]);  // within 10 miles
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&geo_width=-3&long_val=-79.793701171875&lat_val=36.065752051707")
            ->assertStatus(200)
            ->assertJsonCount(4)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint)])
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('0', $meeting['distance_in_km']);
        $this->assertEquals('0', $meeting['distance_in_miles']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertStringStartsWith('2.85', $meeting['distance_in_km']);
        $this->assertStringStartsWith('1.77', $meeting['distance_in_miles']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting3->id_bigint)->first();
        $this->assertStringStartsWith('10.03', $meeting['distance_in_km']);
        $this->assertStringStartsWith('6.23', $meeting['distance_in_miles']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting4->id_bigint)->first();
        $this->assertStringStartsWith('10.03', $meeting['distance_in_km']);
        $this->assertStringStartsWith('6.23', $meeting['distance_in_miles']);
    }

    public function testHasDistanceCalculationFromDataFieldKeyMiles()
    {
        $meeting2 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=id_bigint,distance_in_miles&long_val=-79.793701171875&lat_val=36.065752051707")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)])
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertStringStartsWith('1.77', $meeting['distance_in_miles']);
    }

    public function testHasDistanceCalculationFromDataFieldKeyKm()
    {
        $meeting2 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);  // within 10 km
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=id_bigint,distance_in_km&long_val=-79.793701171875&lat_val=36.065752051707")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)])
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertStringStartsWith('2.85', $meeting['distance_in_km']);
    }

    public function testHasOnlyLongVal()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&long_val=-79.8240715")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testHasOnlyLatVal()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&lat_val=36.0733691")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testHasEmptyGeoWidth()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&geo_width=&long_val=-79.8240715&lat_val=36.0733691")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testHasEmptyGeoWidthKm()
    {
        $meeting1 = $this->createMeeting(['latitude' => 36.0733691, 'longitude' => -79.8240715]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&geo_width_km=long_val=-79.8240715&lat_val=36.0733691")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    // SearchString
    //
    //
    public function testSearchStringMeetingIds()
    {
        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();
        $meeting3 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&SearchString=$meeting1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&SearchString=$meeting1->id_bigint,$meeting2->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testSearchStringMatchesDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $meeting1 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'this test is blah']);
                $meeting2 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'this should not match']);
                // MySQL full text searches do not work against uncommitted data, because the full text
                // index has not yet been updated. We commit here, and then are very careful to clean up all
                // data in the finally block
                DB::commit();
                $this->get("/client_interface/json/?switcher=GetSearchResults&SearchString=blah%20test")
                    ->assertStatus(200)
                    ->assertJsonCount(1)
                    ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
            } finally {
                Meeting::query()->whereIn('id_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingLongData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
            }
        }
    }

    public function testSearchStringMatchesLongDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $meeting1 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'this test is blah']);
                $meeting2 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'this should not match']);
                // MySQL full text searches do not work against uncommitted data, because the full text
                // index has not yet been updated. We commit here, and then are very careful to clean up all
                // data in the finally block
                DB::commit();
                $this->get("/client_interface/json/?switcher=GetSearchResults&SearchString=blah%20test")
                    ->assertStatus(200)
                    ->assertJsonCount(1)
                    ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
            } finally {
                Meeting::query()->whereIn('id_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
                MeetingLongData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
            }
        }
    }

    // advanced_published
    //
    //
    public function testAdvancedPublishedOnlyUnpublished()
    {
        $meeting1 = $this->createMeeting(['published' => 1]);
        $meeting2 = $this->createMeeting(['published' => 0]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&advanced_published=-1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    public function testAdvancedPublishedOnlyPublished()
    {
        $meeting1 = $this->createMeeting(['published' => 1]);
        $meeting2 = $this->createMeeting(['published' => 0]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&advanced_published=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testAdvancedPublishedBoth()
    {
        $meeting1 = $this->createMeeting(['published' => 1]);
        $meeting2 = $this->createMeeting(['published' => 0]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&advanced_published=0")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    // sort_key
    //
    //
    public function testDefaultSortKeyWeekday()
    {
        LegacyConfig::set('default_sort_key', 'weekday');
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'a']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'z']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_municipality' => 'z']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_municipality' => 'a']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_municipality' => 'z']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_municipality' => 'a']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=weekday")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting4->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[5]['id_bigint']);
    }

    public function testSortKeyWeekday()
    {
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'a']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'z']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_municipality' => 'z']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_municipality' => 'a']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_municipality' => 'z']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_municipality' => 'a']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=weekday")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting4->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[5]['id_bigint']);
    }

    public function testSortKeyTime()
    {
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'a']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'z']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_municipality' => 'a']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_municipality' => 'z']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_municipality' => 'z']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_municipality' => 'a']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=time")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting4->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[5]['id_bigint']);
    }

    public function testSortKeyTown()
    {
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'f']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_municipality' => 'e']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_municipality' => 'd']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_municipality' => 'a', 'location_city_subsection' => 'x']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_municipality' => 'a', 'location_city_subsection' => 'y']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_municipality' => 'a', 'location_city_subsection' => 'z']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=town")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting4->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[5]['id_bigint']);
    }

    public function testSortKeyState()
    {
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_province' => 'a']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_province' => 'd']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_province' => 'f']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_province' => 'e']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_province' => 'b']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_province' => 'c']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=state")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting4->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[5]['id_bigint']);
    }

    public function testSortKeyWeekdayState()
    {
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_province' => 'a']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_province' => 'd']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_province' => 'f']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_province' => 'e']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_province' => 'b']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_province' => 'c']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=weekday_state")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting4->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[5]['id_bigint']);
    }

    public function testSortKeyInvalid()
    {
        $meeting1 = $this->createMeeting(['lang_enum' => 'a', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_province' => 'a']);
        $meeting2 = $this->createMeeting(['lang_enum' => 'b', 'weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['location_province' => 'd']);
        $meeting3 = $this->createMeeting(['lang_enum' => 'c', 'weekday_tinyint' => 1, 'start_time' => '19:00:00'], ['location_province' => 'f']);
        $meeting4 = $this->createMeeting(['lang_enum' => 'd', 'weekday_tinyint' => 1, 'start_time' => '09:00:00'], ['location_province' => 'e']);
        $meeting5 = $this->createMeeting(['lang_enum' => 'e', 'weekday_tinyint' => 2, 'start_time' => '23:59:59'], ['location_province' => 'b']);
        $meeting6 = $this->createMeeting(['lang_enum' => 'f', 'weekday_tinyint' => 2, 'start_time' => '01:00:00'], ['location_province' => 'c']);


        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_key=asdf")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting2->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting4->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[5]['id_bigint']);
    }

    // sort_keys
    //
    //
    public function testSortKeysIntFields()
    {
        $fieldNames = ['weekday_tinyint', 'service_body_bigint', 'venue_type'];
        foreach ($fieldNames as $fieldName) {
            try {
                $meeting3 = $this->createMeeting([$fieldName => 3]);
                $meeting2 = $this->createMeeting([$fieldName => 2]);
                $meeting1 = $this->createMeeting([$fieldName => 1]);

                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=$fieldName")
                    ->assertStatus(200)
                    ->assertJsonCount(3)
                    ->json());

                $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
                $this->assertEquals(strval($meeting2->id_bigint), $data[1]['id_bigint']);
                $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testSortKeysStringFields()
    {
        $fieldNames = ['worldid_mixed', 'time_zone'];
        foreach ($fieldNames as $fieldName) {
            try {
                $meeting3 = $this->createMeeting([$fieldName => 'c']);
                $meeting2 = $this->createMeeting([$fieldName => 'b']);
                $meeting1 = $this->createMeeting([$fieldName => 'a']);

                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=$fieldName")
                    ->assertStatus(200)
                    ->assertJsonCount(3)
                    ->json());

                $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
                $this->assertEquals(strval($meeting2->id_bigint), $data[1]['id_bigint']);
                $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testSortKeysTimeFields()
    {
        $fieldNames = ['start_time', 'duration_time'];
        foreach ($fieldNames as $fieldName) {
            try {
                $meeting3 = $this->createMeeting([$fieldName => '13:00']);
                $meeting2 = $this->createMeeting([$fieldName => '12:00']);
                $meeting1 = $this->createMeeting([$fieldName => '01:00']);

                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=$fieldName")
                    ->assertStatus(200)
                    ->assertJsonCount(3)
                    ->json());

                $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
                $this->assertEquals(strval($meeting2->id_bigint), $data[1]['id_bigint']);
                $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testSortKeysDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $meeting3 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'c']);
                $meeting2 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'b']);
                $meeting1 = $this->createMeeting(dataFields: [$fieldTemplate->key => 'a']);
                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=$fieldTemplate->key")
                    ->assertStatus(200)
                    ->assertJsonCount(3)
                    ->json());
                $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
                $this->assertEquals(strval($meeting2->id_bigint), $data[1]['id_bigint']);
                $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
            } finally {
                Meeting::query()->delete();
                MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
                MeetingLongData::query()->whereNot('meetingid_bigint', 0)->delete();
            }
        }
    }

    public function testSortKeysLongDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $meeting3 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'c']);
                $meeting2 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'b']);
                $meeting1 = $this->createMeeting(longDataFields: [$fieldTemplate->key => 'a']);
                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=$fieldTemplate->key")
                    ->assertStatus(200)
                    ->assertJsonCount(3)
                    ->json());
                $this->assertEquals(strval($meeting1->id_bigint), $data[0]['id_bigint']);
                $this->assertEquals(strval($meeting2->id_bigint), $data[1]['id_bigint']);
                $this->assertEquals(strval($meeting3->id_bigint), $data[2]['id_bigint']);
            } finally {
                Meeting::query()->delete();
                MeetingData::query()->whereNot('meetingid_bigint', 0)->delete();
                MeetingLongData::query()->whereNot('meetingid_bigint', 0)->delete();
            }
        }
    }

    public function testSortKeysMultipleFields()
    {
        $meeting1 = $this->createMeeting(['weekday_tinyint' => 1, 'start_time' => '19:00:00', ['meeting_name' => 'abc']]);
        $meeting2 = $this->createMeeting(['weekday_tinyint' => 1, 'start_time' => '09:00:00', ['meeting_name' => 'abc']]);
        $meeting3 = $this->createMeeting(['weekday_tinyint' => 2, 'start_time' => '23:59:59', ['meeting_name' => 'abc']]);
        $meeting4 = $this->createMeeting(['weekday_tinyint' => 2, 'start_time' => '01:00:00', ['meeting_name' => 'abc']]);
        $meeting5 = $this->createMeeting(['weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['meeting_name' => 'def']);
        $meeting6 = $this->createMeeting(['weekday_tinyint' => 3, 'start_time' => '01:00:00'], ['meeting_name' => 'abc']);

        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=weekday_tinyint,start_time,meeting_name")
            ->assertStatus(200)
            ->assertJsonCount(6)
            ->json());

        $this->assertEquals(strval($meeting2->id_bigint), $data[0]['id_bigint']);
        $this->assertEquals(strval($meeting1->id_bigint), $data[1]['id_bigint']);
        $this->assertEquals(strval($meeting4->id_bigint), $data[2]['id_bigint']);
        $this->assertEquals(strval($meeting3->id_bigint), $data[3]['id_bigint']);
        $this->assertEquals(strval($meeting6->id_bigint), $data[4]['id_bigint']);
        $this->assertEquals(strval($meeting5->id_bigint), $data[5]['id_bigint']);
    }

    // data_field_key
    //
    //
    public function testDataFieldKeyMainFields()
    {
        $meeting1 = $this->createMeeting();
        foreach (Meeting::$mainFields as $fieldName) {
            if ($fieldName == 'root_server_id' || $fieldName == 'source_id') {
                continue;
            }

            try {
                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=$fieldName")
                    ->assertStatus(200)
                    ->json());
                $keys = array_keys($data[0]);
                $this->assertEquals(1, count($keys));
                $this->assertEquals($fieldName, $keys[0]);
            } finally {
                MeetingResource::resetStaticVariables();
            }
        }
    }

    public function testDataFieldKeyFormatSharedIdList()
    {
        $meeting1 = $this->createMeeting();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=format_shared_id_list")
            ->assertStatus(200)
            ->json());
        $keys = array_keys($data[0]);
        $this->assertEquals(1, count($keys));
        $this->assertEquals('format_shared_id_list', $keys[0]);
    }

    public function testDataFieldKeyPublished()
    {
        $meeting1 = $this->createMeeting();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=published")
            ->assertStatus(200)
            ->json());
        $keys = array_keys($data[0]);
        $this->assertEquals(1, count($keys));
        $this->assertEquals('published', $keys[0]);
    }

    public function testDataFieldKeyRootServerUriWithAggregatorDisabled()
    {
        $meeting1 = $this->createMeeting();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=root_server_uri")
            ->assertStatus(200)
            ->json());
        $keys = array_keys($data[0]);
        $this->assertEquals(1, count($keys));
        $this->assertEquals('root_server_uri', $keys[0]);
    }

    public function testDataFieldKeyRootServerUriWithAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootserver()->associate($rootServer);
        $meeting1->save();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer->id&data_field_key=root_server_uri")
            ->assertStatus(200)
            ->json());
        $keys = array_keys($data[0]);
        $this->assertEquals(1, count($keys));
        $this->assertEquals('root_server_uri', $keys[0]);
        $this->assertEquals($rootServer->url, $data[0]['root_server_uri']);
    }

    public function testDataFieldKeyRootServerIdWithAggregatorDisabled()
    {
        $rootServer = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootserver()->associate($rootServer);
        $meeting1->save();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer->id&data_field_key=root_server_id")
            ->assertStatus(200)
            ->json());
        $this->assertArrayNotHasKey('root_server_id', $data[0]);
    }

    public function testDataFieldKeyRootServerIdWithAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootserver()->associate($rootServer);
        $meeting1->save();
        $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer->id&data_field_key=root_server_id")
            ->assertStatus(200)
            ->json());
        $this->assertEquals($rootServer->id, $data[0]['root_server_id']);
    }

    public function testDataFieldKeyDataFields()
    {
        $dataFieldTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        $meeting1 = $this->createMeeting();
        foreach ($dataFieldTemplates as $fieldTemplate) {
            try {
                $data = collect($this->get("/client_interface/json/?switcher=GetSearchResults&data_field_key=$fieldTemplate->key")
                    ->assertStatus(200)
                    ->json());
                $keys = array_keys($data[0]);
                $this->assertEquals(1, count($keys));
                $this->assertEquals($fieldTemplate->key, $keys[0]);
            } finally {
                MeetingResource::resetStaticVariables();
            }
        }
    }

    // page_size/page_num
    //
    //
    public function testPageSize()
    {
        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();
        $meeting3 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&page_size=1")
            ->assertStatus(200)
            ->assertJsonCount(1);
        $this->get("/client_interface/json/?switcher=GetSearchResults&page_size=2")
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function testPageNum()
    {
        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();
        $meeting3 = $this->createMeeting();
        $this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=id_bigint&page_size=1&page_num=1")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=id_bigint&page_size=1&page_num=2")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=id_bigint&page_size=1&page_num=3")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting3->id_bigint)]);
        $this->get("/client_interface/json/?switcher=GetSearchResults&sort_keys=id_bigint&page_size=1&page_num=4")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    // test visibility of sensitive fields
    //
    //
    public function testSensitiveFieldsHiddenUnauthenticated()
    {
        $meeting1 = $this->createMeeting(
            ['email_contact' => 'test'],
            [
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]
        );
        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'email_contact' => '',
                'contact_name_1' => '',
                'contact_name_2' => '',
                'contact_phone_1' => '',
                'contact_phone_2' => '',
                'contact_email_1' => '',
                'contact_email_2' => '',
        ]);
    }

    public function testSensitiveFieldsVisibleServerAdmin()
    {
        $user = $this->createServerAdminUser();
        $meeting1 = $this->createMeeting(
            ['email_contact' => 'test'],
            [
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]
        );
        $this->actingAs($user)
            ->withSession([
                'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
            ])
            ->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment([
                'id_bigint' => strval($meeting1->id_bigint),
                'email_contact' => 'test',
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]);
    }

    public function testSensitiveFieldsServiceBodyAdminPrincipal()
    {
        $user = $this->createServiceBodyAdminUser();
        $region1 = $this->createRegion('region1', 'region1', 0, principalUserId: $user->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', 0);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(
            [
                'email_contact' => 'test',
                'service_body_bigint' => $area1->id_bigint,
            ],
            [
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]
        );
        $meeting2 = $this->createMeeting(
            [
                'email_contact' => 'test',
                'service_body_bigint' => $area2->id_bigint,
            ],
            [
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]
        );

        $data = collect($this->actingAs($user)
            ->withSession([
                'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
            ])
            ->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('test', $meeting['email_contact']);
        $this->assertEquals('test', $meeting['contact_name_1']);
        $this->assertEquals('test', $meeting['contact_name_2']);
        $this->assertEquals('test', $meeting['contact_phone_1']);
        $this->assertEquals('test', $meeting['contact_phone_2']);
        $this->assertEquals('test', $meeting['contact_email_1']);
        $this->assertEquals('test', $meeting['contact_email_2']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertEquals('', $meeting['email_contact']);
        $this->assertEquals('', $meeting['contact_name_1']);
        $this->assertEquals('', $meeting['contact_name_2']);
        $this->assertEquals('', $meeting['contact_phone_1']);
        $this->assertEquals('', $meeting['contact_phone_2']);
        $this->assertEquals('', $meeting['contact_email_1']);
        $this->assertEquals('', $meeting['contact_email_2']);
    }

    public function testSensitiveFieldsServiceBodyAdminEditorsString()
    {
        $user = $this->createServiceBodyAdminUser();
        $region1 = $this->createRegion('region1', 'region1', 0, assignedUserIds: [$user->id_bigint]);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', 0);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        $meeting1 = $this->createMeeting(
            [
                'email_contact' => 'test',
                'service_body_bigint' => $area1->id_bigint,
            ],
            [
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]
        );
        $meeting2 = $this->createMeeting(
            [
                'email_contact' => 'test',
                'service_body_bigint' => $area2->id_bigint,
            ],
            [
                'contact_name_1' => 'test',
                'contact_name_2' => 'test',
                'contact_phone_1' => 'test',
                'contact_phone_2' => 'test',
                'contact_email_1' => 'test',
                'contact_email_2' => 'test',
            ]
        );

        $data = collect($this->actingAs($user)
            ->withSession([
                'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => $user->id_bigint,
            ])
            ->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->json());

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting1->id_bigint)->first();
        $this->assertEquals('test', $meeting['email_contact']);
        $this->assertEquals('test', $meeting['contact_name_1']);
        $this->assertEquals('test', $meeting['contact_name_2']);
        $this->assertEquals('test', $meeting['contact_phone_1']);
        $this->assertEquals('test', $meeting['contact_phone_2']);
        $this->assertEquals('test', $meeting['contact_email_1']);
        $this->assertEquals('test', $meeting['contact_email_2']);

        $meeting = $data->filter(fn ($meeting) => $meeting['id_bigint'] == $meeting2->id_bigint)->first();
        $this->assertEquals('', $meeting['email_contact']);
        $this->assertEquals('', $meeting['contact_name_1']);
        $this->assertEquals('', $meeting['contact_name_2']);
        $this->assertEquals('', $meeting['contact_phone_1']);
        $this->assertEquals('', $meeting['contact_phone_2']);
        $this->assertEquals('', $meeting['contact_email_1']);
        $this->assertEquals('', $meeting['contact_email_2']);
    }

    public function testGetUsedFormats()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $data = $this->get("/client_interface/json/?switcher=GetSearchResults&get_used_formats")
            ->assertStatus(200)
            ->json();
        $this->assertEquals(2, count($data['meetings']));
        $this->assertEquals(1, count($data['formats']));
        $this->assertEquals(strval($format1->shared_id_bigint), $data['formats'][0]['id']);
    }

    public function testGetFormatsOnly()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $data = $this->get("/client_interface/json/?switcher=GetSearchResults&get_formats_only")
            ->assertStatus(200)
            ->json();
        $this->assertFalse(array_key_exists('meetings', $data));
        $this->assertEquals(1, count($data['formats']));
        $this->assertEquals(strval($format1->shared_id_bigint), $data['formats'][0]['id']);
    }

    public function testGetFormatsOnlyPrecedence()
    {
        $format1 = $this->createFormat1();
        $format2 = $this->createFormat2();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting2 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $data = $this->get("/client_interface/json/?switcher=GetSearchResults&get_used_formats&get_formats_only")
            ->assertStatus(200)
            ->json();
        $this->assertFalse(array_key_exists('meetings', $data));
        $this->assertEquals(1, count($data['formats']));
        $this->assertEquals(strval($format1->shared_id_bigint), $data['formats'][0]['id']);
    }

    // misc
    //
    //
    public function testNullDataValue()
    {
        $this->createMeeting([], ['location_postal_code_1' => null]);
        $data = $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->json();
        $this->assertEquals('', $data[0]['location_postal_code_1']);
    }

    public function testDurationTimeNull()
    {
        $this->createMeeting(['duration_time' => null]);

        LegacyConfig::set('default_duration_time', 'blah');
        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment(['duration_time' => 'blah']);
    }

    public function testDurationTime0Hours()
    {
        $this->createMeeting(['duration_time' => '00:00:00']);

        LegacyConfig::set('default_duration_time', 'blah');
        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment(['duration_time' => 'blah']);
    }

    public function testDurationTime24Hours()
    {
        $this->createMeeting(['duration_time' => '24:00:00']);
        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonFragment(['duration_time' => '24:00:00']);
    }

    public function testRootServerUriWithAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer = $this->createRootServer(1);
        $meeting = $this->createMeeting();
        $meeting->rootServer()->associate($rootServer);
        $meeting->save();
        $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer->id")
            ->assertStatus(200)
            ->assertJsonFragment(['root_server_uri' => $rootServer->url]);
    }

    public function testRootServerIdWithAggregatorDisabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', false);
        $rootServer = $this->createRootServer(1);
        $meeting = $this->createMeeting();
        $meeting->rootServer()->associate($rootServer);
        $meeting->save();
        $response = $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer->id")
            ->assertStatus(200)
            ->json();
        $this->assertArrayNotHasKey('root_server_id', $response[0]);
    }

    public function testRootServerIdWithAggregatorEnabled()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer = $this->createRootServer(1);
        $meeting = $this->createMeeting();
        $meeting->rootServer()->associate($rootServer);
        $meeting->save();
        $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer->id")
            ->assertStatus(200)
            ->assertJsonFragment(['root_server_id' => $rootServer->id]);
    }

    // root server ids
    //
    //
    public function testRootServerIdsWithAggregatorDisabled()
    {
        $rootServer = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer);
        $meeting1->save();
        $badId = $rootServer->id + 1;
        $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$badId")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testRootServerIdsNone()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer);
        $meeting1->save();
        $badId = $rootServer->id + 1;
        $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$badId")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testRootServerIdsIncludeOne()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $rootServer2 = $this->createRootServer(2);
        $meeting2 = $this->createMeeting();
        $meeting2->rootServer()->associate($rootServer2);
        $meeting2->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids=$rootServer1->id")
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)]);
    }

    public function testRootServerIdsIncludeTwo()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $rootServer2 = $this->createRootServer(2);
        $meeting2 = $this->createMeeting();
        $meeting2->rootServer()->associate($rootServer2);
        $meeting2->save();

        $rootServer3 = $this->createRootServer(3);
        $meeting3 = $this->createMeeting();
        $meeting3->rootServer()->associate($rootServer3);
        $meeting3->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&root_server_ids[]=$rootServer1->id&root_server_ids[]=$rootServer2->id")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id_bigint' => strval($meeting1->id_bigint)])
            ->assertJsonFragment(['id_bigint' => strval($meeting2->id_bigint)]);
    }

    // aggregator mode required filters
    //
    //
    public function testAggregatorModeRequiredFiltersNone()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testAggregatorModeRequiredFiltersMeetingIds()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_ids=$meeting1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testAggregatorModeRequiredFiltersServices()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting(['service_body_bigint' => 1]);
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&services=1")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testAggregatorModeRequiredFiltersFormats()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $format1 = $this->createFormat1();
        $meeting1 = $this->createMeeting(['formats' => "$format1->shared_id_bigint"]);
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&formats=$format1->shared_id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testAggregatorModeRequiredFiltersMeetingKeyAndMeetingKeyValue()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&meeting_key=id_bigint&meeting_key_value=$meeting1->id_bigint")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testAggregatorModeRequiredFiltersGeo()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting(['latitude' => 36.065752051707, 'longitude' => -79.793701171875]);
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&lat_val=$meeting1->latitude&long_val=$meeting1->longitude&geo_width=1")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testAggregatorModeRequiredFiltersPageSizePageNum()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);

        $rootServer1 = $this->createRootServer(1);
        $meeting1 = $this->createMeeting();
        $meeting1->rootServer()->associate($rootServer1);
        $meeting1->save();

        $this->get("/client_interface/json/?switcher=GetSearchResults&page_size=1&page_num=1")
            ->assertStatus(200)
            ->assertJsonCount(1);
    }
}
