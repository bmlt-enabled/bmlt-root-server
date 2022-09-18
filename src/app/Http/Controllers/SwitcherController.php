<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Legacy\LegacyController;
use App\Http\Resources\FormatResource;
use App\Http\Resources\MeetingResource;
use App\Http\Resources\MeetingChangeResource;
use App\Http\Resources\ServiceBodyResource;
use App\Http\Responses\JsonResponse;
use App\Interfaces\ChangeRepositoryInterface;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\MigrationRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse as BaseJsonResponse;
use Illuminate\Support\Facades\Http;

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

        $validValues = ['GetSearchResults', 'GetFormats', 'GetServiceBodies', 'GetFieldKeys', 'GetFieldValues', 'GetChanges', 'GetServerInfo', 'GetCoverageArea'];
        if (in_array($switcher, $validValues)) {
            if ($dataFormat != 'json' && $dataFormat != 'jsonp') {
                abort(404, 'This endpoint only supports the \'json\' and \'jsonp\' data formats.');
            }

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

            return $response;
        }

        return LegacyController::handle($request);
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

            $geocodeResponse = Http::get("https://maps.googleapis.com/maps/api/geocode/json?key=$googleApiKey&address=$searchString&sensor=false");
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
        $langEnum = $request->input('lang_enum', config('app.locale', 'en'));
        $formats = $this->formatRepository->getFormats(langEnums: [$langEnum], meetings: $meetings);

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
        $langEnums = $request->input('lang_enum', config('app.locale', 'en'));
        if (!is_array($langEnums)) {
            $langEnums = [$langEnums];
        }

        $keyStrings = $request->input('key_strings', []);
        if (!is_array($keyStrings)) {
            $keyStrings = [$keyStrings];
        }

        $showAll = $request->input('show_all') == '1';

        $formats = $this->formatRepository->getFormats($langEnums, $keyStrings, $showAll);

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

        $serviceBodies = $this->serviceBodyRepository->getServiceBodies($includeIds, $excludeIds, $recurseChildren, $recurseParents);

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
            'phpVersion' => phpversion()
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
}
