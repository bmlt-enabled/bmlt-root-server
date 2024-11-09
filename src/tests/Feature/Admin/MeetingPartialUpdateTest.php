<?php

namespace Tests\Feature\Admin;

use App\Http\Resources\Admin\MeetingResource;
use App\Models\Change;
use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Repositories\MeetingRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingPartialUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        MeetingResource::resetStaticVariables();
        parent::tearDown();
    }

    protected function createMeeting(array $mainFields = [], array $dataFields = [], array $longDataFields = [], array $removeFieldKeys = [])
    {
        $mainFields = collect([
            'formats' => '',
            'venue_type' => Meeting::VENUE_TYPE_IN_PERSON,
            'weekday_tinyint' => 0,
            'start_time' => '20:00:00',
            'duration_time' => '01:00:00',
            'latitude' => 35.7079,
            'longitude' => 79.8136,
            'published' => 1,
            'time_zone' => 'America/New_York'
        ])
        ->reject(fn ($_, $key) => in_array($key, $removeFieldKeys))
        ->merge($mainFields)
        ->toArray();

        $dataFields = collect([
            'location_street' => '813 Darby St.',
            'location_municipality' => 'Raleigh',
            'location_province' => 'NC',
            'location_postal_code_1' => '27610',
            'virtual_meeting_link' => 'https://zoom.us',
            'phone_meeting_number' => '5555555555',
        ])
        ->reject(fn ($_, $key) => in_array($key, $removeFieldKeys) || array_key_exists($key, $longDataFields))
        ->merge($dataFields)
        ->toArray();

        return parent::createMeeting($mainFields, $dataFields, $longDataFields);
    }

    public function testPartialUpdateMeetingAllFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        foreach (collect(Meeting::$mainFields)->merge(MeetingData::STOCK_FIELDS) as $fieldName) {
            if ($fieldName == 'id_bigint' || $fieldName == 'formats' || $fieldName == 'time_zone' || $fieldName == 'lang_enum' || $fieldName == 'root_server_id' || $fieldName == 'source_id') {
                continue;
            }

            $payload = [];

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

            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            $meeting->refresh();
            if (in_array($fieldName, Meeting::$mainFields)) {
                if ($fieldName == 'duration_time') {
                    $this->assertEquals('01:30:00', $meeting->{$fieldName});
                } elseif ($fieldName == 'start_time') {
                    $this->assertEquals('08:00:00', $meeting->{$fieldName});
                } else {
                    $this->assertEquals($payload[array_key_first($payload)], $meeting->{$fieldName});
                }
            } else {
                $meetingData = $meeting->data
                    ->mapWithKeys(fn($data, $_) => [$data->key => $data->data_string])->toBase()
                    ->merge($meeting->longdata->mapWithKeys(fn($data, $_) => [$data->key => $data->data_blob])->toBase());
                $this->assertTrue($meetingData->has($fieldName));
                $this->assertEquals($payload[array_key_first($payload)], $meetingData->get($fieldName));
            }
        }
    }

    // TODO Write some tests specific to custom fields. Need to make sure excluded custom fields are
    // not modified, and that included custom fields are.

    public function testPartialMeetingUpdateFormats()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        $payload = [];
        $payload = ['formatIds' => []];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
        $meeting->refresh();
        $this->assertEquals('', $meeting->formats);

        $payload = ['formatIds' => [$format->shared_id_bigint]];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
        $meeting->refresh();
        $this->assertEquals(strval($format->shared_id_bigint), $meeting->formats);
    }

    public function testPartialUpdateMeetingValidateServiceBodyId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['serviceBodyId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid service body
        $payload['serviceBodyId'] = $area->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be a valid service body
        $payload['serviceBodyId'] = $area->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateFormatIds()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['formatIds'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid format id
        $payload['formatIds'] = [Format::query()->max('shared_id_bigint') + 1];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be an empty list
        $payload['formatIds'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be a valid service body
        $payload['formatIds'] = [$format->shared_id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateVenueType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['venueType'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid value
        $payload['venueType'] = 9999;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be a valid value
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateTemporarilyVirtual()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be invalid values
        foreach (['blah', 9999] as $published) {
            $payload['temporarilyVirtual'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([true, false, 0, 1, '0', '1'] as $published) {
            $payload['temporarilyVirtual'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }

        // it is not required
        unset($payload['temporarilyVirtual']);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateDay()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['day'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be less than 0
        $payload['day'] = -1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be greater than 6
        $payload['day'] = 7;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be a valid value
        foreach ([0,1,2,3,4,5,6] as $day) {
            $payload['day'] = $day;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateStartTime()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['startTime'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be an invalid value
        $payload['startTime'] = '08:00:00';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be valid values
        foreach (['00:00', '23:59'] as $startTime) {
            $payload['startTime'] = $startTime;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateDuration()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['duration'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be an invalid value
        $payload['duration'] = '08:00:00';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be valid values
        foreach (['00:00', '23:59'] as $duration) {
            $payload['duration'] = $duration;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateLatitude()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['latitude'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach ([-90.01, 90.01] as $latitude) {
            $payload['latitude'] = $latitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([-90, 0, 90] as $latitude) {
            $payload['latitude'] = $latitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateLongitude()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['longitude'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach ([-180.01, 180.01] as $longitude) {
            $payload['longitude'] = $longitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([-180.0, 0, 180] as $longitude) {
            $payload['longitude'] = $longitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidatePublished()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be null
        $payload['published'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach (['blah', 9999] as $published) {
            $payload['published'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([true, false, 0, 1, '0', '1'] as $published) {
            $payload['published'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be an invalid email
        $payload['email'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be longer than be 255 characters
        $payload['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . 'z.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 255 characters
        $payload['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . '.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it is not required
        unset($payload['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can't be more than 30 chars
        $payload['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 30 characters
        $payload['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it is not required
        unset($payload['worldId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        // it can be null
        $payload['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be more than 128 chars
        $payload['name'] = str_repeat('t', 129);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 128 characters
        $payload['name'] = str_repeat('t', 128);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateDataFields()
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

            $payload = [];

            if (!in_array($template->key, ['location_street', 'location_municipality', 'location_province', 'location_postal_code_1', 'virtual_meeting_link', 'phone_meeting_number'])) {
                // it can be null
                $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
                $payload = [];
                $payload[$template->key] = null;
                $this->withHeader('Authorization', "Bearer $token")
                    ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                    ->assertStatus(204);

                // it is not required
                MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
                MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
                $meeting->delete();
                $meeting = $this->createMeeting(
                    ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                    removeFieldKeys: [$template->key]
                );
                $payload = [];
                $this->withHeader('Authorization', "Bearer $token")
                    ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                    ->assertStatus(204);
            }

            // it can't be more than 512 chars
            $payload = [];
            $payload[$template->key] = str_repeat('t', 513);
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            // it can be 512 characters
            $payload = [];
            $payload[$template->key] = str_repeat('t', 512);
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateCustomDataFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $customFieldName = "customFieldName";
        $this->addCustomField($customFieldName);

        // the field can be null
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
        $payload = [];
        $payload["customFields"] = [$customFieldName => null];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // the list can be empty
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
        $payload = [];
        $payload["customFields"] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // the list is not required
        MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
        MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
        $meeting->delete();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            removeFieldKeys: [$customFieldName]
        );
        $payload = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can't be more than 512 chars
        $payload = [];
        $payload["customFields"] = [$customFieldName => str_repeat('t', 513)];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 512 characters
        $payload = [];
        $payload["customFields"] = [$customFieldName => str_repeat('t', 512)];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateInPersonStreet()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        foreach ([Meeting::VENUE_TYPE_IN_PERSON, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // it is required
            $payload['location_street'] = null;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            // it can be 512
            $payload['location_street'] = '813 Darby St.';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateInPersonCityStateZip()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();

        foreach ([Meeting::VENUE_TYPE_IN_PERSON, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_municipality'] = null;
            $payload['location_province'] = null;
            $payload['location_postal_code_1'] = null;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['location_municipality', 'location_province', 'location_postal_code_1']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_municipality'] = 'Raleigh';
            $payload['location_province'] = null;
            $payload['location_postal_code_1'] = null;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['location_province', 'location_postal_code_1']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_municipality'] = 'Raleigh';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_municipality'] = 'Raleigh';
            $payload['location_province'] = 'NC';
            $payload['location_postal_code_1'] = null;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['location_postal_code_1']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_municipality'] = 'Raleigh';
            $payload['location_province'] = 'NC';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_municipality'] = null;
            $payload['location_province'] = null;
            $payload['location_postal_code_1'] = '27610';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['location_municipality', 'location_province']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['location_postal_code_1'] = '27610';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateInPersonVirtualMeetingLinkPhoneMeetingNumber()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
        $payload['venueType'] = Meeting::VENUE_TYPE_IN_PERSON;

        // it can be null
        $payload['virtual_meeting_link'] = null;
        $payload['phone_meeting_number'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateMeetingValidateVirtualVirtualMeetingLinkPhoneMeetingNumber()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();

        foreach ([Meeting::VENUE_TYPE_VIRTUAL, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            // one or the other is required

            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['virtual_meeting_link', 'phone_meeting_number']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['virtual_meeting_link', 'phone_meeting_number']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['virtual_meeting_link'] = 'https://zoom.us';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
            $meeting->delete();
            $meeting = $this->createMeeting(
                ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
                removeFieldKeys: ['virtual_meeting_link', 'phone_meeting_number']
            );
            $payload = [];
            $payload['venueType'] = $venueType;
            $payload['phone_meeting_number'] = '5555555555';
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateMeetingValidateVirtualStreetCityStateZip()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;

        // it is not required
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            removeFieldKeys: ['location_street', 'location_municipality', 'location_province', 'location_postal_code_1']
        );
        $payload = [];
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
        MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
        $meeting->delete();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);
        $payload = [];
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $payload['location_street'] = null;
        $payload['location_municipality'] = null;
        $payload['location_province'] = null;
        $payload['location_postal_code_1'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        MeetingData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
        MeetingLongData::query()->where('meetingid_bigint', $meeting->id_bigint)->delete();
        $meeting->delete();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => null, 'location_municipality' => null, 'location_province' => null, 'location_postal_code_1' => null]
        );
        $payload = [];
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testPartialUpdateNoChangeCreatesNoChangeRecord()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)]);

        $payload['name'] = $meeting->data->firstWhere('key', 'meeting_name')->data_string;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        $this->assertEmpty(Change::query()->get());
    }
}
