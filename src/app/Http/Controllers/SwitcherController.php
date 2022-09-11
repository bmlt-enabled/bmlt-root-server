<?php

namespace App\Http\Controllers;

use App\Models\Migration;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Http\Resources\FormatResource;
use App\Http\Resources\MeetingChangeResource;
use App\Http\Resources\ServiceBodyResource;
use App\Http\Responses\JsonResponse;
use App\Http\Controllers\Legacy\LegacyController;
use App\Interfaces\ChangeRepositoryInterface;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;

class SwitcherController extends Controller
{
    private ChangeRepositoryInterface $changeRepository;
    private FormatRepositoryInterface $formatRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(
        ChangeRepositoryInterface $changeRepository,
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository
    ) {
        $this->changeRepository = $changeRepository;
        $this->formatRepository = $formatRepository;
        $this->meetingRepository = $meetingRepository;
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function get(Request $request, string $dataFormat)
    {
        $switcher = $request->input('switcher');

        $validValues = ['GetFormats', 'GetServiceBodies', 'GetFieldKeys', 'GetFieldValues', 'GetChanges', 'GetServerInfo'];
        if (in_array($switcher, $validValues)) {
            if ($dataFormat != 'json' && $dataFormat != 'jsonp') {
                abort(404, 'This endpoint only supports the \'json\' and \'jsonp\' data formats.');
            }

            if ($switcher == 'GetFormats') {
                $collection = $this->getFormats($request);
                $response = FormatResource::collection($collection)->response();
            } elseif ($switcher == 'GetServiceBodies') {
                $collection = $this->getServiceBodies($request);
                $response = ServiceBodyResource::collection($collection)->response();
            } elseif ($switcher == 'GetFieldKeys') {
                $collection = $this->getFieldKeys($request);
                $response = new JsonResponse($collection);
            } elseif ($switcher == 'GetFieldValues') {
                $collection = $this->getFieldValues($request);
                $response = new JsonResponse($collection);
            } elseif ($switcher == 'GetChanges') {
                $collection = $this->getMeetingChanges($request);
                $response = MeetingChangeResource::collection($collection)->response();
            } else {
                $collection = $this->getServerInfo($request);
                $response = new JsonResponse($collection);
            }

            if ($dataFormat == 'jsonp') {
                $response = $response->withCallback($request->input('callback', 'callback'));
            }

            return $response;
        }

        return LegacyController::handle($request);
    }

    private function getFormats(Request $request): Collection
    {
        $langEnums = $request->input('lang_enum', ['en']);
        if (!is_array($langEnums)) {
            $langEnums = [$langEnums];
        }

        $keyStrings = $request->input('key_strings', []);
        if (!is_array($keyStrings)) {
            $keyStrings = [$keyStrings];
        }

        $showAll = $request->input('show_all') == '1';

        return $this->formatRepository->getFormats($langEnums, $keyStrings, $showAll);
    }

    private function getServiceBodies(Request $request): Collection
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

        return $this->serviceBodyRepository->getServiceBodies($includeIds, $excludeIds, $recurseChildren, $recurseParents);
    }

    private function getFieldKeys($request): Collection
    {
        return $this->meetingRepository->getFieldKeys();
    }

    private function getFieldValues($request): Collection
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

        return $this->meetingRepository->getFieldValues($fieldName, $specificFormats, $allFormats);
    }

    private function getMeetingChanges(Request $request): Collection
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

        return $this->changeRepository->getMeetingChanges($startDate, $endDate, $meetingId, $serviceBodyId);
    }

    private function getServerInfo($request)
    {
        $versionArray = explode('.', config('app.version'));
        return [[
            'version' => config('app.version'),
            'versionInt' => strval((intval($versionArray[0]) * 1000000) + (intval($versionArray[1]) * 1000) + intval($versionArray[2])),
            'langs' => collect(scandir(base_path('lang')))->reject(fn ($dir) => $dir == '.' || $dir == '..')->sort()->join(','),
            'nativeLang' => config('app.locale'),
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
            'dbVersion' => Migration::query()->orderByDesc('id')->first()->migration,
            'dbPrefix' => legacy_config('db_prefix'),
            'meeting_time_zones_enabled' => legacy_config('meeting_time_zones_enabled') ? '1' : '0',
            'phpVersion' => phpversion()
        ]];
    }
}
