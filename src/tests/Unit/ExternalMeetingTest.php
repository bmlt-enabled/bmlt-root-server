<?php

namespace Tests\Unit;

use App\Models\Format;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\MeetingLongData;
use App\Repositories\External\ExternalMeeting;
use App\Repositories\External\InvalidMeetingException;
use PHPUnit\Framework\TestCase;

class ExternalMeetingTest extends TestCase
{
    private function validValues(): array
    {
        return [
            'id_bigint' => '12875',
            'service_body_bigint' => '63',
            'weekday_tinyint' => '7',
            'venue_type' => '1',
            'start_time' => '20:00:00',
            'duration_time' => '01:00:00',
            'longitude' => '-84.4661174',
            'latitude' => '33.8658047',
            'comments' => 'comments',
            'virtual_meeting_additional_info' => 'additional',
            'location_city_subsection' => 'subsection',
            'virtual_meeting_link' => 'link',
            'phone_meeting_number' => 'number',
            'location_nation' => '',
            'location_postal_code_1' => '30339',
            'location_province' => 'GA',
            'location_sub_province' => 'Cobb',
            'location_municipality' => 'Atlanta',
            'location_neighborhood' => 'Smyrna',
            'location_street' => '4336 Paces Ferry Rd SE',
            'location_info' => 'location info',
            'location_text' => 'Vinings Fire House (in back)',
            'meeting_name' => 'F.I.R.E. House NA Meeting',
            'bus_lines' => 'busss',
            'train_lines' => 'trainsss',
            'worldid_mixed' => 'G00227317',
            'published' => '1',
            'format_shared_id_list' => '17,19'
        ];
    }

    private function getModel(array $validValues, array $formatSharedIds): Meeting
    {
        $meeting = new Meeting([
            'source_id' => $validValues['id_bigint'],
            'service_body_bigint' => $validValues['service_body_bigint'],
            'weekday_tinyint' => $validValues['weekday_tinyint'] - 1,
            'venue_type' => $validValues['venue_type'],
            'start_time' => $validValues['start_time'],
            'duration_time' => $validValues['duration_time'],
            'longitude' => $validValues['longitude'],
            'latitude' => $validValues['latitude'],
            'worldid_mixed' => $validValues['worldid_mixed'],
            'published' => $validValues['published'],
            'formats' => implode(',', $formatSharedIds),
        ]);

        $meeting->data = collect($validValues)
            ->reject(fn ($value, $fieldName) => !in_array($fieldName, MeetingData::STOCK_FIELDS) || strlen($value) > 255)
            ->map(fn ($value, $fieldName) => new MeetingData([
                'key' => $fieldName,
                'field_prompt' => $fieldName,
                'lang_enum' => 'en',
                'data_string' => $value,
                'visibility' => 0,
            ]));

        $meeting->longdata = collect($validValues)
            ->reject(fn ($value, $fieldName) => !in_array($fieldName, MeetingData::STOCK_FIELDS) || strlen($value) < 255)
            ->map(fn ($value, $fieldName) => new MeetingLongData([
                'key' => $fieldName,
                'field_prompt' => $fieldName,
                'lang_enum' => 'en',
                'data_blob' => $value,
                'visibility' => 0,
            ]));

        return $meeting;
    }

    private function getFormat(int $sharedId, int $sourceId): Format
    {
        return new Format([
            'source_id' => $sourceId,
            'shared_id_bigint' => $sharedId,
        ]);
    }

