<?php

namespace App\Http\Resources\Query;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TsmlMeetingResource extends JsonResource
{
    protected mixed $formatsById;

    public function __construct($resource, $formatsById = [])
    {
        parent::__construct($resource);
        $this->formatsById = $formatsById;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $formatsById = $this->formatsById;
        // Mapping formats to TSML types.
        // Servers may use arbitrary `key_string` values for formats, which are not guaranteed to be unique.
        // To ensure consistency, we map format IDs to their `worldid_mixed` codes (which are unique and standardized),
        // and then map those to TSML type codes using the table below.
        // WORLD_ID -> TSML
        $worldIdToTsmlTypes = [
            'CPT' => 'CPT',
            'BT' => 'BT',
            'BEG' => 'BEG',
            'CAN' => 'CAN',
            'CW' => 'CW',
            'CLOSED' => 'C',
            'DISC' => 'DISC',
            'GL' => 'GL',
            'IP' => 'IP',
            'IW' => 'IW',
            'JFT' => 'JFT',
            'LIT' => 'LIT',
            'LC' => 'LC',
            'TC' => 'TC',
            'M' => 'M',
            'MED' => 'MED',
            'NS' => 'NS',
            'VM' => 'ONL',
            'OPEN' => 'O',
            'QA' => 'QA',
            'RA' => 'RA',
            'SMOK' => 'SMOK',
            'SPAD' => 'SPAD',
            'SPK' => 'SPK',
            'STEP' => 'STEP',
            'SWG' => 'SWG',
            'TOP' => 'TOP',
            'TRAD' => 'TRAD',
            'VAR' => 'VAR',
            'WCHR' => 'X',
            'W' => 'W',
            'Y' => 'Y',
        ];

        $data = $this->data->mapWithKeys(fn ($d) => [$d->key => $d->data_string])->toArray();
        $longdata = $this->longdata->mapWithKeys(fn ($d) => [$d->key => $d->data_blob])->toArray();
        $allData = array_merge($data, $longdata);

        $meetingFormats = explode(',', $this->formats ?? '');
        $virtualOnly = in_array('VM', $meetingFormats) && !in_array('HY', $meetingFormats);

        return [
            'day' => isset($this->weekday_tinyint) ? intval($this->weekday_tinyint)  : null,
            'time' => $this->start_time ? substr($this->start_time, 0, 5) : null,
            'end_time' => (!empty($this->start_time) && !empty($this->duration_time)) ? TsmlMeetingResource::addTimes($this->start_time, $this->duration_time) : null,
            'name' => $allData['meeting_name'] ?? '',
            'location' => $virtualOnly ? 'Online Meeting' : ($allData['location_text'] ?? ''),
            'formatted_address' => collect(['location_street', 'location_municipality', 'location_province', 'location_postal_code_1', 'location_nation'])->map(fn($key) => $data[$key] ?? '')->filter()->implode(', '),
            'address' => $allData['location_street'] ?? '',
            'city' => $allData['location_municipality'] ?? '',
            'state' => $allData['location_province'] ?? '',
            'postal_code' => $allData['location_postal_code_1'] ?? '',
            'country' => $allData['location_nation'] ?? '',
            'types' => collect($this->formats ? explode(',', $this->formats) : [])
                ->map(fn($id) => intval($id))
                ->filter(fn($id) => isset($formatsById[$id]) && !empty($formatsById[$id]->worldid_mixed))
                ->map(fn($id) => $worldIdToTsmlTypes[$formatsById[$id]->worldid_mixed] ?? null)
                ->filter()
                ->values()
                ->toArray(),
            'notes' => $allData['location_info'] ?? '',
            'coordinates' => (!empty($this->latitude) && !empty($this->longitude)) ? "{$this->latitude},{$this->longitude}" : null,
            'slug' => \Str::slug($allData['meeting_name']),
            'updated' => $this->updated_at ?? null,
            'region' => $this->serviceBody->name_string ?? '',
            'regions' => [$this->serviceBody->name_string ?? ''],
            'conference_url' => $allData['virtual_meeting_link'] ?? null,
            'conference_url_notes' => $allData['virtual_meeting_additional_info'] ?? null,
            'conference_phone' => $allData['phone_meeting_number'] ?? null,
            'conference_phone_notes' => $allData['phone_meeting_additional_info'] ?? null,
        ];
    }

    /**
     * Create a collection of resources with formatsById mapping.
     *
     * Note: We pass in $formatsById separately because the $meeting objects themselves
     * only contain format IDs (key_strings), and not the full format metadata. This metadata,
     * including `worldid_mixed`, is necessary for mapping to TSML types,
     * and is not available directly on the meeting objects.
     *
     * @param  mixed  $resource
     * @param  array  $formatsById
     * @return \Illuminate\Support\Collection
     */
    public static function collection($resource, $formatsById = [])
    {
        return collect($resource)->map(function ($meeting) use ($formatsById) {
            return (new static($meeting, $formatsById))->toArray(request());
        })->values();
    }

    /**
     * Adds a start time and a duration (both as strings in H:i or H:i:s format) and returns the end time in H:i format.
     *
     * @param string $start    The start time (H:i or H:i:s)
     * @param string $duration The duration to add (H:i or H:i:s)
     * @return string|null     The calculated end time in H:i format, or null if input is invalid
     */
    public static function addTimes(string $start, string $duration): ?string
    {
        $startTime = Carbon::createFromFormat(strlen($start) === 5 ? 'H:i' : 'H:i:s', $start);
        $durationTime = Carbon::createFromFormat(strlen($duration) === 5 ? 'H:i' : 'H:i:s', $duration);

        $endTime = $startTime->copy()
            ->addHours((int) $durationTime->format('H'))
            ->addMinutes((int) $durationTime->format('i'))
            ->addSeconds((int) $durationTime->format('s'));

        return $endTime->format('H:i');
    }
}
