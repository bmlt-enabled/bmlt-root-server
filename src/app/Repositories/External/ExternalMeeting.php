<?php

namespace App\Repositories\External;

use App\Models\Meeting;
use Illuminate\Support\Collection;

class ExternalMeeting extends ExternalObject
{
    public int $id;
    public int $serviceBodyId;
    public int $weekdayId;
    public ?int $venueType;
    public string $startTime;
    public string $durationTime;
    public ?float $latitude;
    public ?float $longitude;
    public string $name;
    public bool $published;
    public ?string $comments;
    public ?string $virtualMeetingAdditionalInfo;
    public ?string $virtualMeetingLink;
    public ?string $phoneMeetingNumber;
    public ?string $locationCitySubsection;
    public ?string $locationNation;
    public ?string $locationPostalCode1;
    public ?string $locationProvince;
    public ?string $locationSubProvince;
    public ?string $locationMunicipality;
    public ?string $locationNeighborhood;
    public ?string $locationStreet;
    public ?string $locationInfo;
    public ?string $locationText;
    public ?string $busLines;
    public ?string $trainLines;
    public ?string $worldId;
    public array $formatIds;

    public function __construct(array $values)
    {
        $this->id = $this->validateInt($values, 'id_bigint');
        $this->serviceBodyId = $this->validateInt($values, 'service_body_bigint');
        $this->weekdayId = $this->validateInt($values, 'weekday_tinyint');
        $this->venueType = $this->validateNullableInt($values, 'venue_type');
        $this->startTime = $this->validateTime($values, 'start_time');
        $this->durationTime = $this->validateTime($values, 'duration_time');
        $this->latitude = $this->validateNullableFloat($values, 'latitude');
        $this->longitude = $this->validateNullableFloat($values, 'longitude');
        $this->name = $this->validateString($values, 'meeting_name');
        $this->comments = $this->validateNullableString($values, 'comments');
        $this->virtualMeetingAdditionalInfo = $this->validateNullableString($values, 'virtual_meeting_additional_info');
        $this->virtualMeetingLink = $this->validateNullableString($values, 'virtual_meeting_link');
        $this->phoneMeetingNumber = $this->validateNullableString($values, 'phone_meeting_number');
        $this->locationCitySubsection = $this->validateNullableString($values, 'location_city_subsection');
        $this->locationNation = $this->validateNullableString($values, 'location_nation');
        $this->locationPostalCode1 = $this->validateNullableString($values, 'location_postal_code_1');
        $this->locationProvince = $this->validateNullableString($values, 'location_province');
        $this->locationSubProvince = $this->validateNullableString($values, 'location_sub_province');
        $this->locationMunicipality = $this->validateNullableString($values, 'location_municipality');
        $this->locationNeighborhood = $this->validateNullableString($values, 'location_neighborhood');
        $this->locationStreet = $this->validateNullableString($values, 'location_street');
        $this->locationInfo = $this->validateNullableString($values, 'location_info');
        $this->locationText = $this->validateNullableString($values, 'location_text');
        $this->busLines = $this->validateNullableString($values, 'bus_lines');
        $this->trainLines = $this->validateNullableString($values, 'train_lines');
        $this->worldId = $this->validateNullableString($values, 'worldid_mixed');
        $this->published = $this->validateBool($values, 'published');
        $this->formatIds = $this->validateIntArray($values, 'format_shared_id_list');
    }

    public function isEqual(Meeting $meeting, Collection $serviceBodyIdToSourceIdMap, Collection $formatSharedIdToSourceIdMap): bool
    {
        if ($this->id != $meeting->source_id) {
            return false;
        }
        if ($this->worldId != $meeting->worldid_mixed) {
            return false;
        }
        if ($this->serviceBodyId != $serviceBodyIdToSourceIdMap->get($meeting->service_body_bigint)) {
            return false;
        }
        if ($this->weekdayId != ($meeting->weekday_tinyint + 1)) {
            return false;
        }
        if ($this->venueType != $meeting->venue_type) {
            return false;
        }
        if ($this->startTime != $meeting->start_time) {
            return false;
        }
        if ($this->durationTime != $meeting->duration_time) {
            return false;
        }
        if ($this->latitude != $meeting->latitude) {
            if (is_null($this->latitude) || is_null($meeting->latitude)) {
                return false;
            } else if (!$this->floatsAreEqual($this->latitude, $meeting->latitude)) {
                return false;
            }
        }
        if ($this->longitude != $meeting->longitude) {
            if (is_null($this->longitude) || is_null($meeting->longitude)) {
                return false;
            } else if (!$this->floatsAreEqual($this->longitude, $meeting->longitude)) {
                return false;
            }
        }
        if ($this->published != (bool)$meeting->published) {
            return false;
        }

        $formatSourceIds = collect(explode(',', $meeting->formats ?? ''))
            ->map(fn ($id) => $formatSharedIdToSourceIdMap->get(intval($id)))
            ->reject(fn ($format) => is_null($format))
            ->sort()
            ->unique()
            ->values()
            ->toArray();

        if ($this->formatIds != $formatSourceIds) {
            return false;
        }

        // data tables
        $meetingData = $meeting->data
            ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])
            ->toBase()
            ->merge(
                $meeting->longdata
                    ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])
                    ->toBase()
            );

        if ($this->name != $meetingData->get('meeting_name')) {
            return false;
        }
        if ($this->comments != $meetingData->get('comments')) {
            return false;
        }
        if ($this->virtualMeetingAdditionalInfo != $meetingData->get('virtual_meeting_additional_info')) {
            return false;
        }
        if ($this->virtualMeetingLink != $meetingData->get('virtual_meeting_link')) {
            return false;
        }
        if ($this->phoneMeetingNumber != $meetingData->get('phone_meeting_number')) {
            return false;
        }
        if ($this->locationCitySubsection != $meetingData->get('location_city_subsection')) {
            return false;
        }
        if ($this->locationNation != $meetingData->get('location_nation')) {
            return false;
        }
        if ($this->locationPostalCode1 != $meetingData->get('location_postal_code_1')) {
            return false;
        }
        if ($this->locationProvince != $meetingData->get('location_province')) {
            return false;
        }
        if ($this->locationSubProvince != $meetingData->get('location_sub_province')) {
            return false;
        }
        if ($this->locationMunicipality != $meetingData->get('location_municipality')) {
            return false;
        }
        if ($this->locationNeighborhood != $meetingData->get('location_neighborhood')) {
            return false;
        }
        if ($this->locationStreet != $meetingData->get('location_street')) {
            return false;
        }
        if ($this->locationInfo != $meetingData->get('location_info')) {
            return false;
        }
        if ($this->locationText != $meetingData->get('location_text')) {
            return false;
        }
        if ($this->busLines != $meetingData->get('bus_lines')) {
            return false;
        }
        if ($this->trainLines != $meetingData->get('train_lines')) {
            return false;
        }

        return true;
    }

    private function floatsAreEqual(float $a, float $b): bool
    {
        return abs($a - $b) < PHP_FLOAT_EPSILON;
    }

    protected function throwInvalidObjectException(): void
    {
        throw new InvalidMeetingException();
    }
}
