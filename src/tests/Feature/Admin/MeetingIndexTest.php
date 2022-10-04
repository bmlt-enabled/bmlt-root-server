<?php

namespace Tests\Feature\Admin;

use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class MeetingIndexTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexMeetings()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('area', 'area', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $meeting1->id_bigint])
            ->assertJsonFragment(['id' => $meeting2->id_bigint]);
    }

    public function testIndexMeetingsServiceBodyFilter()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area1 = $this->createArea('area1', 'area1', 0);
        $area2 = $this->createArea('area2', 'area2', 0);
        $area3 = $this->createArea('area3', 'area3', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $meeting3 = $this->createMeeting(['service_body_bigint' => $area3->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?serviceBodyIds={$area1->id_bigint},{$area2->id_bigint}")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $meeting1->id_bigint])
            ->assertJsonFragment(['id' => $meeting2->id_bigint]);
    }

    public function testIndexMeetingsMeetingIdsFilter()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting1 = $this->createMeeting([]);
        $meeting2 = $this->createMeeting([]);
        $meeting3 = $this->createMeeting([]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?meetingIds={$meeting1->id_bigint},{$meeting2->id_bigint}")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $meeting1->id_bigint])
            ->assertJsonFragment(['id' => $meeting2->id_bigint]);
    }

    public function testIndexMeetingsDaysFilter()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting1 = $this->createMeeting(['weekday_tinyint' => 0]);
        $meeting2 = $this->createMeeting(['weekday_tinyint' => 1]);
        $meeting3 = $this->createMeeting(['weekday_tinyint' => 2]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?days=0,1")
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $meeting1->id_bigint])
            ->assertJsonFragment(['id' => $meeting2->id_bigint]);
    }

    public function testIndexMeetingsSearchStringFilter()
    {
        try {
            $user = $this->createAdminUser();
            $token = $user->createToken('test')->plainTextToken;
            $meeting1 = $this->createMeeting([], ['meeting_name' => 'Sunday Serenity']);
            $meeting2 = $this->createMeeting([], ['meeting_name' => 'Living the Program']);
            // MySQL full text searches do not work against uncommitted data, because the full text
            // index has not yet been updated. We commit here, and then are very careful to clean up all
            // data in the finally block
            DB::commit();
            $this->withHeader('Authorization', "Bearer $token")
                ->get('/api/v1/meetings?searchString=Living')
                ->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment(['id' => $meeting2->id_bigint]);
        } finally {
            $user->delete();
            Meeting::query()->whereIn('id_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
            MeetingData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
            MeetingLongData::query()->whereIn('meetingid_bigint', [$meeting1->id_bigint, $meeting2->id_bigint])->delete();
        }
    }

    public function testIndexMeetingsServiceBodyIdsValidate()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('area1', 'area1', 0);

        // can't be a string
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?serviceBodyIds={$area->id_bigint},notAnInt")
            ->assertStatus(422);

        // can't be an invalid sb id
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?serviceBodyIds={$area->id_bigint},999")
            ->assertStatus(422);

        // can be a valid sb id
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?serviceBodyIds={$area->id_bigint}")
            ->assertStatus(200);
    }

    public function testIndexMeetingsMeetingIdsValidate()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $meeting1 = $this->createMeeting();
        $meeting2 = $this->createMeeting();

        // can't be a string
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?meetingIds={$meeting1->id_bigint},notAnInt")
            ->assertStatus(422);

        // can't be an invalid meeting id
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?meetingIds={$meeting1->id_bigint},999")
            ->assertStatus(422);

        // can be a valid meeting id
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?meetingIds={$meeting1->id_bigint}")
            ->assertStatus(200);

        // or multiple
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings?meetingIds={$meeting1->id_bigint},{$meeting2->id_bigint}")
            ->assertStatus(200);
    }

    public function testIndexMeetingsDaysValidate()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        // can't be an invalid day
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings?days=0,999')
            ->assertStatus(422);

        // can be a valid day
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings?days=0')
            ->assertStatus(200);

        // or multiple
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings?days=0,1,2,3,4,5,6')
            ->assertStatus(200);
    }

    public function testIndexMeetingsSearchStringValidate()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        // can't be less than 3 characters
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings?searchString=ab')
            ->assertStatus(422);

        // can be 3 characters
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings?searchString=abc')
            ->assertStatus(200);
    }
}
