<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\MeetingResource;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Meeting;
use App\Models\MeetingData;
use App\Repositories\ServiceBodyRepository;
use App\Rules\VenueTypeLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeetingController extends ResourceController
{
    private FormatRepositoryInterface $formatRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private ServiceBodyRepository $serviceBodyRepository;

    public function __construct(FormatRepositoryInterface $formatRepository, MeetingRepositoryInterface $meetingRepository, ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->formatRepository = $formatRepository;
        $this->meetingRepository = $meetingRepository;
        $this->serviceBodyRepository = $serviceBodyRepository;
        $this->authorizeResource(Meeting::class);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $serviceBodyIds = null;
        if (!$user->isAdmin()) {
            $serviceBodyIds = $this->serviceBodyRepository->getUserServiceBodyIds($user->id_bigint)->toArray();
        }
        $meetings = $this->meetingRepository->getSearchResults(servicesInclude: $serviceBodyIds);
        return MeetingResource::collection($meetings);
    }

    public function show(Meeting $meeting)
    {
        return new MeetingResource($meeting);
    }

    public function store(Request $request)
    {
        $values = $this->validateInputsAndCreateValuesArray($request);
        $meeting = $this->meetingRepository->create($values);
        return new MeetingResource($meeting);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $values = $this->validateInputsAndCreateValuesArray($request);
        $this->meetingRepository->update($meeting->id_bigint, $values);
        return response()->noContent();
    }

    public function partialUpdate(Request $request, Meeting $meeting)
    {
        $meetingData = $meeting->data
            ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])
            ->toBase()
            ->merge(
                $meeting->longdata
                    ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])
                    ->toBase()
            );

        $dataTemplateKeys = $this->meetingRepository->getDataTemplates()
            ->map(fn ($template, $_) => $template->key);

        $mergedInputs = collect(Meeting::$mainFields)->merge($dataTemplateKeys)
            ->reject(fn ($fieldName) => $fieldName == 'id_bigint' || $fieldName == 'time_zone' || $fieldName == 'lang_enum')
            ->mapWithKeys(function ($fieldName, $_) use ($request, $meeting, $meetingData) {
                if ($fieldName == 'service_body_bigint') {
                    return ['serviceBodyId' => $request->has('serviceBodyId') ? $request->input('serviceBodyId') : $meeting->service_body_bigint];
                } elseif ($fieldName == 'formats') {
                    return ['formatIds' => $request->has('formatIds') ? $request->input('formatIds') : (empty($this->formats) ? collect([]) : collect(explode(',', $meeting->formats))->map(fn ($id) => intval($id))->toArray())];
                } elseif ($fieldName == 'venue_type') {
                    return ['venueType' => $request->has('venueType') ? $request->input('venueType') : $meeting->venue_type];
                } elseif ($fieldName == 'weekday_tinyint') {
                    return ['day' => $request->has('day') ? $request->input('day') : $meeting->weekday_tinyint];
                } elseif ($fieldName == 'start_time') {
                    return ['startTime' => $request->has('startTime') ? $request->input('startTime') : (empty($meeting->start_time) ? null : (\DateTime::createFromFormat('H:i:s', $meeting->start_time) ?: \DateTime::createFromFormat('H:i', $meeting->start_time))->format('H:i'))];
                } elseif ($fieldName == 'duration_time') {
                    return ['duration' => $request->has('duration') ? $request->input('duration') : (empty($meeting->duration_time) ? null : (\DateTime::createFromFormat('H:i:s', $meeting->duration_time) ?: \DateTime::createFromFormat('H:i', $meeting->duration_time))->format('H:i'))];
                } elseif ($fieldName == 'published') {
                    return ['published' => $request->has('published') ? $request->input('published') : ($meeting->published === 1)];
                } elseif ($fieldName == 'email_contact') {
                    return ['email' => $request->has('email') ? $request->input('email') : $meeting->email_contact];
                } elseif ($fieldName == 'worldid_mixed') {
                    return ['worldId' => $request->has('worldId') ? $request->input('worldId') : $meeting->worldid_mixed];
                } elseif ($fieldName == 'meeting_name') {
                    return ['name' => $request->has('name') ? $request->input('name') : $meetingData->get('meeting_name')];
                } elseif (in_array($fieldName, Meeting::$mainFields)) {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $meeting->{$fieldName}];
                } else {
                    return [$fieldName => $request->has($fieldName) ? $request->input($fieldName) : $meetingData->get($fieldName)];
                }
            })
            ->toArray();

        $request->merge($mergedInputs);
        $values = $this->validateInputsAndCreateValuesArray($request);
        $this->meetingRepository->update($meeting->id_bigint, $values);
        return response()->noContent();
    }

    public function destroy(Meeting $meeting)
    {
        $this->meetingRepository->delete($meeting->id_bigint);
        return response()->noContent();
    }

    private function validateInputsAndCreateValuesArray(Request $request): array
    {
        $virtualFormatId = $this->formatRepository->getVirtualFormat()->shared_id_bigint;
        $temporarilyClosedId = $this->formatRepository->getTemporarilyClosedFormat()->shared_id_bigint;
        $hybridFormatId = $this->formatRepository->getHybridFormat()->shared_id_bigint;

        $validated = collect($request->validate([
            'serviceBodyId' => ['required', 'int', 'exists:comdef_service_bodies,id_bigint'],
            'formatIds' => 'present|array',
            'formatIds.*' => ['int', 'exists:comdef_formats,shared_id_bigint', Rule::notIn([$virtualFormatId, $temporarilyClosedId, $hybridFormatId])],
            'venueType' => ['required', Rule::in(Meeting::VALID_VENUE_TYPES)],
            'temporarilyVirtual' => 'sometimes|boolean',
            'day' => 'required|int|between:0,6',
            'startTime' => 'required|date_format:H:i',
            'duration' => 'required|date_format:H:i',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'published' => 'required|boolean',
            'email' => 'nullable|email|max:255',
            'worldId' => 'nullable|string|max:30',
            'name' => 'required|string|max:128',
            'location_text' => 'nullable|string|max:512',
            'location_info' => 'nullable|string|max:512',
            'location_street' => ['max:512', new VenueTypeLocation],
            'location_neighborhood' => 'nullable|string|max:512',
            'location_city_subsection' => 'nullable|string|max:512',
            'location_municipality' => ['max:512', new VenueTypeLocation],
            'location_sub_province' => 'nullable|string|max:512',
            'location_province' => ['max:512', new VenueTypeLocation],
            'location_postal_code_1' => ['max:512', new VenueTypeLocation],
            'location_nation' => 'nullable|string|max:512',
            'phone_meeting_number' => ['max:512', new VenueTypeLocation],
            'virtual_meeting_link' => ['max:512', new VenueTypeLocation],
            'virtual_meeting_additional_info' => 'nullable|string|max:512',
            'contact_name_1' => 'nullable|string|max:512',
            'contact_name_2' => 'nullable|string|max:512',
            'contact_phone_1' => 'nullable|string|max:512',
            'contact_phone_2' => 'nullable|string|max:512',
            'contact_email_1' => 'nullable|string|max:512',
            'contact_email_2' => 'nullable|string|max:512',
            'bus_lines' => 'nullable|string|max:512',
            'train_lines' => 'nullable|string|max:512',
            'comments' => 'nullable|string|max:512',
        ]))->merge(collect($request->validate(
            $this->meetingRepository->getDataTemplates()
                ->mapWithKeys(fn ($template, $_) => [$template->key => 'nullable|string|max:512'])
                ->reject(fn ($_, $fieldName) => in_array($fieldName, MeetingData::STOCK_FIELDS))
                ->toArray()
        )));

        return $validated->mapWithKeys(function ($value, $fieldName) use ($validated, $virtualFormatId, $temporarilyClosedId, $hybridFormatId) {
            if ($fieldName == 'serviceBodyId') {
                return ['service_body_bigint' => $value];
            } elseif ($fieldName == 'formatIds') {
                $temporarilyVirtual = boolval($validated['temporarilyVirtual'] ?? false);
                $venueType = $validated->get('venueType');
                if ($venueType == Meeting::VENUE_TYPE_VIRTUAL) {
                    array_push($value, $virtualFormatId);
                    if ($temporarilyVirtual) {
                        array_push($value, $temporarilyClosedId);
                    }
                } elseif ($venueType == Meeting::VENUE_TYPE_HYBRID) {
                    array_push($value, $hybridFormatId);
                }
                return ['formats' => collect($value)->sort()->unique()->join(',')];
            } elseif ($fieldName == 'venueType') {
                return ['venue_type' => $value];
            } elseif ($fieldName == 'day') {
                return ['weekday_tinyint' => $value];
            } elseif ($fieldName == 'startTime') {
                return ['start_time' => $value];
            } elseif ($fieldName == 'duration') {
                return ['duration_time' => $value];
            } elseif ($fieldName == 'published') {
                return ['published' => $value ? 1 : 0];
            } elseif ($fieldName == 'email') {
                return ['email_contact' => $value];
            } elseif ($fieldName == 'worldId') {
                return ['worldid_mixed' => $value];
            } elseif ($fieldName == 'name') {
                return ['meeting_name' => $value];
            } else {
                return [$fieldName => $value];
            }
        })->toArray();
    }
}
