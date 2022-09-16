<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetFieldValuesTest extends TestCase
{
    use RefreshDatabase;

    private function createMeeting($fieldName, $fieldValue)
    {
        $fields = array_merge(
            ['service_body_bigint' => 1],
            [$fieldName => $fieldValue],
        );
        return Meeting::create($fields);
    }

    private function createMeetingWithData($fieldName, $fieldValue)
    {
        $field = MeetingData::query()
            ->where('key', $fieldName)
            ->where('meetingid_bigint', 0)
            ->first();

        $meeting = Meeting::create(['service_body_bigint' => 1]);

        MeetingData::create([
            'meetingid_bigint' => $meeting->id_bigint,
            'key' => $field->key,
            'field_prompt' => $field->field_prompt,
            'lang_enum' => 'en',
            'data_string' => $fieldValue,
            'visibility' => 0,
        ]);

        return $meeting;
    }

    private function createMeetingWithLongData($fieldName, $fieldValue)
    {
        $field = MeetingData::query()
            ->where('key', $fieldName)
            ->where('meetingid_bigint', 0)
            ->first();

        $meeting = Meeting::create(['service_body_bigint' => 1]);

        MeetingLongData::create([
            'meetingid_bigint' => $meeting->id_bigint,
            'key' => $field->key,
            'field_prompt' => $field->field_prompt,
            'lang_enum' => 'en',
            'data_blob' => $fieldValue,
            'visibility' => 0,
        ]);

        return $meeting;
    }

    public function testXml()
    {
        $this->get('/client_interface/xml/?switcher=GetFieldValues')
            ->assertStatus(404);
    }

    public function testJsonp()
    {
        $response = $this->get('/client_interface/jsonp/?switcher=GetFieldValues&callback=asdf&meeting_key=meeting_name')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');
        $this->assertEquals('/**/asdf([]);', $response->content());
    }

    public function testNoMeetingKey()
    {
        $this->get('/client_interface/json/?switcher=GetFieldValues')
            ->assertStatus(400);
    }

    public function testBadMeetingKey()
    {
        $this->get('/client_interface/json/?switcher=GetFieldValues&meeting_key=asdf')
            ->assertStatus(400);
    }

    public function testStringMainFields()
    {
        $mainFields = ['worldid_mixed', 'time_zone', 'lang_enum', 'formats'];

        foreach ($mainFields as $fieldName) {
            try {
                $meeting1 = $this->createMeeting($fieldName, null);
                $meeting2 = $this->createMeeting($fieldName, 'test');
                $meeting3 = $this->createMeeting($fieldName, 'test');
                $meeting4 = $this->createMeeting($fieldName, 'test2');
                $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
                    ->assertStatus(200)
                    ->assertExactJson([
                        [$fieldName => 'NULL', 'ids' => strval($meeting1->id_bigint)],
                        [$fieldName => $meeting2->{$fieldName}, 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                        [$fieldName => $meeting4->{$fieldName}, 'ids' => strval($meeting4->id_bigint)]
                    ]);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testIntMainFields()
    {
        $mainFields = ['weekday_tinyint', 'venue_type'];

        foreach ($mainFields as $fieldName) {
            try {
                $meeting1 = $this->createMeeting($fieldName, null);
                $meeting2 = $this->createMeeting($fieldName, 1);
                $meeting3 = $this->createMeeting($fieldName, 1);
                $meeting4 = $this->createMeeting($fieldName, 2);
                $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
                    ->assertStatus(200)
                    ->assertExactJson([
                        [$fieldName => 'NULL', 'ids' => strval($meeting1->id_bigint)],
                        [$fieldName => strval($meeting2->{$fieldName}), 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                        [$fieldName => strval($meeting4->{$fieldName}), 'ids' => strval($meeting4->id_bigint)]
                    ]);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testTimeMainFields()
    {
        $mainFields = ['start_time', 'duration_time'];

        foreach ($mainFields as $fieldName) {
            try {
                $meeting1 = $this->createMeeting($fieldName, null);
                $meeting2 = $this->createMeeting($fieldName, '01:00:00');
                $meeting3 = $this->createMeeting($fieldName, '01:00:00');
                $meeting4 = $this->createMeeting($fieldName, '13:00:00');
                $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
                    ->assertStatus(200)
                    ->assertExactJson([
                        [$fieldName => 'NULL', 'ids' => strval($meeting1->id_bigint)],
                        [$fieldName => strval($meeting2->{$fieldName}), 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                        [$fieldName => strval($meeting4->{$fieldName}), 'ids' => strval($meeting4->id_bigint)]
                    ]);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testDoubleMainFields()
    {
        $mainFields = ['longitude', 'latitude'];

        foreach ($mainFields as $fieldName) {
            try {
                $meeting1 = $this->createMeeting($fieldName, null);
                $meeting2 = $this->createMeeting($fieldName, 1.0);
                $meeting3 = $this->createMeeting($fieldName, 1.0);
                $meeting4 = $this->createMeeting($fieldName, 2.0);
                $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
                    ->assertStatus(200)
                    ->assertExactJson([
                        [$fieldName => 'NULL', 'ids' => strval($meeting1->id_bigint)],
                        [$fieldName => strval($meeting2->{$fieldName}), 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                        [$fieldName => strval($meeting4->{$fieldName}), 'ids' => strval($meeting4->id_bigint)]
                    ]);
            } finally {
                Meeting::query()->delete();
            }
        }
    }

    public function testServiceBodyField()
    {
        $fieldName = 'service_body_bigint';
        $meeting1 = $this->createMeeting($fieldName, 1);
        $meeting2 = $this->createMeeting($fieldName, 1);
        $meeting3 = $this->createMeeting($fieldName, 2);
        $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
            ->assertStatus(200)
            ->assertExactJson([
                [$fieldName => strval($meeting1->{$fieldName}), 'ids' => implode(',', [$meeting1->id_bigint, $meeting2->id_bigint])],
                [$fieldName => strval($meeting3->{$fieldName}), 'ids' => strval($meeting3->id_bigint)]
            ]);
    }

    public function testIdBigintField()
    {
        $meeting1 = $this->createMeeting('venue_type', 1);
        $meeting2 = $this->createMeeting('venue_type', 1);
        $meeting3 = $this->createMeeting('venue_type', 1);
        $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=id_bigint")
            ->assertStatus(200)
            ->assertExactJson([
                ['id_bigint' => strval($meeting1->id_bigint), 'ids' => strval($meeting1->id_bigint)],
                ['id_bigint' => strval($meeting2->id_bigint), 'ids' => strval($meeting2->id_bigint)],
                ['id_bigint' => strval($meeting3->id_bigint), 'ids' => strval($meeting3->id_bigint)],
            ]);
    }

    public function testSpecificFormats()
    {
        $meeting1 = $this->createMeeting('formats', '1');
        $meeting2 = $this->createMeeting('formats', '2,3');
        $meeting3 = $this->createMeeting('formats', '2,3');
        $meeting4 = $this->createMeeting('formats', '3,4');
        $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=formats&specific_formats=2,3")
            ->assertStatus(200)
            ->assertExactJson([
                ['formats' => '2,3', 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                ['formats' => '3', 'ids' => strval($meeting4->id_bigint)],
            ]);
    }

    public function testSpecificFormatsAllFormats()
    {
        $meeting1 = $this->createMeeting('formats', '1');
        $meeting2 = $this->createMeeting('formats', '2,3');
        $meeting3 = $this->createMeeting('formats', '2,3');
        $meeting4 = $this->createMeeting('formats', '3,4');
        $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=formats&specific_formats=2,3&all_formats=1")
            ->assertStatus(200)
            ->assertExactJson([
                ['formats' => '2,3', 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
            ]);
    }

    public function testAllMeetingDataFields()
    {
        $fieldNames = [
            'meeting_name', 'location_text', 'location_info', 'location_street', 'location_city_subsection',
            'location_neighborhood', 'location_municipality', 'location_sub_province', 'location_province',
            'location_postal_code_1', 'location_nation', 'comments', 'train_lines', 'bus_lines',
            'phone_meeting_number', 'virtual_meeting_link', 'virtual_meeting_additional_info',
        ];

        foreach ($fieldNames as $fieldName) {
            try {
                $meeting1 = $this->createMeetingWithData($fieldName, null);
                $meeting2 = $this->createMeetingWithData($fieldName, 'test');
                $meeting3 = $this->createMeetingWithData($fieldName, 'test');
                $meeting4 = $this->createMeetingWithData($fieldName, 'test2');
                $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
                    ->assertStatus(200)
                    ->assertExactJson([
                        [$fieldName => 'NULL', 'ids' => strval($meeting1->id_bigint)],
                        [$fieldName => 'test', 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                        [$fieldName => 'test2', 'ids' => strval($meeting4->id_bigint)]
                    ]);
            } finally {
                Meeting::query()->delete();
                MeetingData::query()
                    ->whereNot('meetingid_bigint', 0)
                    ->delete();
            }
        }
    }

    public function testAllMeetingLongDataFields()
    {
        $fieldNames = [
            'meeting_name', 'location_text', 'location_info', 'location_street', 'location_city_subsection',
            'location_neighborhood', 'location_municipality', 'location_sub_province', 'location_province',
            'location_postal_code_1', 'location_nation', 'comments', 'train_lines', 'bus_lines',
            'phone_meeting_number', 'virtual_meeting_link', 'virtual_meeting_additional_info',
        ];

        foreach ($fieldNames as $fieldName) {
            try {
                $meeting1 = $this->createMeetingWithLongData($fieldName, null);
                $meeting2 = $this->createMeetingWithLongData($fieldName, 'test');
                $meeting3 = $this->createMeetingWithLongData($fieldName, 'test');
                $meeting4 = $this->createMeetingWithLongData($fieldName, 'test2');
                $data = $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")->json();
                $this->get("/client_interface/json/?switcher=GetFieldValues&meeting_key=$fieldName")
                    ->assertStatus(200)
                    ->assertExactJson([
                        [$fieldName => 'NULL', 'ids' => strval($meeting1->id_bigint)],
                        [$fieldName => 'test', 'ids' => implode(',', [$meeting2->id_bigint, $meeting3->id_bigint])],
                        [$fieldName => 'test2', 'ids' => strval($meeting4->id_bigint)]
                    ]);
            } finally {
                Meeting::query()->delete();
                MeetingLongData::query()
                    ->whereNot('meetingid_bigint', 0)
                    ->delete();
            }
        }
    }
}
