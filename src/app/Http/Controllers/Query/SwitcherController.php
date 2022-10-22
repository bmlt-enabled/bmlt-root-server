<?php

namespace App\Http\Controllers\Query;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CatchAllController;
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

            $searchStringRadius = $request->input('SearchStringRadius');
            if (is_null($searchStringRadius) || !is_numeric($searchStringRadius)) {
                $nNearestAuto = abs(legacy_config('number_of_meetings_for_auto')) * -1;
                $geoWidthMiles = legacy_config('distance_units') == 'mi' ? $nNearestAuto : null;
                $geoWidthKilometers = legacy_config('distance_units') != 'mi' ? $nNearestAuto : null;
            } else {
                $geoWidthMiles = legacy_config('distance_units') == 'mi' ? floatval($searchStringRadius) : null;
                $geoWidthKilometers = legacy_config('distance_units') != 'mi' ? floatval($searchStringRadius) : null;
            }

            $searchString = null;
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
            'versionInt' => strval((intval($versionArray[0]) * 1000000) + (intval($versionArray[1]) * 1000) + intval(strstr($versionArray[2], '-', true))),
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
            'auto_geocoding_enabled' => legacy_config('auto_geocoding_enabled'),
            'commit' => config('app.commit'),
            'default_closed_status' => legacy_config('default_closed_status')
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
            ]
        ]);


        // Generate a filename for the spreadsheet of this form: BMLT_AR73828_seattle_area_2022_09_30_16_57_53.csv where
        // AR73828 is the World ID for the service body (converting letters to upper case and non alpha characters to _),
        // seattle_area is the name of the service body (converting letters to lower case and non alpha characters to _),
        // and 2022_09_30_16_57_53 is current the date and time.
        $serviceBody =  ($this->serviceBodyRepository->search([$validated['sb_id']]))[0];
        // kind of a hack -- assume there is just one service body
        $serviceBodyName = $serviceBody->name_string;
        $serviceBodyWorldId = $serviceBody->worldid_mixed;
        $worldIdForFileName = preg_replace('|[\W]|', '_', strtoupper($serviceBodyWorldId));
        if (preg_match('|^_+$|', $worldIdForFileName)) {
            $worldIdForFileName = '';
        }
        $sbNameForFileName = preg_replace('|[\W]|', '_', strtolower($serviceBodyName));
        if (preg_match('|^_+$|', $sbNameForFileName)) {
            $sbNameForFileName = '';
        }
        $filename = join('_', ['BMLT', $worldIdForFileName, $sbNameForFileName, date('Y_m_d_H_i_s')]);
        $filename .= '.csv';

        return response()->streamDownload(function () use ($validated) {
            // constants for getNawsDump
            $columnNames = ['Committee', 'CommitteeName', 'AddDate', 'AreaRegion', 'ParentName', 'ComemID', 'ContactID', 'ContactName',
                'CompanyName', 'ContactAddrID', 'ContactAddress1', 'ContactAddress2', 'ContactCity', 'ContactState', 'ContactZip', 'ContactCountry',
                'ContactPhone', 'MeetingID', 'Room', 'Closed', 'WheelChr', 'Day', 'Time', 'Language1', 'Language2', 'Language3', 'LocationId',
                'Place', 'Address', 'City', 'LocBorough', 'State', 'Zip', 'Country', 'Directions', 'Institutional', 'Format1', 'Format2', 'Format3',
                'Format4', 'Format5', 'Delete', 'LastChanged', 'Longitude', 'Latitude', 'ContactGP', 'PhoneMeetingNumber', 'VirtualMeetingLink',
                'VirtualMeetingInfo', 'TimeZone', 'bmlt_id', 'unpublished'];
            // $excludedNawsFormats are formats included with meetings but that should not be among the formats listed in the export spreadsheet --
            //  they are exported in their own columns.
            $excludedNawsFormats = ['OPEN', 'CLOSED', 'WCHR'];
            // $nawsExportFormatsAtFront are formats that should be listed first in the format1, ... format5 columns, to make sure they are included.
            // This is particularly important for VM (Virtual Meeting), TC (Temporarily Closed) and HYBR (Hybrid), since these are essential and we
            // don't want them falling off the end.  In versions 2.16 and earlier of the server, this could be overridden by a variable in
            // auto-config.inc.php, but this is currently no longer supported since it seemed unlikely that anyone would ever want to change the default here.
            $nawsExportFormatsAtFront =  ['VM', 'TC', 'HYBR', 'W', 'M', 'GL'];

            $allServices = $this->serviceBodyRepository->getChildren([$validated['sb_id']]);
            $deletedMeetingData = [];
            $deletedMeetings = $this->changeRepository->getMeetingChanges(serviceBodyId: $validated['sb_id'], changeTypes: [Change::CHANGE_TYPE_DELETE])
                ->map(function ($meetingChange) use (&$deletedMeetingData) {
                    $serializedMeeting = $meetingChange->before_object;
                    if (is_null($serializedMeeting)) {
                        // TODO write a test
                        return null;
                    }

                    $meetingId = $meetingChange->before_id_bigint ?? $serializedMeeting['main_table_values']['id_bigint'] ?? null;
                    if (is_null($meetingId) || !is_numeric($meetingId)) {
                        // TODO write a test
                        return null;
                    }

                    $meeting = new Meeting($serializedMeeting['main_table_values']);
                    $meeting->id_bigint = $meetingId;

                    if (is_null($meeting->worldid_mixed) || empty(trim($meeting->worldid_mixed)) || trim($meeting->worldid_mixed) == 'deleted') {
                        // TODO write a test
                        return null;
                    }

                    $deletedMeetingData[$meeting->id_bigint] = collect($serializedMeeting['data_table_values'])
                        ->mapWithKeys(fn ($data, $_) => [$data['key'] => $data['data_string']])
                        ->merge(collect($serializedMeeting['longdata_table_values'])->mapWithKeys(fn ($data, $_) => [$data['key'] => $data['data_blob']]));

                    return $meeting;
                })
                ->reject(fn ($meeting) => is_null($meeting));
            $meetings = $this->meetingRepository->getSearchResults(servicesInclude: $allServices, published: null, eagerServiceBodies: true, sortKeys: ['lang_enum', 'weekday_tinyint', 'start_time', 'id_bigint'])
                ->concat($deletedMeetings);
            $allFormats = $this->formatRepository->search(langEnums: [legacy_config('language')], showAll: true)
                ->reject(fn ($fmt) => is_null($fmt->key_string) || empty(trim($fmt->key_string)));
            $formatIdToWorldId = $allFormats->mapWithKeys(fn ($fmt, $_) => [$fmt->shared_id_bigint => $fmt->worldid_mixed]);
            $formatIdToKeyString = $allFormats->mapWithKeys(fn ($fmt, $_) => [$fmt->shared_id_bigint => $fmt->key_string]);
            $formatIdToNameString = $allFormats->mapWithKeys(fn ($fmt, $_) => [$fmt->shared_id_bigint => $fmt->name_string]);
            // $lastChanged is a dictionary whose keys are meeting IDs and whose values are the last time that meeting was changed
            // $meetingIdsAndTimes is just used in constructing $lastChanged
            $meetingIdsAndTimes = $this->changeRepository->getMeetingChanges(serviceBodyId: $validated['sb_id'])
                ->map(function ($change, $_) {
                    return [$change?->before_id_bigint ?? $change->after_id_bigint, strtotime($change->change_date)];
                });
            $lastChanged = [];
            foreach ($meetingIdsAndTimes as list($id, $time)) {
                $lastChanged[$id] = max($time, $lastChanged[$id] ?? 0);
            }

            $f = fopen('php://output', 'r+');
            fputcsv($f, $columnNames);

            foreach ($meetings as $meeting) {
                $row = [];

                $isDeleted = array_key_exists($meeting->id_bigint, $deletedMeetingData);
                if ($isDeleted) {
                    $meetingData = $deletedMeetingData[$meeting->id_bigint];
                } else {
                    $meetingData = $meeting->data
                        ->mapWithKeys(fn($data, $_) => [$data->key => $data->data_string])->toBase()
                        ->merge($meeting->longdata->mapWithKeys(fn($data, $_) => [$data->key => $data->data_blob])->toBase());
                }

                $allMeetingFormatIds = collect(explode(',', $meeting->formats ?? ''));
                // list of format world ids
                $allNawsMeetingFormats = $allMeetingFormatIds
                    ->map(fn ($id) => $formatIdToWorldId->get(intval($id)))
                    ->reject(fn ($value, $_) => is_null($value) || $value == '')
                    ->unique()
                    ->toArray();
                $nawsMeetingFormatsForExport = [];
                foreach ($nawsExportFormatsAtFront as $fmt) {
                    if (in_array($fmt, $allNawsMeetingFormats)) {
                        $nawsMeetingFormatsForExport[] = $fmt;
                    }
                }
                foreach ($allNawsMeetingFormats as $fmt) {
                    if (!in_array($fmt, $nawsExportFormatsAtFront) && !in_array($fmt, $excludedNawsFormats)) {
                        $nawsMeetingFormatsForExport[] = $fmt;
                    }
                }
                // $nonNawsFormatNames is a array of names of all formats that don't map to NAWS codes
                $nonNawsFormatNames = $allMeetingFormatIds
                    ->reject(fn ($value, $_) => $formatIdToWorldId->get(intval($value)))
                    ->map(fn ($id) => $formatIdToNameString->get(intval($id)))
                    ->toArray();
                $nawsLanguages = $allMeetingFormatIds
                    ->filter(fn ($value, $_) => $formatIdToWorldId->get(intval($value)) === 'LANG')
                    ->map(fn ($id) => strtoupper($formatIdToKeyString->get(intval($id))))
                    ->values()
                    ->toArray();

                foreach ($columnNames as $columnName) {
                    switch ($columnName) {
                        case 'Committee':
                            $row[] = !empty($meeting->worldid_mixed) ? trim($meeting->worldid_mixed) : '';
                            break;
                        case 'CommitteeName':
                            $row[] = $meetingData->get('meeting_name', '');
                            break;
                        case 'AddDate':
                            $row[] = '';
                            break;
                        case 'AreaRegion':
                            $row[] = $meeting->serviceBody->worldid_mixed;
                            break;
                        case 'ParentName':
                            $row[] = $meeting->serviceBody->name_string;
                            break;
                        case 'ComemID':
                            $row[] = '';
                            break;
                        case 'ContactID':
                            $row[] = '';
                            break;
                        case 'ContactName':
                            $row[] = '';
                            break;
                        case 'CompanyName':
                            $row[] = '';
                            break;
                        case 'ContactAddrID':
                            $row[] = '';
                            break;
                        case 'ContactAddress1':
                            $row[] = '';
                            break;
                        case 'ContactAddress2':
                            $row[] = '';
                            break;
                        case 'ContactCity':
                            $row[] = '';
                            break;
                        case 'ContactState':
                            $row[] = '';
                            break;
                        case 'ContactZip':
                            $row[] = '';
                            break;
                        case 'ContactCountry':
                            $row[] = '';
                            break;
                        case 'ContactPhone':
                            $row[] = '';
                            break;
                        case 'MeetingID':
                            $row[] = '';
                            break;
                        case 'Room':
                            $row[] = implode(', ', $nonNawsFormatNames);
                            break;
                        case 'Closed':
                            $row[] = $this->getNawsClosed($allNawsMeetingFormats);
                            break;
                        case 'WheelChr':
                            $row[] = in_array('WCHR', $allNawsMeetingFormats) ? 'TRUE' : 'FALSE';
                            break;
                        case 'Day':
                            $row[] = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$meeting->weekday_tinyint] ?? '';
                            break;
                        case 'Time':
                            $row[] = $this->getNawsTime($meeting);
                            break;
                        case 'Language1':
                            $row[] = $nawsLanguages[0] ?? '';
                            break;
                        case 'Language2':
                            $row[] = '';
                            // could also fill this in as follows:
                            // $row[] = $nawsLanguages[1] ?? '';
                            break;
                        case 'Language3':
                            $row[] = '';
                            // could also fill this in as follows:
                            // $row[] = $nawsLanguages[2] ?? '';
                            break;
                        case 'LocationId':
                            $row[] = '';
                            break;
                        case 'Place':
                            $row[] = $meetingData->get('location_text', '');
                            break;
                        case 'Address':
                            $row[] = $meetingData->get('location_street', '');
                            break;
                        case 'City':
                            $row[] = $this->getNawsCity($meetingData);
                            break;
                        case 'LocBorough':
                            $row[] = $meetingData->get('location_neighborhood', '');
                            break;
                        case 'State':
                            $row[] = $meetingData->get('location_province', '');
                            break;
                        case 'Zip':
                            $row[] = $meetingData->get('location_postal_code_1', '');
                            break;
                        case 'Country':
                            $row[] = $meetingData->get('location_nation', '');
                            break;
                        case 'Directions':
                            $row[] = $this->getNawsDirections($meetingData);
                            break;
                        case 'Institutional':
                            $row[] = 'FALSE';
                            break;
                        case 'Format1':
                            $row[] = $nawsMeetingFormatsForExport[0] ?? '';
                            break;
                        case 'Format2':
                            $row[] = $nawsMeetingFormatsForExport[1] ?? '';
                            break;
                        case 'Format3':
                            $row[] = $nawsMeetingFormatsForExport[2] ?? '';
                            break;
                        case 'Format4':
                            $row[] = $nawsMeetingFormatsForExport[3] ?? '';
                            break;
                        case 'Format5':
                            $row[] = $nawsMeetingFormatsForExport[4] ?? '';
                            break;
                        case 'Delete':
                            // TODO write a test
                            $row[] = $isDeleted ? 'D' : '';
                            break;
                        case 'LastChanged':
                            $row[] = $this->getLastChanged($lastChanged, $meeting);
                            break;
                        case 'Longitude':
                            $row[] = $meeting->longitude ?? '';
                            break;
                        case 'Latitude':
                            $row[] = $meeting->latitude ?? '';
                            break;
                        case 'ContactGP':
                            $row[] = '';
                            break;
                        case 'PhoneMeetingNumber':
                            $row[] = $meetingData->get('phone_meeting_number', '');
                            break;
                        case 'VirtualMeetingLink':
                            $row[] = $meetingData->get('virtual_meeting_link', '');
                            break;
                        case 'VirtualMeetingInfo':
                            $row[] = $meetingData->get('virtual_meeting_additional_info', '');
                            break;
                        case 'TimeZone':
                            $row[] = !empty($meeting->time_zone) ? trim($meeting->time_zone) : '';
                            break;
                        case 'bmlt_id':
                            $row[] = $meeting->id_bigint;
                            break;
                        case 'unpublished':
                            $row[] = $meeting->published ? '' : '1';
                            break;
                        default:
                            $row[] = 'INTERNAL ERROR - BAD COLUMN NAME';
                    }
                }

                if (fputcsv($f, $row) == false) {
                    abort(500);
                }
            }
        }, $filename);
    }

    // return 'OPEN' or 'CLOSED' depending on whether it's an open or closed meeting
    private function getNawsClosed($meetingFormats)
    {
        // If the meeting formats include just OPEN, then it's open.
        // If the meeting formats include just CLOSED, then it's closed.
        // If the meeting formats don't include either, then it defaults to default_closed_status from the config.
        // If the meeting formats include both OPEN and CLOSED, then it's closed.  (Admins shouldn't do this, but the UI
        // doesn't prevent it.  This behavior is different from the old root server, which in this case would return
        // the opposite of default_closed_status.  That seemed like a bug.)
        if (in_array('CLOSED', $meetingFormats)) {
            return 'CLOSED';
        }
        if (in_array('OPEN', $meetingFormats)) {
            return 'OPEN';
        }
        return legacy_config('default_closed_status') ? 'CLOSED' : 'OPEN';
    }

    // Meeting times will be of the form 19:30:00.  Convert to 1930 (which is what this format expects).
    private function getNawsTime($meeting)
    {
        $t = explode(':', $meeting->start_time);
        if (is_array($t) && count($t) > 1) {
            return $t[0].$t[1];
        } else {
            return $t;
        }
    }

    private function getNawsCity($meetingData)
    {
        // first choice is the borough, then municipality, then neighborhood
        $ret = $meetingData->get('location_city_subsection', '');
        if (!$ret) {
            $ret = $meetingData->get('location_municipality', '');
        }
        if (!$ret) {
            $ret = $meetingData->get('location_neighborhood', '');
        }
        return $ret;
    }

    // returns the location_info plus the comments field
    private function getNawsDirections($meetingData)
    {
        $location_info = trim($meetingData->get('location_info', ''));
        $comments = trim($meetingData->get('comments', ''));
        if ($location_info) {
            if ($comments) {
                return $location_info . ', ' . $comments;
            } else {
                return $location_info;
            }
        }
        return $comments;
    }

    private function getLastChanged($lastChanged, $meeting)
    {
        $c = $lastChanged[$meeting->id_bigint] ?? false;
        return $c ? date('n/j/y', $c) : '';
    }
}
