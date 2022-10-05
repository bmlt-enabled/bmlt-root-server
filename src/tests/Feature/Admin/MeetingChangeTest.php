<?php

namespace Tests\Feature\Admin;

use App\Models\Change;
use App\Models\Format;
use App\Models\Meeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

class MeetingChangeTest extends TestCase
{
    use RefreshDatabase;

    public function testNewMeetingChange()
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
            'worldId' => 'nice world id',
        ];

        $meeting = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->json();
        $meeting = Meeting::query()->where('id_bigint', $meeting['id'])->first();

        $change = Change::query()->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($meeting->service_body_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals($change->object_class_string, 'c_comdef_meeting');
        $this->assertNull($change->before_id_bigint);
        $this->assertNull($change->before_lang_enum);
        $this->assertEquals($change->after_id_bigint, $meeting->id_bigint);
        $this->assertEquals(App::currentLocale(), $change->after_lang_enum);
        $this->assertEquals($change->change_type_enum, 'comdef_change_type_new');
        $this->assertNull($change->before_object);
        $this->assertNotNull($change->after_object);
        $object = $change->after_object;
        $mainTableValues = $object['main_table_values'];
        $this->assertEquals($meeting->id_bigint, $mainTableValues['id_bigint']);
        $this->assertEquals($meeting->email_contact, $mainTableValues['email_contact']);
        $this->assertEquals($meeting->worldid_mixed, $mainTableValues['worldid_mixed']);
        $this->assertEquals($meeting->service_body_bigint, $mainTableValues['service_body_bigint']);
        $this->assertEquals($meeting->weekday_tinyint, $mainTableValues['weekday_tinyint']);
        $this->assertEquals($meeting->venue_type, $mainTableValues['venue_type']);
        $this->assertEquals($meeting->start_time, $mainTableValues['start_time']);
        $this->assertEquals($meeting->lang_enum, $mainTableValues['lang_enum']);
        $this->assertEquals($meeting->duration_time, $mainTableValues['duration_time']);
        $this->assertEquals($meeting->time_zone, $mainTableValues['time_zone']);
        $this->assertEquals($meeting->longitude, $mainTableValues['longitude']);
        $this->assertEquals($meeting->latitude, $mainTableValues['latitude']);
        $this->assertEquals($meeting->published, $mainTableValues['published']);
        $this->assertEquals($meeting->formats, $mainTableValues['formats']);
        $dataTableValues = $object['data_table_values'];
        $this->assertEquals(count($meeting->data), count($dataTableValues));
        foreach ($meeting->data as $data) {
            $dataTableValue = collect($dataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $dataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $dataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $dataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $dataTableValue['visibility']);
            $this->assertEquals($data->key, $dataTableValue['key']);
            $this->assertEquals($data->data_string, $dataTableValue['data_string']);
            $this->assertEquals($data->data_bigint, $dataTableValue['data_bigint']);
            $this->assertEquals($data->data_double, $dataTableValue['data_double']);
        }
        $longDataTableValues = $object['longdata_table_values'];
        $this->assertEquals(count($meeting->longdata), count($longDataTableValues));
        foreach ($meeting->longdata as $data) {
            $longDataTableValue = collect($longDataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $longDataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $longDataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $longDataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $longDataTableValue['visibility']);
            $this->assertEquals($data->key, $longDataTableValue['key']);
            $this->assertEquals($data->data_blob, $longDataTableValue['data_blob']);
        }
    }

    public function testChangeMeetingChange()
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
            'worldId' => 'nice world id',
        ];

        $meeting = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->json();
        $meeting = Meeting::query()->where('id_bigint', $meeting['id'])->first();
        $meeting->loadMissing(['data', 'longdata']);

        $payload['name'] = 'new name';
        $this
            ->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/{$meeting->id_bigint}", $payload)
            ->assertStatus(204);

        $change = Change::query()->orderBy('id_bigint', 'desc')->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($meeting->service_body_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals($change->object_class_string, 'c_comdef_meeting');
        $this->assertEquals($meeting->id_bigint, $change->before_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->before_lang_enum);
        $this->assertEquals($meeting->id_bigint, $change->after_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->after_lang_enum);
        $this->assertEquals($change->change_type_enum, 'comdef_change_type_change');

        // BEFORE
        $this->assertNotNull($change->before_object);
        $object = $change->before_object;
        $mainTableValues = $object['main_table_values'];
        $this->assertEquals($meeting->id_bigint, $mainTableValues['id_bigint']);
        $this->assertEquals($meeting->email_contact, $mainTableValues['email_contact']);
        $this->assertEquals($meeting->worldid_mixed, $mainTableValues['worldid_mixed']);
        $this->assertEquals($meeting->service_body_bigint, $mainTableValues['service_body_bigint']);
        $this->assertEquals($meeting->weekday_tinyint, $mainTableValues['weekday_tinyint']);
        $this->assertEquals($meeting->venue_type, $mainTableValues['venue_type']);
        $this->assertEquals($meeting->start_time, $mainTableValues['start_time']);
        $this->assertEquals($meeting->lang_enum, $mainTableValues['lang_enum']);
        $this->assertEquals($meeting->duration_time, $mainTableValues['duration_time']);
        $this->assertEquals($meeting->time_zone, $mainTableValues['time_zone']);
        $this->assertEquals($meeting->longitude, $mainTableValues['longitude']);
        $this->assertEquals($meeting->latitude, $mainTableValues['latitude']);
        $this->assertEquals($meeting->published, $mainTableValues['published']);
        $this->assertEquals($meeting->formats, $mainTableValues['formats']);
        $dataTableValues = $object['data_table_values'];
        $this->assertEquals(count($meeting->data), count($dataTableValues));
        foreach ($meeting->data as $data) {
            $dataTableValue = collect($dataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $dataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $dataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $dataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $dataTableValue['visibility']);
            $this->assertEquals($data->key, $dataTableValue['key']);
            $this->assertEquals($data->data_string, $dataTableValue['data_string']);
            $this->assertEquals($data->data_bigint, $dataTableValue['data_bigint']);
            $this->assertEquals($data->data_double, $dataTableValue['data_double']);
        }
        $longDataTableValues = $object['longdata_table_values'];
        $this->assertEquals(count($meeting->longdata), count($longDataTableValues));
        foreach ($meeting->longdata as $data) {
            $longDataTableValue = collect($longDataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $longDataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $longDataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $longDataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $longDataTableValue['visibility']);
            $this->assertEquals($data->key, $longDataTableValue['key']);
            $this->assertEquals($data->data_blob, $longDataTableValue['data_blob']);
        }

        // AFTER
        $meeting->refresh();
        $this->assertNotNull($change->after_object);
        $object = $change->after_object;
        $mainTableValues = $object['main_table_values'];
        $this->assertEquals($meeting->id_bigint, $mainTableValues['id_bigint']);
        $this->assertEquals($meeting->email_contact, $mainTableValues['email_contact']);
        $this->assertEquals($meeting->worldid_mixed, $mainTableValues['worldid_mixed']);
        $this->assertEquals($meeting->service_body_bigint, $mainTableValues['service_body_bigint']);
        $this->assertEquals($meeting->weekday_tinyint, $mainTableValues['weekday_tinyint']);
        $this->assertEquals($meeting->venue_type, $mainTableValues['venue_type']);
        $this->assertEquals($meeting->start_time, $mainTableValues['start_time']);
        $this->assertEquals($meeting->lang_enum, $mainTableValues['lang_enum']);
        $this->assertEquals($meeting->duration_time, $mainTableValues['duration_time']);
        $this->assertEquals($meeting->time_zone, $mainTableValues['time_zone']);
        $this->assertEquals($meeting->longitude, $mainTableValues['longitude']);
        $this->assertEquals($meeting->latitude, $mainTableValues['latitude']);
        $this->assertEquals($meeting->published, $mainTableValues['published']);
        $this->assertEquals($meeting->formats, $mainTableValues['formats']);
        $dataTableValues = $object['data_table_values'];
        $this->assertEquals(count($meeting->data), count($dataTableValues));
        foreach ($meeting->data as $data) {
            $dataTableValue = collect($dataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $dataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $dataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $dataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $dataTableValue['visibility']);
            $this->assertEquals($data->key, $dataTableValue['key']);
            $this->assertEquals($data->data_string, $dataTableValue['data_string']);
            $this->assertEquals($data->data_bigint, $dataTableValue['data_bigint']);
            $this->assertEquals($data->data_double, $dataTableValue['data_double']);
        }
        $longDataTableValues = $object['longdata_table_values'];
        $this->assertEquals(count($meeting->longdata), count($longDataTableValues));
        foreach ($meeting->longdata as $data) {
            $longDataTableValue = collect($longDataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $longDataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $longDataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $longDataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $longDataTableValue['visibility']);
            $this->assertEquals($data->key, $longDataTableValue['key']);
            $this->assertEquals($data->data_blob, $longDataTableValue['data_blob']);
        }
    }

    public function testDeleteMeetingChange()
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
            'worldId' => 'nice world id',
        ];

        $meeting = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/meetings', $payload)
            ->json();
        $meeting = Meeting::query()->where('id_bigint', $meeting['id'])->first();
        $meeting->loadMissing(['data', 'longdata']);

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/{$meeting->id_bigint}")
            ->assertStatus(204);

        $change = Change::query()->orderBy('id_bigint', 'desc')->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($meeting->service_body_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals($change->object_class_string, 'c_comdef_meeting');
        $this->assertEquals($meeting->id_bigint, $change->before_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->before_lang_enum);
        $this->assertNull($change->after_id_bigint);
        $this->assertNull($change->after_lang_enum);
        $this->assertEquals($change->change_type_enum, 'comdef_change_type_delete');

        // BEFORE
        $this->assertNotNull($change->before_object);
        $object = $change->before_object;
        $mainTableValues = $object['main_table_values'];
        $this->assertEquals($meeting->id_bigint, $mainTableValues['id_bigint']);
        $this->assertEquals($meeting->email_contact, $mainTableValues['email_contact']);
        $this->assertEquals($meeting->worldid_mixed, $mainTableValues['worldid_mixed']);
        $this->assertEquals($meeting->service_body_bigint, $mainTableValues['service_body_bigint']);
        $this->assertEquals($meeting->weekday_tinyint, $mainTableValues['weekday_tinyint']);
        $this->assertEquals($meeting->venue_type, $mainTableValues['venue_type']);
        $this->assertEquals($meeting->start_time, $mainTableValues['start_time']);
        $this->assertEquals($meeting->lang_enum, $mainTableValues['lang_enum']);
        $this->assertEquals($meeting->duration_time, $mainTableValues['duration_time']);
        $this->assertEquals($meeting->time_zone, $mainTableValues['time_zone']);
        $this->assertEquals($meeting->longitude, $mainTableValues['longitude']);
        $this->assertEquals($meeting->latitude, $mainTableValues['latitude']);
        $this->assertEquals($meeting->published, $mainTableValues['published']);
        $this->assertEquals($meeting->formats, $mainTableValues['formats']);
        $dataTableValues = $object['data_table_values'];
        $this->assertEquals(count($meeting->data), count($dataTableValues));
        foreach ($meeting->data as $data) {
            $dataTableValue = collect($dataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $dataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $dataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $dataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $dataTableValue['visibility']);
            $this->assertEquals($data->key, $dataTableValue['key']);
            $this->assertEquals($data->data_string, $dataTableValue['data_string']);
            $this->assertEquals($data->data_bigint, $dataTableValue['data_bigint']);
            $this->assertEquals($data->data_double, $dataTableValue['data_double']);
        }
        $longDataTableValues = $object['longdata_table_values'];
        $this->assertEquals(count($meeting->longdata), count($longDataTableValues));
        foreach ($meeting->longdata as $data) {
            $longDataTableValue = collect($longDataTableValues)->where('key', $data->key)->first();
            $this->assertEquals($data->meetingid_bigint, $longDataTableValue['meetingid_bigint']);
            $this->assertEquals($data->lang_enum, $longDataTableValue['lang_enum']);
            $this->assertEquals($data->field_prompt, $longDataTableValue['field_prompt']);
            $this->assertEquals($data->visibility, $longDataTableValue['visibility']);
            $this->assertEquals($data->key, $longDataTableValue['key']);
            $this->assertEquals($data->data_blob, $longDataTableValue['data_blob']);
        }

        // AFTER
        $this->assertNull($change->after_object);
    }
}