    public function testValidWithoutNulls()
    {
        $values = $this->validValues();
        $meeting = new ExternalMeeting($values);
        $this->assertEquals($values['id_bigint'], $meeting->id);
        $this->assertEquals($values['worldid_mixed'], $meeting->worldId);
        $this->assertEquals($values['service_body_bigint'], $meeting->serviceBodyId);
        $this->assertEquals($values['weekday_tinyint'], $meeting->weekdayId);
        $this->assertEquals($values['venue_type'], $meeting->venueType);
        $this->assertEquals($values['start_time'], $meeting->startTime);
        $this->assertEquals($values['duration_time'], $meeting->durationTime);
        $this->assertEquals($values['latitude'], $meeting->latitude);
        $this->assertEquals($values['longitude'], $meeting->longitude);
        $this->assertEquals($values['meeting_name'], $meeting->name);
        $this->assertEquals($values['comments'], $meeting->comments);
        $this->assertEquals($values['virtual_meeting_additional_info'], $meeting->virtualMeetingAdditionalInfo);
        $this->assertEquals($values['virtual_meeting_link'], $meeting->virtualMeetingLink);
        $this->assertEquals($values['phone_meeting_number'], $meeting->phoneMeetingNumber);
        $this->assertEquals($values['location_city_subsection'], $meeting->locationCitySubsection);
        $this->assertEquals($values['location_nation'], $meeting->locationNation);
        $this->assertEquals($values['location_postal_code_1'], $meeting->locationPostalCode1);
        $this->assertEquals($values['location_province'], $meeting->locationProvince);
        $this->assertEquals($values['location_sub_province'], $meeting->locationSubProvince);
        $this->assertEquals($values['location_municipality'], $meeting->locationMunicipality);
        $this->assertEquals($values['location_neighborhood'], $meeting->locationNeighborhood);
        $this->assertEquals($values['location_street'], $meeting->locationStreet);
        $this->assertEquals($values['location_info'], $meeting->locationInfo);
        $this->assertEquals($values['location_text'], $meeting->locationText);
        $this->assertEquals($values['bus_lines'], $meeting->busLines);
        $this->assertEquals($values['train_lines'], $meeting->trainLines);
        $this->assertEquals($values['published'], $meeting->published);
        $formatIds = collect($meeting->formatIds)->sort();
        $expectedFormatIds = collect(explode(',', $values['format_shared_id_list']))
            ->map(fn ($f) => intval($f))
            ->sort()
            ->unique();
        $this->assertEquals($expectedFormatIds, $formatIds);
    }

    public function testValidWithEmpty()
    {
        $values = $this->validValues();
        $values['worldid_mixed'] = '';
        $values['comments'] = '';
        $values['virtual_meeting_additional_info'] = '';
        $values['virtual_meeting_link'] = '';
        $values['phone_meeting_number'] = '';
        $values['location_city_subsection'] = '';
        $values['location_nation'] = '';
        $values['location_postal_code_1'] = '';
        $values['location_province'] = '';
        $values['location_sub_province'] = '';
        $values['location_municipality'] = '';
        $values['location_neighborhood'] = '';
        $values['location_street'] = '';
        $values['location_info'] = '';
        $values['location_text'] = '';
        $values['bus_lines'] = '';
        $values['train_lines'] = '';
        $values['venue_type'] = '';
        $values['latitude'] = '';
        $values['longitude'] = '';
        $values['format_shared_id_list'] = '';
        $meeting = new ExternalMeeting($values);
        $this->assertEquals($values['id_bigint'], $meeting->id);
        $this->assertEquals($values['service_body_bigint'], $meeting->serviceBodyId);
        $this->assertEquals($values['weekday_tinyint'], $meeting->weekdayId);
        $this->assertEquals($values['start_time'], $meeting->startTime);
        $this->assertEquals($values['duration_time'], $meeting->durationTime);
        $this->assertEquals($values['meeting_name'], $meeting->name);
        $this->assertEquals($values['published'], $meeting->published);
        $this->assertNull($meeting->latitude);
        $this->assertNull($meeting->longitude);
        $this->assertNull($meeting->venueType);
        $this->assertNull($meeting->worldId);
        $this->assertNull($meeting->comments);
        $this->assertNull($meeting->virtualMeetingAdditionalInfo);
        $this->assertNull($meeting->virtualMeetingLink);
        $this->assertNull($meeting->phoneMeetingNumber);
        $this->assertNull($meeting->locationCitySubsection);
        $this->assertNull($meeting->locationNation);
        $this->assertNull($meeting->locationPostalCode1);
        $this->assertNull($meeting->locationProvince);
        $this->assertNull($meeting->locationSubProvince);
        $this->assertNull($meeting->locationMunicipality);
        $this->assertNull($meeting->locationNeighborhood);
        $this->assertNull($meeting->locationStreet);
        $this->assertNull($meeting->locationInfo);
        $this->assertNull($meeting->locationText);
        $this->assertNull($meeting->busLines);
        $this->assertNull($meeting->trainLines);
        $this->assertIsArray($meeting->formatIds);
        $this->assertEmpty($meeting->formatIds);
    }

