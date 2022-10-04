<?php

namespace Tests\Feature\Admin;

use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Repositories\FormatRepository;
use App\Repositories\MeetingRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class MeetingUpdateTest extends TestCase
{
    use RefreshDatabase;

    private static bool $isDataInitialized = false;
    private static ?Collection $extraFieldsTemplates = null;
    private static ?Collection $formatsById = null;
    private static ?int $virtualFormatId = null;
    private static ?int $hybridFormatId = null;
    private static ?int $temporarilyClosedFormatId = null;
    private static ?Collection $hiddenFormatIds = null;

    private function initializeData()
    {
        if (!self::$isDataInitialized) {
            $formatRepository = new FormatRepository();
            self::$formatsById = $formatRepository->getAsTranslations()->mapWithKeys(fn($fmt) => [$fmt->shared_id_bigint => $fmt]);
            self::$virtualFormatId = $formatRepository->getVirtualFormat()->shared_id_bigint;
            self::$hybridFormatId = $formatRepository->getHybridFormat()->shared_id_bigint;
            self::$temporarilyClosedFormatId = $formatRepository->getTemporarilyClosedFormat()->shared_id_bigint;
            self::$hiddenFormatIds = collect([self::$virtualFormatId, self::$hybridFormatId, self::$temporarilyClosedFormatId]);
            $meetingRepository = new MeetingRepository();
            self::$extraFieldsTemplates = $meetingRepository
                ->getDataTemplates()
                ->reject(fn($template, $_) => in_array($template->key, MeetingData::STOCK_FIELDS));
            self::$isDataInitialized = true;
        }
    }

    private function toPayload(Meeting $meeting): array
    {
        $this->initializeData();

        $meetingData = $meeting->data
            ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])
            ->toBase()
            ->merge(
                $meeting->longdata
                    ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])
                    ->toBase()
            );

        $formatIds = empty($this->formats) ? collect([]) : collect(explode(',', $this->formats))
            ->map(fn ($id) => intval($id))
            ->reject(fn ($id) => !self::$formatsById->has($id))
            ->sort();


        return array_merge([
            'serviceBodyId' => $meeting->service_body_bigint,
            'formatIds' => $formatIds->reject(fn ($id) => self::$hiddenFormatIds->contains($id))->toArray(),
            'venueType' => $meeting->venue_type,
            'temporarilyVirtual' => $meeting->venue_type == Meeting::VENUE_TYPE_VIRTUAL && $formatIds->contains(self::$temporarilyClosedFormatId),
            'day' => $meeting->weekday_tinyint,
            'startTime' => $meeting->start_time ? explode(':', $meeting->start_time)[0] . ':' . explode(':', $meeting->start_time)[1] : null,
            'duration' => $meeting->duration_time ? explode(':', $meeting->duration_time)[0] . ':' . explode(':', $meeting->duration_time)[1] : null,
            'timeZone' => $meeting->time_zone ?: null,
            'latitude' => $meeting->latitude ?? null,
            'longitude' => $meeting->longitude ?? null,
            'published' => $meeting->published === 1,
            'email' => $meeting->email_contact ?: null,
            'worldId' => $meeting->worldid_mixed ?: null,
            'name' => $meetingData->get('meeting_name') ?: null,
            'location_text' => $meetingData->get('location_text') ?: null,
            'location_info' => $meetingData->get('location_info') ?: null,
            'location_street' => $meetingData->get('location_street') ?: null,
            'location_neighborhood' => $meetingData->get('location_neighborhood') ?: null,
            'location_city_subsection' => $meetingData->get('location_city_subsection') ?: null,
            'location_municipality' => $meetingData->get('location_municipality') ?: null,
            'location_sub_province' => $meetingData->get('location_sub_province') ?: null,
            'location_province' => $meetingData->get('location_province') ?: null,
            'location_postal_code_1' => $meetingData->get('location_postal_code_1') ?: null,
            'location_nation' => $meetingData->get('location_nation') ?: null,
            'phone_meeting_number' => $meetingData->get('phone_meeting_number') ?: null,
            'virtual_meeting_link' => $meetingData->get('virtual_meeting_link') ?: null,
            'virtual_meeting_additional_info' => $meetingData->get('virtual_meeting_additional_info') ?: null,
            'contact_name_1' => $meetingData->get('contact_name_1') ?: null,
            'contact_name_2' => $meetingData->get('contact_name_2') ?: null,
            'contact_phone_1' => $meetingData->get('contact_phone_1') ?: null,
            'contact_phone_2' => $meetingData->get('contact_phone_2') ?: null,
            'contact_email_1' => $meetingData->get('contact_email_1') ?: null,
            'contact_email_2' => $meetingData->get('contact_email_2') ?: null,
            'bus_lines' => $meetingData->get('bus_lines') ?: null,
            'train_lines' => $meetingData->get('train_lines') ?: null,
            'comments' => $meetingData->get('comments') ?: null,
        ], self::$extraFieldsTemplates->mapWithKeys(fn ($t, $_) => [$t->key => $meetingData->get($t->key) ?: null])->toArray());
    }

    public function testUpdateMeetingAllFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        $fieldNames = collect(Meeting::$mainFields)->merge(MeetingData::STOCK_FIELDS);

        foreach ($fieldNames as $fieldName) {
            if ($fieldName == 'id_bigint' || $fieldName == 'formats' || $fieldName == 'time_zone' || $fieldName == 'lang_enum') {
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

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting->id_bigint")
            ->json();

        foreach ($fieldNames as $fieldName) {
            if ($fieldName == 'id_bigint' || $fieldName == 'formats' || $fieldName == 'time_zone' || $fieldName == 'lang_enum') {
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

    public function testUpdateMeetingValidateServiceBodyId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['serviceBodyId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['serviceBodyId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid service body
        $payload['serviceBodyId'] = $area->id_bigint + 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be a valid service body
        $payload['serviceBodyId'] = $area->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateFormatIds()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['formatIds']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['formatIds'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid format id
        $payload['formatIds'] = [Format::query()->max('shared_id_bigint') + 1];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be an empty list
        $payload['formatIds'] = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be a valid service body
        $payload['formatIds'] = [$format->shared_id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateVenueType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['venueType']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['venueType'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid value
        $payload['venueType'] = 9999;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be a valid value
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateTemporarilyVirtual()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it can't be null
        $payload['temporarilyVirtual'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach (['blah', 9999] as $published) {
            $payload['temporarilyVirtual'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([true, false, 0, 1, '0', '1'] as $published) {
            $payload['temporarilyVirtual'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }

        // it is not required
        unset($payload['temporarilyVirtual']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateDay()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['day']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['day'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be less than 0
        $payload['day'] = -1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be greater than 6
        $payload['day'] = 7;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be a valid value
        foreach ([0,1,2,3,4,5,6] as $day) {
            $payload['day'] = $day;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateStartTime()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['startTime']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['startTime'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be an invalid value
        $payload['startTime'] = '08:00:00';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be valid values
        foreach (['00:00', '23:59'] as $startTime) {
            $payload['startTime'] = $startTime;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateDuration()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['duration']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['duration'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be an invalid value
        $payload['duration'] = '08:00:00';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be valid values
        foreach (['00:00', '23:59'] as $duration) {
            $payload['duration'] = $duration;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateLatitude()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['latitude']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['latitude'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach ([-90.01, 90.01] as $latitude) {
            $payload['latitude'] = $latitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([-90, 0, 90] as $latitude) {
            $payload['latitude'] = $latitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateLongitude()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['longitude']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['longitude'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach ([-180.01, 180.01] as $longitude) {
            $payload['longitude'] = $longitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([-180.0, 0, 180] as $longitude) {
            $payload['longitude'] = $longitude;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidatePublished()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['published']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be null
        $payload['published'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be invalid values
        foreach (['blah', 9999] as $published) {
            $payload['published'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);
        }

        // it can be valid values
        foreach ([true, false, 0, 1, '0', '1'] as $published) {
            $payload['published'] = $published;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be an invalid email
        $payload['email'] = 'blah';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be longer than be 255 characters
        $payload['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . 'z.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 255 characters
        $payload['email'] = str_repeat('t', 255 - 63 - 5) . '@' . str_repeat('t', 63) . '.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateWorldId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['worldId']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be more than 30 chars
        $payload['worldId'] = str_repeat('t', 31);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 30 characters
        $payload['worldId'] = str_repeat('t', 30);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['worldId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        // it is required
        unset($payload['name']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be null
        $payload['name'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can't be more than 128 chars
        $payload['name'] = str_repeat('t', 129);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 128 characters
        $payload['name'] = str_repeat('t', 128);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateDataFields()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );

        $meetingRepository = new MeetingRepository();
        foreach ($meetingRepository->getDataTemplates() as $template) {
            if ($template->key == 'meeting_name') {
                continue;
            }

            $payload = $this->toPayload($meeting);

            if (!in_array($template->key, ['location_street', 'location_municipality', 'location_province', 'location_postal_code_1', 'virtual_meeting_link', 'phone_meeting_number'])) {
                // it can be null
                $payload[$template->key] = null;
                $this->withHeader('Authorization', "Bearer $token")
                    ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                    ->assertStatus(204);

                // it is not required
                unset($payload[$template->key]);
                $this->withHeader('Authorization', "Bearer $token")
                    ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                    ->assertStatus(204);
            }

            // it can't be more than 512 chars
            $payload[$template->key] = str_repeat('t', 513);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            // it can be 512 characters
            $payload[$template->key] = str_repeat('t', 512);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateInPersonStreet()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        foreach ([Meeting::VENUE_TYPE_IN_PERSON, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // it is required
            unset($payload['location_street']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            // it can be 512
            $payload['location_street'] = '813 Darby St.';
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateInPersonCityStateZip()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        foreach ([Meeting::VENUE_TYPE_IN_PERSON, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // city and state OR zip are required
            unset($payload['location_municipality']);
            unset($payload['location_province']);
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            $payload['location_municipality'] = 'Raleigh';
            unset($payload['location_province']);
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            $payload['location_municipality'] = 'Raleigh';
            $payload['location_province'] = 'NC';
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            unset($payload['location_municipality']);
            unset($payload['location_province']);
            $payload['location_postal_code_1'] = '27610';
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateInPersonVirtualMeetingLinkPhoneMeetingNumber()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);
        $payload['venueType'] = Meeting::VENUE_TYPE_IN_PERSON;

        // it is not required
        unset($payload['virtual_meeting_link']);
        unset($payload['phone_meeting_number']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['virtual_meeting_link'] = null;
        $payload['phone_meeting_number'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateVirtualVirtualMeetingLinkPhoneMeetingNumber()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

        foreach ([Meeting::VENUE_TYPE_VIRTUAL, Meeting::VENUE_TYPE_HYBRID] as $venueType) {
            $payload['venueType'] = $venueType;

            // one or the other is required
            unset($payload['virtual_meeting_link']);
            unset($payload['phone_meeting_number']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(422);

            $payload['virtual_meeting_link'] = 'https://zoom.us';
            unset($payload['phone_meeting_number']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);

            unset($payload['virtual_meeting_link']);
            $payload['phone_meeting_number'] = '5555555555';
            unset($payload['location_postal_code_1']);
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
                ->assertStatus(204);
        }
    }

    public function testUpdateMeetingValidateVirtualStreetCityStateZip()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);
        $payload['venueType'] = Meeting::VENUE_TYPE_VIRTUAL;

        // it is not required
        unset($payload['location_street']);
        unset($payload['location_municipality']);
        unset($payload['location_province']);
        unset($payload['location_postal_code_1']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['location_street'] = null;
        $payload['location_municipality'] = null;
        $payload['location_province'] = null;
        $payload['location_postal_code_1'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }

    public function testUpdateMeetingValidateExtraDataField()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $format = Format::query()->first();
        $meeting = $this->createMeeting(
            ['service_body_bigint' => $area->id_bigint, 'formats' => strval($format->shared_id_bigint)],
            ['location_street' => '813 Darby St', 'location_municipality' => 'Raleigh', 'location_province' => 'NC', 'virtual_meeting_link' => 'https://zoom.us']
        );
        $payload = $this->toPayload($meeting);

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
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can be null
        $payload['blahblah'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);

        // it can't be longer than 512
        $payload['blahblah'] = str_repeat('t', 513);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(422);

        // it can be 512
        $payload['blahblah'] = str_repeat('t', 512);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting->id_bigint", $payload)
            ->assertStatus(204);
    }
}
