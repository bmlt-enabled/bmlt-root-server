<?php

namespace Tests\Feature\Admin;

use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;

use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteMeeting()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $meeting = $this->createMeeting(
            dataFields: ['location_street' => '813 Darby St'],
            longDataFields: ['bus_lines' => 'some very long text']
        );

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/$meeting->id_bigint")
            ->assertStatus(204);

        $this->assertFalse(Meeting::query()->where('id_bigint', $meeting->id_bigint)->exists());
        $this->assertFalse(MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->exists());
        $this->assertFalse(MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->exists());
    }
}
