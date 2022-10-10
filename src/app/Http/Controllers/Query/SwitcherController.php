<?php

namespace App\Http\Controllers\Query;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Legacy\LegacyController;
use App\Http\Resources\Query\FormatResource;
use App\Http\Resources\Query\MeetingResource;
use App\Http\Resources\Query\MeetingChangeResource;
use App\Http\Resources\Query\ServiceBodyResource;
use App\Http\Responses\JsonResponse;
use App\Interfaces\ChangeRepositoryInterface;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\MigrationRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Change;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse as BaseJsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SwitcherController extends Controller
{
    private ChangeRepositoryInterface $changeRepository;
    private FormatRepositoryInterface $formatRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private MigrationRepositoryInterface $migrationRepository;
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(
        ChangeRepositoryInterface $changeRepository,
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository,
        MigrationRepositoryInterface $migrationRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository
    ) {
        $this->changeRepository = $changeRepository;
        $this->formatRepository = $formatRepository;
        $this->meetingRepository = $meetingRepository;
        $this->migrationRepository = $migrationRepository;
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function get(Request $request, string $dataFormat)
    {
        $switcher = $request->input('switcher');

        $response = null;

        $validValues = ['GetSearchResults', 'GetFormats', 'GetServiceBodies', 'GetFieldKeys', 'GetFieldValues', 'GetChanges', 'GetServerInfo', 'GetCoverageArea', 'GetNAWSDump'];
        if (in_array($switcher, $validValues)) {
            if ($switcher == 'GetNAWSDump' && $dataFormat == 'csv') {
                return LegacyController::handle($request);
                $response = $this->getNawsDump($request);
            } elseif ($dataFormat == 'json' || $dataFormat == 'jsonp') {
                if ($switcher == 'GetSearchResults') {
                    $response = $this->getSearchResults($request);
                } elseif ($switcher == 'GetFormats') {
                    $response = $this->getFormats($request);
                } elseif ($switcher == 'GetServiceBodies') {
                    $response = $this->getServiceBodies($request);
                } elseif ($switcher == 'GetFieldKeys') {
                    $response = $this->getFieldKeys($request);
                } elseif ($switcher == 'GetFieldValues') {
                    $response = $this->getFieldValues($request);
                } elseif ($switcher == 'GetChanges') {
                    $response = $this->getMeetingChanges($request);
                } elseif ($switcher == 'GetServerInfo') {
                    $response = $this->getServerInfo($request);
                } else {
                    $response = $this->getCoverageArea($request);
                }

                if ($dataFormat == 'jsonp') {
                    $response = $response->withCallback($request->input('callback', 'callback'));
                }
            }
        }

        if (is_null($response)) {
            $validJsonValues = collect($validValues)->reject(fn ($v) => $v == 'GetNAWSDump')->join(', ');
            abort(422, "Invalid data format or endpoint name. Valid endpoint names for the json and jsonp data formats are: $validJsonValues. Valid endpoint names for the csv format are: GetNAWSDump.");
        }

        return $response;
    }

    private function getSearchResults(Request $request): BaseJsonResponse
    {
        $meetingIds = $request->input('meeting_ids');
        $meetingIds = !is_null($meetingIds) ? ensure_integer_array($meetingIds) : null;

        $weekdays = $request->input('weekdays', []);
        $weekdays = ensure_integer_array($weekdays);
        $weekdaysInclude = collect($weekdays)->filter(fn ($w) => $w > 0)->map(fn ($w) => $w - 1)->toArray();
        $weekdaysInclude = count($weekdaysInclude) ? $weekdaysInclude : null;
        $weekdaysExclude = collect($weekdays)->filter(fn ($w) => $w < 0)->map(fn ($w) => abs($w) - 1)->toArray();
        $weekdaysExclude = count($weekdaysExclude) ? $weekdaysExclude : null;

        $venueTypes = $request->input('venue_types', []);
        $venueTypes = ensure_integer_array($venueTypes);
        $venueTypesInclude = collect($venueTypes)->filter(fn ($v) => $v > 0)->toArray();
        $venueTypesInclude = !empty($venueTypesInclude) ? $venueTypesInclude : null;
        $venueTypesExclude = collect($venueTypes)->filter(fn ($v) => $v < 0)->map(fn ($v) => abs($v))->toArray();
        $venueTypesExclude = !empty($venueTypesExclude) ? $venueTypesExclude : null;

        $recursive = $request->input('recursive', '0') == '1';
        $services = $request->input('services', []);
        $services = ensure_integer_array($services);
        $servicesInclude = collect($services)->filter(fn ($s) => $s > 0)->toArray();
        $servicesInclude = $recursive ? $this->serviceBodyRepository->getChildren($servicesInclude) : $servicesInclude;
        $servicesInclude = !empty($servicesInclude) ? $servicesInclude : null;
        $servicesExclude = collect($services)->filter(fn ($s) => $s < 0)->map(fn ($s) => abs($s))->toArray();
        $servicesExclude = $recursive ? $this->serviceBodyRepository->getChildren($servicesExclude) : $servicesExclude;
        $servicesExclude = !empty($servicesExclude) ? $servicesExclude : null;

        $formats = $request->input('formats', []);
        $formats = ensure_integer_array($formats);
        $formatsInclude = collect($formats)->filter(fn ($f) => $f > 0)->toArray();
        $formatsInclude = !empty($formatsInclude) ? $formatsInclude : null;
        $formatsExclude = collect($formats)->filter(fn ($s) => $s < 0)->map(fn ($s) => abs($s))->toArray();
        $formatsExclude = !empty($formatsExclude) ? $formatsExclude : null;
        $formatsComparisonOperator = $request->input('formats_comparison_operator', 'AND');
        $formatsComparisonOperator = strtoupper($formatsComparisonOperator) == 'AND' ? 'AND' : 'OR';

        $meetingKey = $request->input('meeting_key');
        $meetingKeyValue = null;
        if (!is_null($meetingKey)) {
            $meetingKeyValue = $request->input('meeting_key_value');
            if (!is_null($meetingKeyValue)) {
                if ($meetingKey == 'weekday_tinyint') {
                    $meetingKeyValue = strval(intval($meetingKeyValue) - 1);
                } elseif ($meetingKey == 'start_time' || $meetingKey == 'duration_time') {
                    $timePieces = explode(':', $meetingKeyValue);
                    if (count($timePieces) >= 2) {
                        $meetingKeyValue = build_time_string($timePieces[0], $timePieces[1]);
                    }
                }
            }
            if (is_null($meetingKeyValue)) {
                return new JsonResponse([]);
            }
        }

        $startsAfter = build_time_string($request->input('StartsAfterH'), $request->input('StartsAfterM'));
        $startsBefore = build_time_string($request->input('StartsBeforeH'), $request->input('StartsBeforeM'));
        $endsBefore = build_time_string($request->input('EndsBeforeH'), $request->input('EndsBeforeM'));
        $minDuration = build_time_string($request->input('MinDurationH'), $request->input('MinDurationM'));
        $maxDuration = build_time_string($request->input('MaxDurationH'), $request->input('MaxDurationM'));

        $latitude = $request->input('lat_val');
        $latitude = is_numeric($latitude) ? floatval($latitude) : null;
        $longitude = $request->input('long_val');
        $longitude = is_numeric($longitude) ? floatval($longitude) : null;
        $geoWidthMiles = null;
        $geoWidthKilometers = null;
        $sortResultsByDistance = false;
        $needsDistanceField = false;
        if (!is_null($latitude) || !is_null($longitude)) {
            $geoWidthMiles = $request->input('geo_width');
            $geoWidthMiles = is_numeric($geoWidthMiles) ? floatval($geoWidthMiles) : null;
            $geoWidthKilometers = $request->input('geo_width_km');
            $geoWidthKilometers = is_numeric($geoWidthKilometers) ? floatval($geoWidthKilometers) : null;
            $sortResultsByDistance = $request->input('sort_results_by_distance') == '1';
            $dataFieldKeys = $request->input('data_field_key');
            $dataFieldKeys = !is_null($dataFieldKeys) ? explode(',', $dataFieldKeys) : [];
            $needsDistanceField = in_array('distance_in_miles', $dataFieldKeys) || in_array('distance_in_km', $dataFieldKeys);
            if (is_null($latitude) || is_null($longitude) || (is_null($geoWidthMiles) && is_null($geoWidthKilometers) && !$needsDistanceField)) {
                return new JsonResponse([]);
            }
        }

        $searchString = $request->input('SearchString');
        $searchString = !is_null($searchString) ? trim($searchString) : null;
        $searchString = strlen($searchString) > 2 || is_numeric($searchString) ? $searchString : null;
        $searchStringIsAddress = !is_null($searchString) && $request->input('StringSearchIsAnAddress') == '1';
        if (!is_null($searchString) && $searchStringIsAddress) {
            $googleApiKey = legacy_config('google_api_key');
            if (is_null($googleApiKey)) {
                abort(400, 'A google api key must be configured to use StringSearchIsAnAddress.');
            }

            $searchStringRadius = $request->input('SearchStringRadius');
            if (is_null($searchStringRadius) || !is_numeric($searchStringRadius)) {
                abort(400, 'SearchStringRadius is required to use SearchStringIsAnAddress.');
            }

            $regionBias = legacy_config('region_bias');
            if (is_string($regionBias) && is_numeric($searchString)) {
                // when it's numeric, like a postcode, add $regionBias directly
                $searchString .= ' ' . $regionBias;
            }

            $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?key=$googleApiKey&address=$searchString&sensor=false";
            if (is_string($regionBias)) {
                $geocodeUrl .= "&region=$regionBias";
            }

            $geocodeResponse = Http::get($geocodeUrl);
            $genericError = 'There was a problem geocoding the SearchString.';
            if (!$geocodeResponse->ok()) {
                abort(500, $genericError);
            }

            $geocodeResponse = $geocodeResponse->json();
            if (!isset($geocodeResponse['status']) || $geocodeResponse['status'] != 'OK') {
                $errorMessage = $geocodeResponse['error_message'] ?? $genericError;
                abort(500, $errorMessage);
            }

            try {
                $latitude = $geocodeResponse['results'][0]['geometry']['location']['lat'];
                $longitude = $geocodeResponse['results'][0]['geometry']['location']['lng'];
            } catch (Exception) {
                abort(500, 'There was a problem parsing the geocoding response.');
            }

            $searchString = null;
            $geoWidthMiles = legacy_config('distance_units') == 'mi' ? floatval($searchStringRadius) : null;
            $geoWidthKilometers = legacy_config('distance_units') != 'mi' ? floatval($searchStringRadius) : null;
        }

        $published = $request->input('advanced_published', '1');
        if ($published == '1') {
            $published = true;
        } elseif ($published == '0') {
            $published = null;
        } else {
            $published = false;
        }

        $sortKeys = $request->input('sort_keys');
        $sortKeys = $sortKeys && $sortKeys != '' ? explode(',', $sortKeys) : ['lang_enum', 'weekday_tinyint', 'start_time', 'id_bigint'];

        $pageSize = $request->input('page_size');
        $pageSize = is_numeric($pageSize) ? intval($pageSize) : null;
        $pageNum = null;
        if (!is_null($pageSize)) {
            $pageNum = $request->input('page_num');
            $pageNum = is_numeric($pageNum) ? intval($pageNum) : 1;
        }

        $meetings = $this->meetingRepository->getSearchResults(
            meetingIds: $meetingIds,
            weekdaysInclude: $weekdaysInclude,
            weekdaysExclude: $weekdaysExclude,
            venueTypesInclude: $venueTypesInclude,
            venueTypesExclude: $venueTypesExclude,
            servicesInclude: $servicesInclude,
            servicesExclude: $servicesExclude,
            formatsInclude: $formatsInclude,
            formatsExclude: $formatsExclude,
            formatsComparisonOperator: $formatsComparisonOperator,
            meetingKey: $meetingKey,
            meetingKeyValue: $meetingKeyValue,
            startsAfter: $startsAfter,
            startsBefore: $startsBefore,
            endsBefore: $endsBefore,
            minDuration: $minDuration,
            maxDuration: $maxDuration,
            latitude: $latitude,
            longitude: $longitude,
            geoWidthMiles: $geoWidthMiles,
            geoWidthKilometers: $geoWidthKilometers,
            needsDistanceField: $needsDistanceField,
            sortResultsByDistance: $sortResultsByDistance,
            searchString: $searchString,
            published: $published,
            sortKeys: $sortKeys,
            pageSize: $pageSize,
            pageNum: $pageNum,
        );

        // This code to calculate the formats fields is really inefficient, but necessary because
        // we don't have foreign keys between the meetings and formats tables.
        $langEnum = $request->input('lang_enum', config('app.locale'));
        $formats = $this->formatRepository->search(langEnums: [$langEnum], meetings: $meetings);

        $formatsById = $formats->mapWithKeys(fn ($format, $_) => [$format->shared_id_bigint => $format]);
        foreach ($meetings as $meeting) {
            $meeting->calculateFormatsFields($formatsById);
        }

        if ($request->has('get_formats_only')) {
            return new JsonResponse([
                'formats' => FormatResource::collection($formats)
            ]);
        } elseif ($request->has('get_used_formats')) {
            return new JsonResponse([
                'meetings' => MeetingResource::collection($meetings),
                'formats' => FormatResource::collection($formats)
            ]);
        }

        return MeetingResource::collection($meetings)->response();
    }

    private function getFormats(Request $request): BaseJsonResponse
    {
        $langEnums = $request->input('lang_enum', config('app.locale'));
        if (!is_array($langEnums)) {
            $langEnums = [$langEnums];
        }

        $keyStrings = $request->input('key_strings', []);
        if (!is_array($keyStrings)) {
            $keyStrings = [$keyStrings];
        }

        $showAll = $request->input('show_all') == '1';

        $formats = $this->formatRepository->search($langEnums, $keyStrings, $showAll);

        return FormatResource::collection($formats)->response();
    }

    private function getServiceBodies(Request $request): BaseJsonResponse
    {
        $serviceBodyIds = $request->input('services', []);
        if (!is_array($serviceBodyIds)) {
            $serviceBodyIds = [$serviceBodyIds];
        }

        $includeIds = [];
        $excludeIds = [];
        foreach ($serviceBodyIds as $serviceBodyId) {
            $serviceBodyId = intval($serviceBodyId);
            if ($serviceBodyId >= 0) {
                $includeIds[] = $serviceBodyId;
            } else {
                $excludeIds[] = abs($serviceBodyId);
            }
        }

        $recurseChildren = $request->input('recurse') == '1';
        $recurseParents = $request->input('parents') == '1';

        $serviceBodies = $this->serviceBodyRepository->search($includeIds, $excludeIds, $recurseChildren, $recurseParents);

        return ServiceBodyResource::collection($serviceBodies)->response();
    }

    private function getFieldKeys($request): BaseJsonResponse
    {
        $fieldKeys = $this->meetingRepository->getFieldKeys();
        return new JsonResponse($fieldKeys);
    }

    private function getFieldValues($request): BaseJsonResponse
    {
        $fieldName = $request->input('meeting_key');
        if (!$fieldName) {
            abort(400);
        }

        $validFieldNames = $this->meetingRepository->getFieldKeys();
        if (!$validFieldNames->contains('key', $fieldName)) {
            abort(400);
        }

        $specificFormats = $request->input('specific_formats');
        $specificFormats = $specificFormats ? explode(',', trim($specificFormats)) : [];
        $allFormats = (bool)$request->input('all_formats');

        $fieldValues = $this->meetingRepository->getFieldValues($fieldName, $specificFormats, $allFormats);

        return new JsonResponse($fieldValues);
    }

    private function getMeetingChanges(Request $request): BaseJsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d',
            'meeting_id' => 'numeric',
            'service_body_id' => 'numeric',
        ]);

        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        if ($endDate) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
        }
        $meetingId = $validated['meeting_id'] ?? null;
        $serviceBodyId = $validated['service_body_id'] ?? null;

        $changes = $this->changeRepository->getMeetingChanges($startDate, $endDate, $meetingId, $serviceBodyId);

        return MeetingChangeResource::collection($changes)->response();
    }

    private function getServerInfo($request): BaseJsonResponse
    {
        $versionArray = explode('.', config('app.version'));
        return new JsonResponse([[
            'version' => config('app.version'),
            'versionInt' => strval((intval($versionArray[0]) * 1000000) + (intval($versionArray[1]) * 1000) + intval($versionArray[2])),
            'langs' => collect(scandir(base_path('lang')))->reject(fn ($dir) => $dir == '.' || $dir == '..')->sort()->join(','),
            'nativeLang' => config('app.locale'),
            'centerLongitude' => strval(legacy_config('search_spec_map_center_longitude')),
            'centerLatitude' => strval(legacy_config('search_spec_map_center_latitude')),
            'centerZoom' => strval(legacy_config('search_spec_map_center_zoom')),
            'defaultDuration' => legacy_config('default_duration_time'),
            'regionBias' => legacy_config('region_bias'),
            'charSet' => 'UTF-8',
            'distanceUnits' => legacy_config('distance_units'),
            'semanticAdmin' => legacy_config('enable_semantic_admin') ? '1' : '0',
            'emailEnabled' => legacy_config('enable_email_contact') ? '1' : '0',
            'emailIncludesServiceBodies' => legacy_config('include_service_body_admin_on_emails') ? '1' : '0',
            'changesPerMeeting' => strval(legacy_config('change_depth_for_meetings')),
            'meeting_states_and_provinces' => implode(',', legacy_config('meeting_states_and_provinces', [])),
            'meeting_counties_and_sub_provinces' => implode(',', legacy_config('meeting_counties_and_sub_provinces', [])),
            'available_keys' => $this->meetingRepository->getFieldKeys()->map(fn ($value) => $value['key'])->merge(['root_server_uri', 'format_shared_id_list'])->join(','),
            'google_api_key' => legacy_config('google_api_key', ''),
            'dbVersion' => $this->migrationRepository->getLastMigration()['migration'],
            'dbPrefix' => legacy_config('db_prefix'),
            'meeting_time_zones_enabled' => legacy_config('meeting_time_zones_enabled') ? '1' : '0',
            'phpVersion' => phpversion(),
            'auto_geocoding_enabled' => legacy_config('auto_geocoding_enabled')
        ]]);
    }

    private function getCoverageArea($request): BaseJsonResponse
    {
        $box = $this->meetingRepository->getBoundingBox();

        return new JsonResponse([[
            'nw_corner_longitude' => strval($box['nw']['long']),
            'nw_corner_latitude' => strval($box['nw']['lat']),
            'se_corner_longitude' => strval($box['se']['long']),
            'se_corner_latitude' => strval($box['se']['lat']),
        ]]);
    }

    private function getNawsDump($request): StreamedResponse
    {
        $validated = $request->validate([
            'sb_id' => [
                'required',
                'int',
                Rule::exists('comdef_service_bodies', 'id_bigint')
                    ->whereNot('worldid_mixed', '')
                    ->whereNotNull('worldid_mixed')
            ]
        ]);

        $deletedMeetings = $this->changeRepository->getMeetingChanges(serviceBodyId: $validated['sb_id'], changeTypes: [Change::CHANGE_TYPE_DELETE]);
        $meetings = $this->meetingRepository->getSearchResults(servicesInclude: [$validated['sb_id']], published: null, eagerServiceBodies: true);
        $formatIdToWorldId = $this->formatRepository->search(showAll: true)
            ->reject(fn ($fmt) => is_null($fmt->worldid_mixed) || empty(trim($fmt->worldid_mixed)))
            ->mapWithKeys(fn ($fmt, $_) => [$fmt->shared_id_bigint => $fmt->worldid_mixed]);

        $columnNames = ['Committee', 'CommitteeName', 'AddDate', 'AreaRegion', 'ParentName', 'ComemID', 'ContactID', 'ContactName', 'CompanyName', 'ContactAddrID', 'ContactAddress1', 'ContactAddress2', 'ContactCity', 'ContactState', 'ContactZip', 'ContactCountry', 'ContactPhone', 'MeetingID', 'Room', 'Closed', 'WheelChr', 'Day', 'Time', 'Language1', 'Language2', 'Language3', 'LocationId', 'Place', 'Address', 'City', 'LocBorough', 'State', 'Zip', 'Country', 'Directions', 'Institutional', 'Format1', 'Format2', 'Format3', 'Format4', 'Format5', 'Delete', 'LastChanged', 'Longitude', 'Latitude', 'ContactGP', 'PhoneMeetingNumber', 'VirtualMeetingLink', 'VirtualMeetingInfo', 'TimeZone', 'bmlt_id', 'unpublished'];
        $f = fopen('php://memory', 'r+');
        fputcsv($f, $columnNames);
        foreach ($meetings->concat($deletedMeetings) as $meeting) {
            $row = [];

            if ($meeting instanceof Meeting) {
                $isDeleted = false;
                $meetingData = $meeting->data
                    ->mapWithKeys(fn($data, $_) => [$data->key => $data->data_string])->toBase()
                    ->merge($meeting->longdata->mapWithKeys(fn($data, $_) => [$data->key => $data->data_blob])->toBase());
            } else {
                $isDeleted = true;
                $meeting = $meeting->before_object;
                if (is_null($meeting)) {
                    continue;  // should never happen, but you never know with these old databases...
                }

                $meetingData = collect($meeting['data_table_values'])
                    ->mapWithKeys(fn ($data, $_) => [$data['key'] => $data['data_string']])
                    ->merge(collect($meeting['longdata_table_values'])->mapWithKeys(fn ($data, $_) => [$data['key'] => $data['data_blob']]));
                $meeting = new Meeting($meeting['main_table_values']);
                $meeting->longitude = !is_null($meeting->longitude) ? floatval($meeting->longitude) : null;
                $meeting->latitude = !is_null($meeting->latitude) ? floatval($meeting->latitude) : null;
            }

            // list of format world ids
            $meetingFormats = collect(explode(',', $meeting->formats ?? ''))
                ->map(fn ($id) => $formatIdToWorldId->get(intval($id)))
                ->reject(fn ($value, $_) => is_null($value))
                ->unique();

            foreach ($columnNames as $columnName) {
                if ($columnName == 'Committee') {
                    $row[] = !empty($meeting->worldid_mixed) ? trim($meeting->worldid_mixed) : '';
                } elseif ($columnName == 'CommitteeName') {
                    $row[] = $meetingData->get('meeting_name', '');
                } elseif ($columnName == 'AddDate') {
                    $row[] = '';
                } elseif ($columnName == 'AreaRegion') {
                    $row[] = $meeting->serviceBody->worldid_mixed;
                } elseif ($columnName == 'ParentName') {
                    $row[] = $meeting->serviceBody->name_string;
                } elseif ($columnName == 'ComemID') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactID') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactName') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'CompanyName') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactAddrID') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactAddress1') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactAddress2') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactCity') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactState') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactZip') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactCountry') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'ContactPhone') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'MeetingID') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Room') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Closed') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'WheelChr') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Day') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Time') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Language1') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Language2') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Language3') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'LocationId') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Place') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Address') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'City') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'LocBorough') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'State') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Zip') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Country') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Directions') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Institutional') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Format1') {
                    $row[] = count($meetingFormats) >= 1 ? $meetingFormats->slice(0, 1)->first() : '';
                } elseif ($columnName == 'Format2') {
                    $row[] = count($meetingFormats) >= 2 ? $meetingFormats->slice(1, 1)->first() : '';
                } elseif ($columnName == 'Format3') {
                    $row[] = count($meetingFormats) >= 3 ? $meetingFormats->slice(2, 1)->first() : '';
                } elseif ($columnName == 'Format4') {
                    $row[] = count($meetingFormats) >= 4 ? $meetingFormats->slice(3, 1)->first() : '';
                } elseif ($columnName == 'Format5') {
                    $row[] = count($meetingFormats) >= 5 ? $meetingFormats->slice(4, 1)->first() : '';
                } elseif ($columnName == 'Delete') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'LastChanged') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'Longitude') {
                    $row[] = $meeting->longitude ?? '';
                } elseif ($columnName == 'Latitude') {
                    $row[] = $meeting->latitude ?? '';
                } elseif ($columnName == 'ContactGP') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'PhoneMeetingNumber') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'VirtualMeetingLink') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'VirtualMeetingInfo') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'TimeZone') {
                    $row[] = 'TODO';
                } elseif ($columnName == 'bmlt_id') {
                    $row[] = $meeting->id_bigint;
                } else {
                    // unpublished
                    $row[] = 'TODO';
                }
            }

            if (fputcsv($f, $row) == false) {
                abort(500);
            }
        }

        $filename = 'NAWSExport.csv';  // TODO generate proper filename
        return response()->streamDownload(fn () => rewind($f) && print(stream_get_contents($f)), $filename);
    }
}