    public function testValidWithNulls()
    {
        $values = $this->validValues();
        $values['worldid_mixed'] = null;
        $values['comments'] = null;
        $values['virtual_meeting_additional_info'] = null;
        $values['virtual_meeting_link'] = null;
        $values['phone_meeting_number'] = null;
        $values['location_city_subsection'] = null;
        $values['location_nation'] = null;
        $values['location_postal_code_1'] = null;
        $values['location_province'] = null;
        $values['location_sub_province'] = null;
        $values['location_municipality'] = null;
        $values['location_neighborhood'] = null;
        $values['location_street'] = null;
        $values['location_info'] = null;
        $values['location_text'] = null;
        $values['bus_lines'] = null;
        $values['train_lines'] = null;
        $values['venue_type'] = null;
        $values['latitude'] = null;
        $values['longitude'] = null;
        $values['format_shared_id_list'] = null;
        $meeting = new ExternalMeeting($values);
        $this->assertEquals($values['id_bigint'], $meeting->id);
        $this->assertEquals($values['service_body_bigint'], $meeting->serviceBodyId);
        $this->assertEquals($values['weekday_tinyint'], $meeting->weekdayId);
        $this->assertEquals($values['start_time'], $meeting->startTime);
        $this->assertEquals($values['duration_time'], $meeting->durationTime);
        $this->assertEquals($values['meeting_name'], $meeting->name);
        $this->assertEquals($values['published'], $meeting->published);
        $this->assertNull($meeting->latitude);
        $this->assertNull($meeting->longitude);
        $this->assertNull($meeting->venueType);
        $this->assertNull($meeting->worldId);
        $this->assertNull($meeting->comments);
        $this->assertNull($meeting->virtualMeetingAdditionalInfo);
        $this->assertNull($meeting->virtualMeetingLink);
        $this->assertNull($meeting->phoneMeetingNumber);
        $this->assertNull($meeting->locationCitySubsection);
        $this->assertNull($meeting->locationNation);
        $this->assertNull($meeting->locationPostalCode1);
        $this->assertNull($meeting->locationProvince);
        $this->assertNull($meeting->locationSubProvince);
        $this->assertNull($meeting->locationMunicipality);
        $this->assertNull($meeting->locationNeighborhood);
        $this->assertNull($meeting->locationStreet);
        $this->assertNull($meeting->locationInfo);
        $this->assertNull($meeting->locationText);
        $this->assertNull($meeting->busLines);
        $this->assertNull($meeting->trainLines);
        $this->assertIsArray($meeting->formatIds);
        $this->assertEmpty($meeting->formatIds);
    }

    public function testValidWithMissing()
    {
        $values = $this->validValues();
        unset($values['worldid_mixed']);
        unset($values['comments']);
        unset($values['virtual_meeting_additional_info']);
        unset($values['virtual_meeting_link']);
        unset($values['phone_meeting_number']);
        unset($values['location_city_subsection']);
        unset($values['location_nation']);
        unset($values['location_postal_code_1']);
        unset($values['location_province']);
        unset($values['location_sub_province']);
        unset($values['location_municipality']);
        unset($values['location_neighborhood']);
        unset($values['location_street']);
        unset($values['location_info']);
        unset($values['location_text']);
        unset($values['bus_lines']);
        unset($values['train_lines']);
        unset($values['venue_type']);
        unset($values['latitude']);
        unset($values['longitude']);
        unset($values['format_shared_id_list']);
        $meeting = new ExternalMeeting($values);
        $this->assertEquals($values['id_bigint'], $meeting->id);
        $this->assertEquals($values['service_body_bigint'], $meeting->serviceBodyId);
        $this->assertEquals($values['weekday_tinyint'], $meeting->weekdayId);
        $this->assertEquals($values['start_time'], $meeting->startTime);
        $this->assertEquals($values['duration_time'], $meeting->durationTime);
        $this->assertEquals($values['meeting_name'], $meeting->name);
        $this->assertEquals($values['published'], $meeting->published);
        $this->assertNull($meeting->latitude);
        $this->assertNull($meeting->longitude);
        $this->assertNull($meeting->venueType);
        $this->assertNull($meeting->worldId);
        $this->assertNull($meeting->comments);
        $this->assertNull($meeting->virtualMeetingAdditionalInfo);
        $this->assertNull($meeting->virtualMeetingLink);
        $this->assertNull($meeting->phoneMeetingNumber);
        $this->assertNull($meeting->locationCitySubsection);
        $this->assertNull($meeting->locationNation);
        $this->assertNull($meeting->locationPostalCode1);
        $this->assertNull($meeting->locationProvince);
        $this->assertNull($meeting->locationSubProvince);
        $this->assertNull($meeting->locationMunicipality);
        $this->assertNull($meeting->locationNeighborhood);
        $this->assertNull($meeting->locationStreet);
        $this->assertNull($meeting->locationInfo);
        $this->assertNull($meeting->locationText);
        $this->assertNull($meeting->busLines);
        $this->assertNull($meeting->trainLines);
        $this->assertIsArray($meeting->formatIds);
        $this->assertEmpty($meeting->formatIds);
    }

