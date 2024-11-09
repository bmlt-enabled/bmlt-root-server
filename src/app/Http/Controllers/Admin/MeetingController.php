<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\MeetingResource;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Meeting;
use App\Repositories\ServiceBodyRepository;
use App\Rules\IANATimeZone;
use App\Rules\VenueTypeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Spatie\ValidationRules\Rules\Delimited;

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
        $validated = $request->validate([
            'meetingIds' => [new Delimited('int|exists:comdef_meetings_main,id_bigint')],
            'days' => [new Delimited('int|between:0,6')],
            'serviceBodyIds' => [new Delimited('int|exists:comdef_service_bodies,id_bigint')],
            'searchString' => 'string|min:3',
        ]);

        $days = !empty($validated['days']) ? array_map(fn ($v) => intval($v), explode(',', $validated['days'])) : null;
        $meetingIds = !empty($validated['meetingIds']) ? array_map(fn ($v) => intval($v), explode(',', $validated['meetingIds'])) : null;
        $serviceBodyIds = !empty($validated['serviceBodyIds']) ? array_map(fn ($v) => intval($v), explode(',', $validated['serviceBodyIds'])) : null;
        $searchString = $validated['searchString'] ?? null;

        $user = $request->user();
        if (!$user->isAdmin()) {
            $allowedServiceBodyIds = $this->serviceBodyRepository->getAssignedServiceBodyIds($user->id_bigint)->toArray();
            $serviceBodyIds = is_null($serviceBodyIds) ? $allowedServiceBodyIds : array_intersect($serviceBodyIds, $allowedServiceBodyIds);
        }

        $meetings = $this->meetingRepository->getSearchResults(
            meetingIds: $meetingIds,
            weekdaysInclude: $days,
            servicesInclude: $serviceBodyIds,
            searchString: $searchString,
        );

        return MeetingResource::collection($meetings);
    }

    public function show(Meeting $meeting)
    {
        return new MeetingResource($meeting);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArray($validated);
        $meeting = $this->meetingRepository->create($values);
        return new MeetingResource($meeting);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArray($validated);
        $this->meetingRepository->update($meeting->id_bigint, $values);
        return response()->noContent();
    }

    public function partialUpdate(Request $request, Meeting $meeting)
    {
        $meetingData = $meeting->data
            ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])
            ->toBase()
            ->merge($meeting->longdata->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])->toBase());

        $customFields = $this->getCustomFields();
        $stockDataFields = $this->getDataTemplates()
            ->reject(fn ($_, $key) => $customFields->contains($key))
            ->map(fn ($_, $key) => $key);

        // Since a patch is only a partial representation of the meeting, we fill in all of the gaps
        // with data from the actual meeting. This allows us to share code with the store and update
        // (POST and PUT) handlers.
        $request->merge(
            collect(Meeting::$mainFields)
                ->merge($stockDataFields)
                ->mapWithKeys(function ($fieldName) use ($request, $meeting, $meetingData) {
                    if ($fieldName == 'service_body_bigint') {
                        return ['serviceBodyId' => $request->has('serviceBodyId') ? $request->input('serviceBodyId') : $meeting->service_body_bigint];
                    } elseif ($fieldName == 'formats') {
                        return ['formatIds' => $request->has('formatIds') ? $request->input('formatIds') : (empty($meeting->formats) ? collect([]) : collect(explode(',', $meeting->formats))->map(fn ($id) => intval($id))->reject(fn ($id) => $id == $this->getVirtualFormatId() || $id == $this->getHybridFormatId() || $id == $this->getTemporarilyClosedFormatId())->toArray())];
                    } elseif ($fieldName == 'venue_type') {
                        return ['venueType' => $request->has('venueType') ? $request->input('venueType') : $meeting->venue_type];
                    } elseif ($fieldName == 'weekday_tinyint') {
                        return ['day' => $request->has('day') ? $request->input('day') : $meeting->weekday_tinyint];
                    } elseif ($fieldName == 'time_zone') {
                        return ['timeZone' => $request->has('timeZone') ? $request->input('timeZone') : $meeting->time_zone];
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
                ->merge([
                    "customFields" => $customFields->mapWithKeys(
                        function ($fieldName) use ($request, $meetingData) {
                            $customFields = $request->input('customFields', []);
                            return [$fieldName => $customFields[$fieldName] ?? $meetingData->get($fieldName)];
                        }
                    )
                    ->toArray()
                ])
                ->toArray()
        );

        $validated = $this->validateInputs($request);
        $values = $this->buildValuesArray($validated);
        $this->meetingRepository->update($meeting->id_bigint, $values);
        return response()->noContent();
    }

    public function destroy(Meeting $meeting)
    {
        $this->meetingRepository->delete($meeting->id_bigint);
        return response()->noContent();
    }

    private function getDataTemplates(): Collection
    {
        static $dataTemplates = null;
        if (is_null($dataTemplates) || App::runningUnitTests()) {
            $dataTemplates = $this->meetingRepository->getDataTemplates();
        }
        return $dataTemplates;
    }

    private function getCustomFields(): Collection
    {
        static $customFields = null;
        if (is_null($customFields) || App::runningUnitTests()) {
            $customFields = $this->meetingRepository->getCustomFields();
        }
        return $customFields;
    }

    private function validateInputs(Request $request)
    {
        return collect($request->validate(
            array_merge([
                'serviceBodyId' => 'required|int|exists:comdef_service_bodies,id_bigint',
                'formatIds' => 'present|array',
                'formatIds.*' => ['int', 'exists:comdef_formats,shared_id_bigint', Rule::notIn([$this->getVirtualFormatId(), $this->getTemporarilyClosedFormatId(), $this->getHybridFormatId()])],
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
                'timeZone' => ['nullable', 'string', 'max:40', new IANATimeZone],
            ], $this->getDataFieldValidators())
        ));
    }

    private function getDataFieldValidators(): array
    {
        $customFields = $this->getCustomFields();
        return $this->getDataTemplates()
            ->reject(fn ($_, $fieldName) => $fieldName == 'meeting_name' || $customFields->contains($fieldName))
            ->mapWithKeys(function ($_, $fieldName) {
                if (in_array($fieldName, VenueTypeLocation::FIELDS)) {
                    return [$fieldName => ['max:512', new VenueTypeLocation]];
                } else {
                    return [$fieldName => 'nullable|string|max:512'];
                }
            })
            ->merge([
                'customFields' => 'array:' . $this->getCustomFields()->join(','),
                'customFields.*' => 'nullable|string|max:512',
            ])
            ->toArray();
    }

    private function getVirtualFormatId(): int
    {
        static $id = null;
        if (is_null($id)) {
            $id = $this->formatRepository->getVirtualFormat()->shared_id_bigint;
        }
        return $id;
    }

    private function getHybridFormatId(): int
    {
        static $id = null;
        if (is_null($id)) {
            $id = $this->formatRepository->getHybridFormat()->shared_id_bigint;
        }
        return $id;
    }

    private function getTemporarilyClosedFormatId(): int
    {
        static $id = null;
        if (is_null($id)) {
            $id = $this->formatRepository->getTemporarilyClosedFormat()->shared_id_bigint;
        }
        return $id;
    }

    private function buildFormatsString(Collection $validated): string
    {
        $formatIds = $validated['formatIds'];
        $temporarilyVirtual = boolval($validated['temporarilyVirtual'] ?? false);
        $venueType = $validated['venueType'];
        if ($venueType == Meeting::VENUE_TYPE_VIRTUAL) {
            array_push($formatIds, $this->getVirtualFormatId());
            if ($temporarilyVirtual) {
                array_push($formatIds, $this->getTemporarilyClosedFormatId());
            }
        } elseif ($venueType == Meeting::VENUE_TYPE_HYBRID) {
            array_push($formatIds, $this->getHybridFormatId());
        }
        return collect($formatIds)->sort()->unique()->join(',');
    }

    private function buildValuesArray(Collection $validated): array
    {
        $values = [
            'service_body_bigint' => $validated['serviceBodyId'],
            'formats' => $this->buildFormatsString($validated),
            'venue_type' => $validated['venueType'],
            'weekday_tinyint' => $validated['day'],
            'time_zone' => $validated['timeZone'],
            'start_time' => \DateTime::createFromFormat('H:i', $validated['startTime'])->format('H:i:s'),
            'duration_time' => \DateTime::createFromFormat('H:i', $validated['duration'])->format('H:i:s'),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'published' => $validated['published'] ? 1 : 0,
            'email_contact' => $validated['email'] ?? null,
            'worldid_mixed' => $validated['worldId'] ?? null,
            'meeting_name' => $validated['name'],
        ];

        $customFields = $this->getCustomFields();

        return collect($values)
            ->merge(
                $this->getDataTemplates()
                    ->reject(fn ($_, $fieldName) => $fieldName == 'meeting_name' || $customFields->contains($fieldName))
                    ->mapWithKeys(fn ($_, $fieldName) => $validated->has($fieldName) ? [$fieldName => $validated[$fieldName]] : [null => null])
                    ->reject(fn ($value, $_) => is_null($value))
            )
            ->merge(
                $customFields
                    ->mapWithKeys(fn ($fieldName) => $validated->has("customFields") && array_key_exists($fieldName, $validated["customFields"]) ? [$fieldName => $validated["customFields"][$fieldName]] : [null => null])
                    ->reject(fn ($value, $_) => is_null($value))
            )
            ->toArray();
    }
}
