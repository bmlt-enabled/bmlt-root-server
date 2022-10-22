<?php

namespace Tests\Feature;

use App\Http\Resources\Query\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Models\ServiceBody;
use App\Models\User;
use App\LegacyConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use League\Csv\Reader as CsvReader;

class GetNawsExportTest extends TestCase
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

    protected function tearDown(): void
    {
        MeetingResource::resetStaticVariables();
        parent::tearDown();
    }

    public function testColumnHeaders()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")
            ->assertStatus(200)
            ->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $columnNames = ['Committee', 'CommitteeName', 'AddDate', 'AreaRegion', 'ParentName', 'ComemID', 'ContactID', 'ContactName',
            'CompanyName', 'ContactAddrID', 'ContactAddress1', 'ContactAddress2', 'ContactCity', 'ContactState', 'ContactZip', 'ContactCountry',
            'ContactPhone', 'MeetingID', 'Room', 'Closed', 'WheelChr', 'Day', 'Time', 'Language1', 'Language2', 'Language3', 'LocationId',
            'Place', 'Address', 'City', 'LocBorough', 'State', 'Zip', 'Country', 'Directions', 'Institutional', 'Format1', 'Format2', 'Format3',
            'Format4', 'Format5', 'Delete', 'LastChanged', 'Longitude', 'Latitude', 'ContactGP', 'PhoneMeetingNumber', 'VirtualMeetingLink',
            'VirtualMeetingInfo', 'TimeZone', 'bmlt_id', 'unpublished'];
        $this->assertEquals($columnNames, $reader->getHeader());
        $this->assertEquals(0, count($reader));   // zero meetings
    }

    // Test every column for one meeting with typical data.  Other cases for particular columns have additional tests later.
    public function testOneMeeting()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'worldid_mixed' => 'G042',
            'service_body_bigint' => $area1->id_bigint,
            'weekday_tinyint' => 2,
            'venue_type' => 1,
            'start_time' => '19:00:00',
            'duration_time' => '01:00:00',
            'time_zone' => 'PT',
            'formats' => '7,17,28,29,30,32,33', // CS, O, Ti, To, Tr, W, WC
            'lang_enum' => 'en',
            'longitude' => -122.3451698,
            'latitude' => 47.719048,
            'published' => 1
        ];
        $meetingDataFields = [
            'meeting_name' => 'Bottom Feeders',
            'location_text' => 'Joes Bar and Grill',
            'location_street' => '12255 Aurora Ave N',
            'location_municipality' => 'Seattle',
            'location_province' => 'WA',
            'location_postal_code_1' => '98133',
            'location_nation' => 'USA',
            'location_info' => 'Back door',
            'comments' => 'Speaker first Tuesday'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")
            ->assertStatus(200)
            ->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $this->assertEquals(1, count($reader));  // should have 1 meeting
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('G042', $row['Committee']);
        $this->assertEquals('Bottom Feeders', $row['CommitteeName']);
        $this->assertEquals('', $row['AddDate']);
        $this->assertEquals('AR123', $row['AreaRegion']);
        $this->assertEquals('Seattle Area', $row['ParentName']);
        $this->assertEquals('', $row['ComemID']);
        $this->assertEquals('', $row['ContactID']);
        $this->assertEquals('', $row['ContactName']);
        $this->assertEquals('', $row['CompanyName']);
        $this->assertEquals('', $row['ContactAddrID']);
        $this->assertEquals('', $row['ContactAddress1']);
        $this->assertEquals('', $row['ContactAddress2']);
        $this->assertEquals('', $row['ContactCity']);
        $this->assertEquals('', $row['ContactState']);
        $this->assertEquals('', $row['ContactZip']);
        $this->assertEquals('', $row['ContactCountry']);
        $this->assertEquals('', $row['ContactPhone']);
        $this->assertEquals('', $row['MeetingID']);
        $this->assertTrue($row['Room'] == 'Children under Supervision, Timer' || $row['Room'] == 'Timer, Children under Supervision');   // non-NAWS formats
        $this->assertEquals('OPEN', $row['Closed']);
        $this->assertEquals('TRUE', $row['WheelChr']);
        $this->assertEquals('Tuesday', $row['Day']);
        $this->assertEquals('1900', $row['Time']);
        $this->assertEquals('', $row['Language1']);
        $this->assertEquals('', $row['Language2']);
        $this->assertEquals('', $row['Language3']);
        $this->assertEquals('', $row['LocationId']);
        $this->assertEquals('Joes Bar and Grill', $row['Place']);
        $this->assertEquals('12255 Aurora Ave N', $row['Address']);
        $this->assertEquals('Seattle', $row['City']);
        $this->assertEquals('', $row['LocBorough']);
        $this->assertEquals('WA', $row['State']);
        $this->assertEquals('98133', $row['Zip']);
        $this->assertEquals('USA', $row['Country']);
        $this->assertEquals('Back door, Speaker first Tuesday', $row['Directions']);
        $this->assertEquals('FALSE', $row['Institutional']);
        $this->assertEquals('W', $row['Format1']);  // W (women) should come before the other formats
        // format2 and format3 should be TOP and TRAD (in either order)
        $this->assertTrue($row['Format2'] == 'TOP' && $row['Format3']== 'TRAD' || $row['Format2'] == 'TRAD' && $row['Format3']== 'TOP');
        $this->assertEquals('', $row['Format4']);
        $this->assertEquals('', $row['Format5']);
        $this->assertEquals('', $row['Delete']);
        $this->assertEquals('', $row['LastChanged']);
        $this->assertEquals('-122.3451698', $row['Longitude']);
        $this->assertEquals('47.719048', $row['Latitude']);
        $this->assertEquals('', $row['ContactGP']);
        $this->assertEquals('', $row['PhoneMeetingNumber']);
        $this->assertEquals('', $row['VirtualMeetingLink']);
        $this->assertEquals('', $row['VirtualMeetingInfo']);
        $this->assertEquals('PT', $row['TimeZone']);
        $this->assertEquals($meeting1->id_bigint, $row['bmlt_id']);
        $this->assertEquals('', $row['unpublished']);
    }

    // test that meetings in a service body hierarchy are all found
    public function testMultipleLevels()
    {
        $zone = $this->createZone('My Zone', 'A Zone of Some Kind', worldId: 'ZN42');
        $region1 = $this->createRegion('region1', 'region1', $zone->id_bigint);
        $area1 = $this->createArea('area1', 'area1', $region1->id_bigint);
        $region2 = $this->createRegion('region2', 'region2', $zone->id_bigint);
        $area2 = $this->createArea('area2', 'area2', $region2->id_bigint);
        // mix the world_id order (probably for no good reason .... anyway, the test should work no matter what order they are in)
        $meeting1 = $this->createMeeting(['worldid_mixed' => 'G004', 'service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['worldid_mixed' => 'G005', 'service_body_bigint' => $area1->id_bigint]);
        $meeting3 = $this->createMeeting(['worldid_mixed' => 'G001', 'service_body_bigint' => $area2->id_bigint]);
        $meeting4 = $this->createMeeting(['worldid_mixed' => 'G003', 'service_body_bigint' => $area2->id_bigint]);
        $meeting5 = $this->createMeeting(['worldid_mixed' => 'G002', 'service_body_bigint' => $area2->id_bigint]);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$zone->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $this->assertEquals(5, count($reader));  // should have 5 meetings
        $rows = $reader->getRecords();
        $worldIds = [];
        foreach ($rows as $offset => $row) {
            array_push($worldIds, $row['Committee']);
        }
        sort($worldIds);
        $this->assertEquals(['G001', 'G002', 'G003', 'G004', 'G005'], $worldIds);
    }

    // test a meeting with more than 5 NAWS formats -- can't include them all since the spreadsheet is limited to 5 columns for this
    public function testTooManyFormats()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => '4,7,10,28,29,30,33,34,54,55'  // C, CS, GL, Ti, To, Tr, WC, YP, VM, TC
        ];
        $meeting1 = $this->createMeeting($meetingMainFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $this->assertEquals(1, count($reader));  // should have 1 meeting
        $row = iterator_to_array($reader)[1];
        $this->assertTrue($row['Room'] == 'Children under Supervision, Timer' || $row['Room'] == 'Timer, Children under Supervision');   // non-NAWS formats
        $this->assertEquals('CLOSED', $row['Closed']);
        $this->assertEquals('TRUE', $row['WheelChr']);
        $this->assertEquals('VM', $row['Format1']);
        $this->assertEquals('TC', $row['Format2']);
        $this->assertEquals('GL', $row['Format3']);
        // format4 and format5 should be two of TOP, TRAD, Y
        // one of the formats drops off because there are too many
        $this->assertContains($row['Format4'], ['TOP', 'TRAD', 'Y']);
        $this->assertContains($row['Format5'], ['TOP', 'TRAD', 'Y']);
        $this->assertNotEquals($row['Format4'], $row['Format5']);
    }

    // Earlier tests included an explicit OPEN and an explicit CLOSED format.  Test what happens if neither is specified.
    // This is taken from the default_closed_status field in auto-config.php.  This field is not in the default auto-config,
    // and defaults to true (i.e., CLOSED meeting).
    public function testOpenClosedDefault()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => ''
        ];
        $meeting1 = $this->createMeeting($meetingMainFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('CLOSED', $row['Closed']);
    }

    public function testOpenClosedDefaultFromAutoConfig()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => ''
        ];
        $meeting1 = $this->createMeeting($meetingMainFields);
        LegacyConfig::set('default_closed_status', false);
        try {
            $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
            $reader = CsvReader::createFromString($csv);
            $reader->setHeaderOffset(0);
            $row = iterator_to_array($reader)[1];
            $this->assertEquals('OPEN', $row['Closed']);
        } finally {
            LegacyConfig::reset();
        }
    }

    // If a meeting has both Open and Closed formats, treat it as closed. Server admins shouldn't do this, but the
    // UI unfortunately does not prevent it
    public function testBothOpenAndClosed()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => '4,17'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('CLOSED', $row['Closed']);
    }

    public function testDirectionsJustLocationInfo()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meetingDataFields = ['location_info' => 'Back door'];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('Back door', $row['Directions']);
    }

    public function testDirectionsJustComments()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meetingDataFields = ['comments' => 'Speaker first Tuesday'];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('Speaker first Tuesday', $row['Directions']);
    }

    public function testVirtualMeetingTC()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => '54,55'  // VM, TC
        ];
        $meetingDataFields = [
            'virtual_meeting_link' => 'https://zoom.us/j/12345',
            'virtual_meeting_additional_info' => 'Zoom Meeting ID: 12345'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('VM', $row['Format1']);
        $this->assertEquals('TC', $row['Format2']);
        $this->assertEquals('', $row['Format3']);
        $this->assertEquals('', $row['Format4']);
        $this->assertEquals('', $row['Format5']);
        $this->assertEquals('https://zoom.us/j/12345', $row['VirtualMeetingLink']);
        $this->assertEquals('Zoom Meeting ID: 12345', $row['VirtualMeetingInfo']);
    }

    public function testPhoneMeeting()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => '54'  // VM
        ];
        $meetingDataFields = [
            'phone_meeting_number' => '206-555-1212'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('VM', $row['Format1']);
        $this->assertEquals('', $row['Format2']);
        $this->assertEquals('', $row['Format3']);
        $this->assertEquals('', $row['Format4']);
        $this->assertEquals('', $row['Format5']);
        $this->assertEquals('206-555-1212', $row['PhoneMeetingNumber']);
    }

    public function testWheelchairDefault()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meeting1 = $this->createMeeting($meetingMainFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('FALSE', $row['WheelChr']);
    }

    public function testUnpublished()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint, 'published' => 0];
        $meeting1 = $this->createMeeting($meetingMainFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('1', $row['unpublished']);
    }

    // Various tests for the City and LocBorough columns.
    // The spec for the City row is to return location_city_subsection (first choice), or location_municipality (second choice),
    // or location_neighborhood (third choice).
    // The LocBorough row is always just location_neighborhood.

    public function testCityGivenAllThreeFields()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meetingDataFields = [
            'location_city_subsection' => 'Ballard',
            'location_municipality' => 'Seattle',
            'location_neighborhood' => 'Old Town'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('Ballard', $row['City']);
        $this->assertEquals('Old Town', $row['LocBorough']);
    }

    public function testCityGivenSubsectionAndMunicipality()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meetingDataFields = [
            'location_city_subsection' => 'Ballard',
            'location_municipality' => 'Seattle'
            ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('Ballard', $row['City']);
        $this->assertEquals('', $row['LocBorough']);
    }

    public function testCityGivenSubsectionAndNeighborhood()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meetingDataFields = [
            'location_city_subsection' => 'Ballard',
            'location_neighborhood' => 'Old Town'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('Ballard', $row['City']);
        $this->assertEquals('Old Town', $row['LocBorough']);
    }

    public function testCityGivenMunicipalityAndNeighborhood()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = ['service_body_bigint' => $area1->id_bigint];
        $meetingDataFields = [
            'location_municipality' => 'Seattle',
            'location_neighborhood' => 'Old Town'
        ];
        $meeting1 = $this->createMeeting($meetingMainFields, $meetingDataFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('Seattle', $row['City']);
        $this->assertEquals('Old Town', $row['LocBorough']);
    }

    public function testLanguage()
    {
        $area1 = $this->createArea('Seattle Area', 'sort of Seattle', 0, worldId: 'AR123');
        $meetingMainFields = [
            'service_body_bigint' => $area1->id_bigint,
            'formats' => '9'  // Spanish
        ];
        $meeting1 = $this->createMeeting($meetingMainFields);
        $csv = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$area1->id_bigint")->streamedContent();
        $reader = CsvReader::createFromString($csv);
        $reader->setHeaderOffset(0);
        $row = iterator_to_array($reader)[1];
        $this->assertEquals('ES', $row['Language1']);
    }

    // the file name should be like this: BMLT_ZN42_my_zone_2022_10_20_17_20_14.csv for a zone named 'My Zone'
    public function testFileName()
    {
        $zone = $this->createZone('My Zone', 'A Zone of Some Kind', worldId: 'ZN42');
        $f = $this->get("/client_interface/csv/?switcher=GetNAWSDump&sb_id=$zone->id_bigint")
            ->assertDownload()
            ->headers->get('content-disposition');
        $this->assertEquals(1, preg_match('/^attachment; filename=BMLT_ZN42_my_zone_\d\d\d\d_\d\d_\d\d_\d\d_\d\d_\d\d.csv$/', $f));
    }
}
