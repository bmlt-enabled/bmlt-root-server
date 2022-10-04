<?php

namespace App\Rules;

use App\Models\Meeting;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

class VenueTypeLocation implements DataAwareRule, InvokableRule, ValidatorAwareRule
{
    public const FIELDS = [
        'location_street',
        'location_municipality',
        'location_province',
        'location_postal_code_1',
        'phone_meeting_number',
        'virtual_meeting_link'
    ];

    public $implicit = true;

    protected array $data = [];
    protected Validator $validator;

    public function __invoke($attribute, $value, $fail)
    {
        $validated = $this->validator->safe()->only(['venueType']);
        if (!array_key_exists('venueType', $validated)) {
            $fail('The venueType was not provided.');
            return;
        }

        $venueType = $validated['venueType'];
        $data = collect($this->data);
        $street = $data->get('location_street') ?: null;
        $city = $data->get('location_municipality') ?: null;
        $state = $data->get('location_province') ?: null;
        $zip = $data->get('location_postal_code_1') ?: null;
        $url = $data->get('virtual_meeting_link') ?: null;
        $phone = $data->get('phone_meeting_number') ?: null;

        $isInPerson = $venueType == Meeting::VENUE_TYPE_IN_PERSON;
        $isVirtual = $venueType == Meeting::VENUE_TYPE_VIRTUAL;
        $isHybrid = $venueType == Meeting::VENUE_TYPE_HYBRID;
        $meetingType = $isInPerson ? 'In-person' : ($isVirtual ? 'Virtual' : 'Hybrid');

        if ($isInPerson || $isHybrid) {
            if ($attribute == 'location_street') {
                if (!$street) {
                    $fail("$meetingType meetings must have a street address.");
                }
            }

            if ($attribute == 'location_municipality' || $attribute == 'location_province' || $attribute == 'location_postal_code_1') {
                if (!($city && $state) && !$zip) {
                    $fail("$meetingType meetings must have a location (at least a city/town and state/province, or a zip/postal code).");
                }
            }
        }

        if ($isVirtual || $isHybrid) {
            if ($attribute == 'virtual_meeting_link' || $attribute == 'phone_meeting_number') {
                if (!$url && !$phone) {
                    $fail("$meetingType meetings must include a Virtual Meeting Link or a Phone Meeting Dial-in Number.");
                }
            }
        }
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }
}
