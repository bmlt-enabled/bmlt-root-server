<?php

namespace Tests\Feature\Admin;

use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Repositories\FormatRepository;
use App\Repositories\MeetingRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingCreateTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload($serviceBody, array $formats): array
    {
        return [
            'name' => 'Sunday Serenity',
            'serviceBodyId' => $serviceBody->id_bigint,
            'formatIds' => collect($formats)->map(fn ($fmt) => $fmt->shared_id_bigint)->sort()->toArray(),
            'venueType' => Meeting::VENUE_TYPE_IN_PERSON,
            'temporarilyVirtual' => false,
            'day' => 0,
            'startTime' => '20:00',
            'duration' => '01:00',
            'latitude' => 35.7079,
            'longitude' => 79.8136,
            'published' => true,
            'email' => 'test@test.com',
            'location_street' => '813 Darby St.',
            'location_municipality' => 'Raleigh',
            'location_province' => 'NC',
            'location_postal_code_1' => '27610',
            'virtual_meeting_link' => 'https://zoom.us',
            'phone_meeting_number' => '5555555555',
            'worldId' => 'nice world id',
        ];
    }

    public function testStoreMeetingAllFieldsNoNulls()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        $fieldNames = collect(Meeting::$mainFields)->merge(MeetingData::STOCK_FIELDS);

        foreach ($fieldNames as $fieldName) {
            if ($fieldName == 'id_bigint' || $fieldName == 'formats' || $fieldName == 'time_zone' || $fieldName == 'lang_enum' || $fieldName == 'root_server_id') {
                continue;
            }

            if ($fieldName == 'service_body_bigint') {
                $payload['serviceBodyId'] = $area->id_bigint;
            } elseif ($fieldName == 'venue_type') {
                $payload['venueType'] = Meeting::VENUE_TYPE_HYBRID;
            } elseif ($fieldName == 'weekday_tinyint') {
                $payload['day'] = 6;
            } elseif ($fieldName == 'start_time') {
                $payload['startTime'] = '08:00';
            } elseif ($fieldName == 'duration_time') {
                $payload['duration'] = '01:30';
            } elseif ($fieldName == 'published') {
                $payload['published'] = false;
            } elseif ($fieldName == 'email_contact') {
                $payload['email'] = 'test2@test2.com';
            } elseif ($fieldName == 'worldid_mixed') {
                $payload['worldId'] = 'test worldid!';
            } elseif ($fieldName == 'meeting_name') {
                $payload['name'] = 'test meeting name';
            } elseif ($fieldName == 'latitude') {
                $payload['latitude'] = 45.0;
            } elseif ($fieldName == 'longitude') {
                $payload['longitude'] = 45.0;
            } else {
                $payload[$fieldName] = "$fieldName test test test";
            }
        }

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        foreach ($fieldNames as $fieldName) {
            if ($fieldName == 'id_bigint' || $fieldName == 'formats' || $fieldName == 'time_zone' || $fieldName == 'lang_enum' || $fieldName == 'root_server_id') {
                continue;
            }

            if ($fieldName == 'service_body_bigint') {
                $fieldName = 'serviceBodyId';
            } elseif ($fieldName == 'venue_type') {
                $fieldName = 'venueType';
            } elseif ($fieldName == 'weekday_tinyint') {
                $fieldName = 'day';
            } elseif ($fieldName == 'start_time') {
                $fieldName = 'startTime';
            } elseif ($fieldName == 'duration_time') {
                $fieldName = 'duration';
            } elseif ($fieldName == 'email_contact') {
                $fieldName = 'email';
            } elseif ($fieldName == 'worldid_mixed') {
                $fieldName = 'worldId';
            } elseif ($fieldName == 'meeting_name') {
                $fieldName = 'name';
            }

            $expectedValue = $payload[$fieldName];
            $actualValue = $data[$fieldName];

            $this->assertEquals($expectedValue, $actualValue);
        }
    }

    public function testStoreMeetingAllFieldsAllNulls()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        $fieldNames = collect(Meeting::$mainFields)->merge(MeetingData::STOCK_FIELDS);

        $nonNullableFields = [
            'id_bigint',
            'formats',
            'time_zone',
            'lang_enum',
            'service_body_bigint',
            'venue_type',
            'weekday_tinyint',
            'start_time',
            'duration_time',
            'published',
            'latitude',
            'longitude',
            'meeting_name',
            'location_street',
            'location_municipality',
            'location_province',
        ];

        foreach ($fieldNames as $fieldName) {
            if (in_array($fieldName, $nonNullableFields) || $fieldName == 'root_server_id') {
                continue;
            }

            if ($fieldName == 'email_contact') {
                $payload['email'] = null;
            } elseif ($fieldName == 'worldid_mixed') {
                $payload['worldId'] = null;
            } else {
                $payload[$fieldName] = null;
            }
        }

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        foreach ($fieldNames as $fieldName) {
            if (in_array($fieldName, $nonNullableFields) || $fieldName == 'root_server_id') {
                continue;
            }

            if ($fieldName == 'email_contact') {
                $fieldName = 'email';
            } elseif ($fieldName == 'worldid_mixed') {
                $fieldName = 'worldId';
            }

            $actualValue = $data[$fieldName];

            $this->assertNull($actualValue);
        }
    }

    public function testStoreMeetingInPersonCheckFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_IN_PERSON;

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        $formatRepository = new FormatRepository();
        $virtualFormat = $formatRepository->getVirtualFormat();
        $hybridFormat = $formatRepository->getHybridFormat();
        $temporarilyClosedFormat = $formatRepository->getTemporarilyClosedFormat();

        $this->assertNotContains($virtualFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($hybridFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($temporarilyClosedFormat->shared_id_bigint, $data['formatIds']);
        $this->assertFalse($data['temporarilyVirtual']);

        $meeting = Meeting::find($data['id']);
        $this->assertNotContains(strval($virtualFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($hybridFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($temporarilyClosedFormat->shared_id_bigint), explode(',', $meeting->formats));
    }

    public function testStoreMeetingInPersonTemporarilyVirtualCheckFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_IN_PERSON;
        $payload['temporarilyVirtual'] = true;  // should have no impact on hybrid meeting

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        $formatRepository = new FormatRepository();
        $virtualFormat = $formatRepository->getVirtualFormat();
        $hybridFormat = $formatRepository->getHybridFormat();
        $temporarilyClosedFormat = $formatRepository->getTemporarilyClosedFormat();

        $this->assertNotContains($virtualFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($hybridFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($temporarilyClosedFormat->shared_id_bigint, $data['formatIds']);
        $this->assertFalse($data['temporarilyVirtual']);

        $meeting = Meeting::find($data['id']);
        $this->assertNotContains(strval($virtualFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($hybridFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($temporarilyClosedFormat->shared_id_bigint), explode(',', $meeting->formats));
    }

    public function testStoreMeetingVirtualNotTemporaryCheckFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        $formatRepository = new FormatRepository();
        $virtualFormat = $formatRepository->getVirtualFormat();
        $hybridFormat = $formatRepository->getHybridFormat();
        $temporarilyClosedFormat = $formatRepository->getTemporarilyClosedFormat();

        $this->assertNotContains($virtualFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($hybridFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($temporarilyClosedFormat->shared_id_bigint, $data['formatIds']);
        $this->assertFalse($data['temporarilyVirtual']);

        $meeting = Meeting::find($data['id']);
        $this->assertContains(strval($virtualFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($hybridFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($temporarilyClosedFormat->shared_id_bigint), explode(',', $meeting->formats));
    }

    public function testStoreMeetingVirtualButTemporaryCheckFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $payload['temporarilyVirtual'] = true;

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        $formatRepository = new FormatRepository();
        $virtualFormat = $formatRepository->getVirtualFormat();
        $hybridFormat = $formatRepository->getHybridFormat();
        $temporarilyClosedFormat = $formatRepository->getTemporarilyClosedFormat();

        $this->assertNotContains($virtualFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($hybridFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($temporarilyClosedFormat->shared_id_bigint, $data['formatIds']);
        $this->assertTrue($data['temporarilyVirtual']);

        $meeting = Meeting::find($data['id']);
        $this->assertContains(strval($virtualFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($hybridFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertContains(strval($temporarilyClosedFormat->shared_id_bigint), explode(',', $meeting->formats));
    }

    public function testStoreMeetingHybridCheckFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_HYBRID;

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        $formatRepository = new FormatRepository();
        $virtualFormat = $formatRepository->getVirtualFormat();
        $hybridFormat = $formatRepository->getHybridFormat();
        $temporarilyClosedFormat = $formatRepository->getTemporarilyClosedFormat();

        $this->assertNotContains($virtualFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($hybridFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($temporarilyClosedFormat->shared_id_bigint, $data['formatIds']);
        $this->assertFalse($data['temporarilyVirtual']);

        $meeting = Meeting::find($data['id']);
        $this->assertNotContains(strval($virtualFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertContains(strval($hybridFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($temporarilyClosedFormat->shared_id_bigint), explode(',', $meeting->formats));
    }

    public function testStoreMeetingHybridTemporarilyVirtualCheckFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_HYBRID;
        $payload['temporarilyVirtual'] = true;  // should have no impact on hybrid meeting

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201)
            ->json();

        $formatRepository = new FormatRepository();
        $virtualFormat = $formatRepository->getVirtualFormat();
        $hybridFormat = $formatRepository->getHybridFormat();
        $temporarilyClosedFormat = $formatRepository->getTemporarilyClosedFormat();

        $this->assertNotContains($virtualFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($hybridFormat->shared_id_bigint, $data['formatIds']);
        $this->assertNotContains($temporarilyClosedFormat->shared_id_bigint, $data['formatIds']);
        $this->assertFalse($data['temporarilyVirtual']);

        $meeting = Meeting::find($data['id']);
        $this->assertNotContains(strval($virtualFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertContains(strval($hybridFormat->shared_id_bigint), explode(',', $meeting->formats));
        $this->assertNotContains(strval($temporarilyClosedFormat->shared_id_bigint), explode(',', $meeting->formats));
    }

    public function testStoreMeetingValidateServiceBodyId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['serviceBodyId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['serviceBodyId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be an invalid service body
        $payload['serviceBodyId'] = $area->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be a valid service body
        $payload['serviceBodyId'] = $area->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateFormatIds()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        $formatRepository = new FormatRepository();
        $virtualFormatId = $formatRepository->getVirtualFormat()->shared_id_bigint;
        $temporarilyClosedId = $formatRepository->getTemporarilyClosedFormat()->shared_id_bigint;
        $hybridFormatId = $formatRepository->getHybridFormat()->shared_id_bigint;

        // it is required
        unset($payload['formatIds']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['formatIds'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't contain a special format id
        foreach ([$virtualFormatId, $temporarilyClosedId, $hybridFormatId] as $formatId) {
            $payload['formatIds'] = [$formatId];
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);
        }

        // it can be an empty list
        $payload['formatIds'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can be a valid service body
        $payload['formatIds'] = [$format->shared_id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateVenueType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['venueType']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['venueType'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be an invalid value
        $payload['venueType'] = 9999;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be a valid value
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateTemporarilyVirtual()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it can't be null
        $payload['temporarilyVirtual'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach (['blah', 9999] as $published) {
            $payload['temporarilyVirtual'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([true, false, 0, 1, '0', '1'] as $published) {
            $payload['temporarilyVirtual'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }

        // it is not required
        unset($payload['temporarilyVirtual']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateDay()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['day']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['day'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be less than 0
        $payload['day'] = -1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be greater than 6
        $payload['day'] = 7;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be a valid value
        foreach ([0,1,2,3,4,5,6] as $day) {
            $payload['day'] = $day;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateStartTime()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['startTime']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['startTime'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be an invalid value
        $payload['startTime'] = '08:00:00';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be valid values
        foreach (['00:00', '23:59'] as $startTime) {
            $payload['startTime'] = $startTime;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateDuration()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['duration']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['duration'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be an invalid value
        $payload['duration'] = '08:00:00';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be valid values
        foreach (['00:00', '23:59'] as $duration) {
            $payload['duration'] = $duration;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateLatitude()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['latitude']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['latitude'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach ([-90.01, 90.01] as $latitude) {
            $payload['latitude'] = $latitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([-90, 0, 90] as $latitude) {
            $payload['latitude'] = $latitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateLongitude()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['longitude']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['longitude'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach ([-180.01, 180.01] as $longitude) {
            $payload['longitude'] = $longitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([-180.0, 0, 180] as $longitude) {
            $payload['longitude'] = $longitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidatePublished()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['published']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['published'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach (['blah', 9999] as $published) {
            $payload['published'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([true, false, 0, 1, '0', '1'] as $published) {
            $payload['published'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it can't be an invalid email
        $payload['email'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be longer than be 255 characters
        $payload['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . 'z.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be 255 characters
        $payload['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . '.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can be null
        $payload['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it is not required
        unset($payload['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it can't be more than 30 chars
        $payload['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be 30 characters
        $payload['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it is not required
        unset($payload['worldId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can be null
        $payload['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        // it is required
        unset($payload['name']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be null
        $payload['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can't be more than 128 chars
        $payload['name'] = str_repeat('t', 129);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be 128 characters
        $payload['name'] = str_repeat('t', 128);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateDataFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();

        $meetingRepository = new MeetingRepository();
        foreach ($meetingRepository->getDataTemplates() as $template) {
            if ($template->key == 'meeting_name') {
                continue;
            }

            $payload = $this->validPayload($area, [$format]);

            if (!in_array($template->key, ['location_street', 'location_municipality', 'location_province', 'location_postal_code_1', 'virtual_meeting_link', 'phone_meeting_number'])) {
                // it can be null
                $payload[$template->key] = null;
                $this->withHeader('Authorization', "Bearer $token")
                    ->post('/api/v1/meetings', $payload)
                    ->assertStatus(201);

                // it is not required
                unset($payload[$template->key]);
                $this->withHeader('Authorization', "Bearer $token")
                    ->post('/api/v1/meetings', $payload)
                    ->assertStatus(201);
            }

            // it can't be more than 512 chars
            $payload[$template->key] = str_repeat('t', 513);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);

            // it can be 512 characters
            $payload[$template->key] = str_repeat('t', 512);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateInPersonStreet()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        foreach ([Meeting::VENUE_TYPE_IN_PERSON, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // it is required
            unset($payload['location_street']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);

            // it can be 512
            $payload['location_street'] = '813 Darby St.';
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateInPersonCityStateZip()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        foreach ([Meeting::VENUE_TYPE_IN_PERSON, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // city and state OR zip are required
            unset($payload['location_municipality']);
            unset($payload['location_province']);
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);

            $payload['location_municipality'] = 'Raleigh';
            unset($payload['location_province']);
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);

            $payload['location_municipality'] = 'Raleigh';
            $payload['location_province'] = 'NC';
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);

            unset($payload['location_municipality']);
            unset($payload['location_province']);
            $payload['location_postal_code_1'] = '27610';
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateInPersonVirtualMeetingLinkPhoneMeetingNumber()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_IN_PERSON;

        // it is not required
        unset($payload['virtual_meeting_link']);
        unset($payload['phone_meeting_number']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can be null
        $payload['virtual_meeting_link'] = null;
        $payload['phone_meeting_number'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateVirtualVirtualMeetingLinkPhoneMeetingNumber()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        foreach ([Meeting::VENUE_TYPE_VIRTUAL, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // one or the other is required
            unset($payload['virtual_meeting_link']);
            unset($payload['phone_meeting_number']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(422);

            $payload['virtual_meeting_link'] = 'https://zoom.us';
            unset($payload['phone_meeting_number']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);

            unset($payload['virtual_meeting_link']);
            $payload['phone_meeting_number'] = '5555555555';
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/meetings', $payload)
                ->assertStatus(201);
        }
    }

    public function testStoreMeetingValidateVirtualStreetCityStateZip()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;

        // it is not required
        unset($payload['location_street']);
        unset($payload['location_municipality']);
        unset($payload['location_province']);
        unset($payload['location_postal_code_1']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can be null
        $payload['location_street'] = null;
        $payload['location_municipality'] = null;
        $payload['location_province'] = null;
        $payload['location_postal_code_1'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }

    public function testStoreMeetingValidateExtraDataField()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload = $this->validPayload($area, [$format]);

        MeetingData::create([
            'meetingid_bigint' => 0,
            'key' => 'blahblah',
            'field_prompt' => 'blahblah',
            'lang_enum' => 'en',
            'data_string' => 'blahblah',
            'visibility' => 0,
        ]);

        // it is not required
        unset($payload['blahblah']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can be null
        $payload['blahblah'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);

        // it can't be longer than 512
        $payload['blahblah'] = str_repeat('t', 513);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(422);

        // it can be 512
        $payload['blahblah'] = str_repeat('t', 512);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->assertStatus(201);
    }
}
