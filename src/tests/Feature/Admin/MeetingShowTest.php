<?php

namespace Tests\Feature\Admin;

use App\Repositories\MeetingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingShowTest extends TestCase
{
    use RefreshDatabase;

    public function testShowMeetingServiceBodyId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('area', 'area', 0);
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsInt($data['serviceBodyId']);
        $this->assertEquals($area->id_bigint, $data['serviceBodyId']);
    }

    public function testShowMeetingWorldIdNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['worldid_mixed' => 'test']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertEquals($meeting->worldid_mixed, $data['worldId']);
    }

    public function testShowMeetingWorldIdNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['worldid_mixed' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['worldId']);
    }

    public function testShowMeetingWeekdayNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['weekday_tinyint' => 0]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsInt($data['day']);
        $this->assertEquals($meeting->weekday_tinyint, $data['day']);
    }

    public function testShowMeetingWeekdayNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['weekday_tinyint' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['day']);
    }

    public function testShowMeetingVenueTypeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['venue_type' => 1]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsInt($data['venueType']);
        $this->assertEquals($meeting->venue_type, $data['venueType']);
    }

    public function testShowMeetingVenueTypeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['venue_type' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['venueType']);
    }

    public function testShowMeetingStartTimeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['start_time' => '10:00:00']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['startTime']);
        $this->assertEquals('10:00', $data['startTime']);
    }

    public function testShowMeetingStartTimeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['start_time' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['startTime']);
    }

    public function testShowMeetingDurationTimeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['duration_time' => '10:00:00']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['duration']);
        $this->assertEquals('10:00', $data['duration']);
    }

    public function testShowMeetingDurationTimeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['duration_time' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['duration']);
    }

    public function testShowMeetingTimeZoneNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['time_zone' => 'test']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['timeZone']);
        $this->assertEquals($meeting->time_zone, $data['timeZone']);
    }

    public function testShowMeetingTimeZoneNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['time_zone' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['timeZone']);
    }

    public function testShowMeetingFormatsNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['formats' => '1,2,3']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsArray($data['formatIds']);
        $this->assertEquals([1,2,3], $data['formatIds']);
    }

    public function testShowMeetingFormatsNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['formats' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsArray($data['formatIds']);
        $this->assertEquals([], $data['formatIds']);
    }

    public function testShowMeetingLongitudeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['longitude' => 1.11]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsFloat($data['longitude']);
        $this->assertEquals($meeting->longitude, $data['longitude']);
    }

    public function testShowMeetingLongitudeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['longitude' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['longitude']);
    }

    public function testShowMeetingLatitudeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['latitude' => 1.11]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsFloat($data['latitude']);
        $this->assertEquals($meeting->latitude, $data['latitude']);
    }

    public function testShowMeetingLatitudeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['latitude' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['latitude']);
    }

    public function testShowMeetingPublished()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['published' => 0]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsBool($data['published']);
        $this->assertFalse($data['published']);

        $meeting = $this->createMeeting(['published' => 1]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsBool($data['published']);
        $this->assertTrue($data['published']);

        $meeting = $this->createMeeting(['published' => 2]);  // the database allows it...
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsBool($data['published']);
        $this->assertFalse($data['published']);
    }

    public function testShowMeetingEmailNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['email_contact' => 'test']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['email']);
        $this->assertEquals($meeting->email_contact, $data['email']);
    }

    public function testShowMeetingEmailNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting(['email_contact' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['email']);
    }

    public function testShowMeetingNameNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting([], ['meeting_name' => 'test']);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['name']);
        $this->assertEquals($meeting->data->where('key', 'meeting_name')->first()->data_string, $data['name']);
    }

    public function testShowMeetingNameNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting = $this->createMeeting([], ['meeting_name' => null]);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['name']);
    }

    public function testShowMeetingDataFieldsNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meetingRepository = new MeetingRepository();
        $fieldNames = $meetingRepository->getDataTemplates()->map(fn ($t) => $t->key)->reject(fn ($n) => $n == 'meeting_name');

        foreach ($fieldNames as $fieldName) {
            $meeting = $this->createMeeting([], [$fieldName => 'test']);
            $data = $this->withHeader('Authorization', "Bearer $token")
                ->get("/api/v1/meetings/$meeting->id_bigint")
                ->assertStatus(200)
                ->json();

            $this->assertIsString($data[$fieldName]);
            $this->assertEquals($meeting->data->where('key', $fieldName)->first()->data_string, $data[$fieldName]);
        }
    }

    public function testShowMeetingDataFieldsNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meetingRepository = new MeetingRepository();
        $fieldNames = $meetingRepository->getDataTemplates()->map(fn ($t) => $t->key)->reject(fn ($n) => $n == 'meeting_name');

        foreach ($fieldNames as $fieldName) {
            $meeting = $this->createMeeting([], [$fieldName => null]);
            $data = $this->withHeader('Authorization', "Bearer $token")
                ->get("/api/v1/meetings/$meeting->id_bigint")
                ->assertStatus(200)
                ->json();

            $this->assertNull($data[$fieldName]);
        }
    }
}