    public function testMissingId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        unset($values['id_bigint']);
        new ExternalMeeting($values);
    }

    public function testInvalidId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['id_bigint'] = 'string';
        new ExternalMeeting($values);
    }

    public function testMissingServiceBodyId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        unset($values['service_body_bigint']);
        new ExternalMeeting($values);
    }

    public function testInvalidServiceBodyId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['service_body_bigint'] = 'string';
        new ExternalMeeting($values);
    }

    public function testMissingWeekdayId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        unset($values['weekday_tinyint']);
        new ExternalMeeting($values);
    }

    public function testInvalidWeekdayId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['weekday_tinyint'] = 'string';
        new ExternalMeeting($values);
    }

    public function testInvalidVenueType()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['venue_type'] = 'string';
        new ExternalMeeting($values);
    }

    public function testMissingStartTime()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        unset($values['start_time']);
        new ExternalMeeting($values);
    }

    public function testInvalidStartTime()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['start_time'] = 'string';
        new ExternalMeeting($values);
    }

    public function testMissingDurationTime()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        unset($values['duration_time']);
        new ExternalMeeting($values);
    }

    public function testInvalidDurationTime()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['duration_time'] = 'string';
        new ExternalMeeting($values);
    }

    public function testMissingName()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        unset($values['meeting_name']);
        new ExternalMeeting($values);
    }

    public function testInvalidName()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['meeting_name'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidComments()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['comments'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidVirtualMeetingAdditionalInfo()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['virtual_meeting_additional_info'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidVirtualMeetingLink()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['virtual_meeting_link'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidPhoneMeetingNumber()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['phone_meeting_number'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationCitySubsection()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_city_subsection'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationNation()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_nation'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationPostalCode1()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_postal_code_1'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationProvince()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_province'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationSubProvince()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_sub_province'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationMunicipality()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_municipality'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationNeighborhood()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_neighborhood'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationStreet()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_street'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationInfo()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_info'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidLocationText()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['location_text'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidBusLines()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['bus_lines'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidTrainLines()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['train_lines'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidWorldId()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['worldid_mixed'] = 123;
        new ExternalMeeting($values);
    }

    public function testInvalidFormatIds()
    {
        $this->expectException(InvalidMeetingException::class);
        $values = $this->validValues();
        $values['format_shared_id_list'] = 123;
        new ExternalMeeting($values);
    }

    // isEqual
    //
    //
    public function testNoDifferences()
    {
        $f1 = $this->getFormat(1, 100);
        $f2 = $this->getFormat(2, 200);

        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id,$f2->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f2->shared_id_bigint, $f1->shared_id_bigint]);

        $this->assertTrue($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id, $f2->shared_id_bigint => $f2->source_id])));
    }

    public function testId()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->source_id = $external->id + 1;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testWorldId()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->worldid_mixed = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testServiceBodyId()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->service_body_bigint = $external->serviceBodyId + 1;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testWeekdayId()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->weekday_tinyint = $external->weekdayId + 1;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testVenueType()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->venue_type = $external->venueType + 1;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));

        $db->venue_type = null;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testStartTime()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->start_time = '03:00:00';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testDurationTime()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->duration_time = '03:00:00';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLatitude()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->latitude = 1.234;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLongitude()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->longitude = 1.234;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testPublished()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->published = 0;
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testFormatIds()
    {
        $f1 = $this->getFormat(1, 100);
        $f2 = $this->getFormat(2, 200);
        $f3 = $this->getFormat(3, 300);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id,$f2->source_id,$f3->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint,$f2->shared_id_bigint]);

        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id, $f2->shared_id_bigint => $f2->source_id, $f3->shared_id_bigint => $f3->source_id])));
    }

    public function testComments()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'comments')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testVirtualMeetingAdditionalInfo()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'virtual_meeting_additional_info')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testVirtualMeetingLink()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'virtual_meeting_link')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testPhoneMeetingNumber()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'phone_meeting_number')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationCitySubsection()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_city_subsection')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationNation()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_nation')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationPostalCode1()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_postal_code_1')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationProvince()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_province')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationSubProvince()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_sub_province')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationMunicipality()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_municipality')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationNeighborhood()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_neighborhood')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationStreet()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_street')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationInfo()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_info')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testLocationText()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'location_text')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testBusLines()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'bus_lines')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }

    public function testTrainLines()
    {
        $f1 = $this->getFormat(1, 100);
        $values = $this->validValues();
        $values['format_shared_id_list'] = "$f1->source_id";

        $external = new ExternalMeeting($values);
        $db = $this->getModel($values, [$f1->shared_id_bigint]);

        $db->data->firstWhere(fn ($d) => $d->key == 'train_lines')->data_string = 'changed';
        $this->assertFalse($external->isEqual($db, collect([$f1->shared_id_bigint => $f1->source_id])));
    }
}
