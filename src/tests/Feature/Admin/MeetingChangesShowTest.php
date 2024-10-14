<?php

namespace Tests\Feature\Admin;

use App\Models\Format;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingChangesShowTest extends TestCase
{
    use RefreshDatabase;

    public function testMeetingChangesShowCreate()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area', 'area', 0);
        $format1 = Format::query()->first();
        $format2 = Format::query()->whereNot('shared_id_bigint', $format1->shared_id_bigint)->first();
        $payload = [
            'name' => 'Sunday Serenity',
            'serviceBodyId' => $area->id_bigint,
            'formatIds' => [$format1->shared_id_bigint, $format2->shared_id_bigint],
            'venueType' => Meeting::VENUE_TYPE_IN_PERSON,
            'temporarilyVirtual' => false,
            'day' => 0,
            'startTime' => '20:00',
            'duration' => '01:00',
            'latitude' => 35.7079,
            'longitude' => 79.8136,
            'published' => true,
            'email' => 'test@test.com',
            'location_street' => str_repeat('t', 256),
            'location_municipality' => 'Raleigh',
            'location_province' => 'NC',
            'location_postal_code_1' => '27610',
            'virtual_meeting_link' => 'https://zoom.us',
            'phone_meeting_number' => '5555555555',
            'timeZone' => 'America/New_York',
            'worldId' => 'nice world id',
        ];

        $meeting = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->json();
        $meeting = Meeting::query()->where('id_bigint', $meeting['id'])->first();
        $changes = $this
            ->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/{$meeting->id_bigint}/changes")
            ->assertStatus(200)
            ->json();
        $this->assertNotEmpty($changes);

        $expectedKeys = [
            'date_int',
            'date_string',
            'change_type',
            'change_id',
            'meeting_id',
            'meeting_name',
            'user_id',
            'user_name',
            'service_body_id',
            'service_body_name',
            'meeting_exists',
            'details',
            'json_data',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $changes[0]);
        }

        $this->assertArrayHasKey('after', $changes[0]['json_data']);
        $this->assertArrayNotHasKey('before', $changes[0]['json_data']);

        print_r($changes);
        $this->assertEquals($meeting->service_body_bigint, $changes[0]['service_body_id']);
    }

    public function testMeetingChangesShowMultiple()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area', 'area', 0);
        $format1 = Format::query()->first();
        $format2 = Format::query()->whereNot('shared_id_bigint', $format1->shared_id_bigint)->first();
        $payload = [
            'name' => 'Sunday Serenity',
            'serviceBodyId' => $area->id_bigint,
            'formatIds' => [$format1->shared_id_bigint, $format2->shared_id_bigint],
            'venueType' => Meeting::VENUE_TYPE_IN_PERSON,
            'temporarilyVirtual' => false,
            'day' => 0,
            'startTime' => '20:00',
            'duration' => '01:00',
            'latitude' => 35.7079,
            'longitude' => 79.8136,
            'published' => true,
            'email' => 'test@test.com',
            'location_street' => str_repeat('t', 256),
            'location_municipality' => 'Raleigh',
            'location_province' => 'NC',
            'location_postal_code_1' => '27610',
            'virtual_meeting_link' => 'https://zoom.us',
            'phone_meeting_number' => '5555555555',
            'timeZone' => 'America/New_York',
            'worldId' => 'nice world id',
        ];

        $meeting = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->json();
        $meeting = Meeting::query()->where('id_bigint', $meeting['id'])->first();
        $payload['name'] = 'new name';
        $this
            ->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/{$meeting->id_bigint}", $payload)
            ->assertStatus(204);
        $changes = $this
            ->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/{$meeting->id_bigint}/changes")
            ->assertStatus(200)
            ->json();
        $this->assertArrayHasKey('after', $changes[1]['json_data']);
        $this->assertArrayHasKey('before', $changes[1]['json_data']);
        $this->assertEquals('Meeting Name was changed from "Sunday Serenity" to "new name".', $changes[1]['details']);
    }
}
