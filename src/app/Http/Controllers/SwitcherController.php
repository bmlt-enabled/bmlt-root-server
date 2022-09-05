<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use App\Http\Resources\FormatResource;
use App\Http\Resources\ServiceBodyResource;
use App\Http\Controllers\Legacy\LegacyController;
use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\MeetingData;

class SwitcherController extends Controller
{
    private FormatRepositoryInterface $formatRepository;
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(
        FormatRepositoryInterface $formatRepository,
        ServiceBodyRepositoryInterface $serviceBodyRepository
    ) {
        $this->formatRepository = $formatRepository;
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function get(Request $request, string $dataFormat)
    {
        $switcher = $request->input('switcher');

        $validValues = ['GetFormats', 'GetServiceBodies', 'GetFieldKeys'];
        if (in_array($switcher, $validValues)) {
            if ($dataFormat != 'json' && $dataFormat != 'jsonp') {
                abort(404, 'This endpoint only supports the \'json\' and \'jsonp\' data formats.');
            }

            if ($switcher == 'GetFormats') {
                $response = $this->getFormats($request);
            } elseif ($switcher == 'GetServiceBodies') {
                $response = $this->getServiceBodies($request);
            } else {
                $response = $this->getFieldKeys($request);
            }

            if ($dataFormat == 'jsonp') {
                $response = $response->withCallback($request->input('callback', 'callback'));
            }

            return $response;
        }

        return LegacyController::handle($request);
    }

    private function getFormats(Request $request): JsonResponse
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

        $formats = $this->formatRepository->getFormats($langEnums, $keyStrings, $showAll);
        return FormatResource::collection($formats->get())->response();
    }

    private function getServiceBodies(Request $request): JsonResponse
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
        return ServiceBodyResource::collection($serviceBodies->get())->response();
    }

    private function getFieldKeys($request)
    {
        $data = [
            ['key' => 'id_bigint', 'description' => __('main_prompts.id_bigint')],
            ['key' => 'worldid_mixed', 'description' => __('main_prompts.worldid_mixed')],
            ['key' => 'service_body_bigint', 'description' => __('main_prompts.service_body_bigint')],
            ['key' => 'weekday_tinyint', 'description' => __('main_prompts.weekday_tinyint')],
            ['key' => 'venue_type', 'description' => __('main_prompts.venue_type')],
            ['key' => 'start_time', 'description' => __('main_prompts.start_time')],
            ['key' => 'duration_time', 'description' => __('main_prompts.duration_time')],
            ['key' => 'time_zone', 'description' => __('main_prompts.time_zone')],
            ['key' => 'formats', 'description' => __('main_prompts.formats')],
            ['key' => 'lang_enum', 'description' => __('main_prompts.lang_enum')],
            ['key' => 'longitude', 'description' => __('main_prompts.longitude')],
            ['key' => 'latitude', 'description' => __('main_prompts.latitude')],
        ];

        $langEnum = App::currentLocale();
        $fields = MeetingData::query()
            ->where('meetingid_bigint', 0)
            ->where('lang_enum', $langEnum)
            ->whereNot('visibility', 1)
            ->get();

        foreach ($fields as $field) {
            array_push($data, ['key' => $field->key, 'description' => $field->field_prompt]);
        }

        if ($langEnum != 'en') {
            $seenKeys = [];
            foreach ($data as $f) {
                $seenKeys[$f['key']] = null;
            }

            $fields = MeetingData::query()
                ->where('meetingid_bigint', 0)
                ->where('lang_enum', 'en')
                ->whereNot('visibility', 1)
                ->get();
            foreach ($fields as $field) {
                if (!array_key_exists($field->key, $seenKeys)) {
                    array_push($data, ['key' => $field->key, 'description' => $field->field_prompt]);
                }
            }
        }

        return new JsonResponse($data);
    }
}
