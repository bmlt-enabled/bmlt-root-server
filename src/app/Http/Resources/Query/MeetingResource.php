<?php

namespace App\Http\Resources\Query;

use App\Http\Resources\JsonResource;
use App\Models\User;
use App\Repositories\MeetingRepository;
use App\Repositories\ServiceBodyRepository;
use Illuminate\Support\Collection;

class MeetingResource extends JsonResource
{
    private static bool $isRequestInitialized = false;

    private static ?Collection $meetingDataTemplates = null;
    private static ?Collection $dataFieldKeys = null;
    private static bool $hasDataFieldKeys = false;

    private static ?Collection $serviceBodyPermissions = null;
    private static bool $userIsAuthenticated = false;
    private static bool $userIsAdmin = false;

    private static ?string $defaultDurationTime = null;

    private static bool $isAggregatorModeEnabled = false;

    // Allows tests to reset state
    public static function resetStaticVariables()
    {
        self::$isRequestInitialized = false;
        self::$meetingDataTemplates = null;
        self::$dataFieldKeys = null;
        self::$hasDataFieldKeys = false;
        self::$serviceBodyPermissions = null;
        self::$userIsAuthenticated = false;
        self::$userIsAdmin = false;
        self::$defaultDurationTime = null;
        self::$isAggregatorModeEnabled = false;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!self::$isRequestInitialized) {
            $this->initializeRequest($request);
            self::$isRequestInitialized = true;
        }

        // main keys
        $meeting = [
            'id_bigint' => $this->getIdBigint(),
            'worldid_mixed' => $this->getWorldIdMixed(),
            'shared_group_id_bigint' => $this->getSharedGroupIdBigint(),
            'service_body_bigint' => $this->getServiceBodyBigint(),
            'weekday_tinyint' => $this->getWeekdayTinyint(),
            'venue_type' => $this->getVenueType(),
            'start_time' => $this->getStartTime(),
            'duration_time' => $this->getDurationTime(),
            'time_zone' => $this->getTimeZone(),
            'formats' => $this->getFormats(),
            'lang_enum' => $this->getLangEnum(),
            'longitude' => $this->getLongitude(),
            'latitude' => $this->getLatitude(),
            'distance_in_km' => $this->getDistanceInKm(),
            'distance_in_miles' => $this->getDistanceInMiles(),
            'email_contact' => $this->getEmailContact(),
            'published' => $this->getPublished(),
            'root_server_uri' => $this->getRootServerUri($request),
            'format_shared_id_list' => $this->getFormatSharedIdList(),
            'root_server_id' => $this->getRootServerId(),
            'source_id' => $this->getSourceId(),
        ];

        // data table keys
        $meetingData = $this->data->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])->toBase()
            ->merge(
                $this->longdata->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])->toBase()
            );

        foreach (self::$meetingDataTemplates as $meetingDataTemplate) {
            if (self::$hasDataFieldKeys && !self::$dataFieldKeys->has($meetingDataTemplate->key)) {
                continue;
            }

            if ($meetingDataTemplate->visibility == 1) {
                if (!(self::$userIsAuthenticated && (self::$userIsAdmin || self::$serviceBodyPermissions?->has($this->service_body_bigint)))) {
                    $meeting[$meetingDataTemplate->key] = '';
                    continue;
                }
            }

            $meeting[$meetingDataTemplate->key] = $meetingData->get($meetingDataTemplate->key, '') ?? '';
        }

        return $meeting;
    }

    private function initializeRequest($request)
    {
        $meetingRepository = new MeetingRepository();
        $serviceBodyRepository = new ServiceBodyRepository();

        // Default duration time
        self::$defaultDurationTime = legacy_config('default_duration_time');

        // Aggregator mode
        self::$isAggregatorModeEnabled = (bool)legacy_config('aggregator_mode_enabled');

        // Preload meeting data templates
        self::$meetingDataTemplates = $meetingRepository->getDataTemplates();

        // Permissions
        $user = $request->user();
        if (!is_null($user) && $user->user_level_tinyint != User::USER_LEVEL_DEACTIVATED) {
            self::$userIsAuthenticated = true;
            if ($user->user_level_tinyint == User::USER_LEVEL_ADMIN) {
                self::$userIsAdmin = true;
            } else {
                self::$serviceBodyPermissions = $serviceBodyRepository
                    ->getAssignedServiceBodyIds($user->id_bigint)
                    ->mapWithKeys(fn ($sbId, $_) => [$sbId => null]);
            }
        }

        // Data field keys
        $dataFieldKeys = $request->input('data_field_key');
        $dataFieldKeys = collect(!is_null($dataFieldKeys) ? explode(',', $dataFieldKeys) : []);
        if ($dataFieldKeys->isNotEmpty()) {
            $dataFieldKeys = self::$meetingDataTemplates
                ->mapWithKeys(fn($data, $_) => [$data->key => $data->data_string])
                ->keys()
                ->merge($meetingRepository->getMainFields())
                ->merge(['published', 'root_server_uri', 'format_shared_id_list', 'distance_in_miles', 'distance_in_km'])
                ->intersect($dataFieldKeys)
                ->mapWithKeys(fn($key, $_) => [$key => $key]);

            self::$hasDataFieldKeys = $dataFieldKeys->isNotEmpty();
            self::$dataFieldKeys = $dataFieldKeys;
        }
    }

    private function getIdBigint()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('id_bigint'),
            strval($this->id_bigint)
        );
    }

    private function getWorldIdMixed()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('worldid_mixed'),
            $this->worldid_mixed ?? ''
        );
    }

    private function getSharedGroupIdBigint()
    {
        return $this->when(
            !self::$hasDataFieldKeys,
            strval($this->shared_group_id_bigint ?? '')
        );
    }

    private function getServiceBodyBigint()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('service_body_bigint'),
            strval($this->service_body_bigint)
        );
    }

    private function getWeekdayTinyint()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('weekday_tinyint'),
            strval((!is_null($this->weekday_tinyint) ? $this->weekday_tinyint + 1 : null) ?? '')
        );
    }

    private function getVenueType()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('venue_type'),
            strval($this->venue_type ?? '')
        );
    }

    private function getStartTime()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('start_time'),
            (\DateTime::createFromFormat('H:i:s', $this->start_time) ?: \DateTime::createFromFormat('H:i', $this->start_time) ?: null)?->format('H:i:s') ?? ''
        );
    }

    private function getDurationTime()
    {
        $durationTime = $this->duration_time;

        if ($durationTime != '24:00:00') {
            $durationTime = (\DateTime::createFromFormat('H:i:s', $this->duration_time) ?: \DateTime::createFromFormat('H:i', $this->duration_time) ?: null)?->format('H:i:s');
            if (empty($durationTime) || $durationTime == '00:00:00') {
                $durationTime = self::$defaultDurationTime;
            }
        }

        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('duration_time'),
            $durationTime
        );
    }

    private function getTimeZone()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('time_zone'),
            $this->time_zone ?? ''
        );
    }

    private function getFormats()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('formats'),
            $this->getCalculatedFormatKeys() ?? ''
        );
    }

    private function getLangEnum()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('lang_enum'),
            $this->lang_enum ?? ''
        );
    }
    private function getLongitude()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('longitude'),
            strval($this->longitude ?? '')
        );
    }

    private function getLatitude()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('latitude'),
            strval($this->latitude ?? '')
        );
    }

    private function getDistanceInKm()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('distance_in_km'),
            strval($this->distance_in_km ?? '')
        );
    }

    private function getDistanceInMiles()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('distance_in_miles'),
            strval($this->distance_in_miles ?? '')
        );
    }

    private function getEmailContact()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('email_contact'),
            (self::$userIsAuthenticated && (self::$userIsAdmin || self::$serviceBodyPermissions?->has($this->service_body_bigint))) ? $this->email_contact ?? '' : ''
        );
    }

    private function getPublished()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('published'),
            strval($this->published)
        );
    }

    private function getRootServerUri($request)
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('root_server_uri'),
            self::$isAggregatorModeEnabled && $this->root_server_id ? $this->rootServer->url : $request->getSchemeAndHttpHost() . $request->getBaseUrl()
        );
    }

    private function getFormatSharedIdList()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('format_shared_id_list'),
            $this->getCalculatedFormatSharedIds() ?? ''
        );
    }

    private function getRootServerId()
    {
        return $this->when(
            self::$isAggregatorModeEnabled && (!self::$hasDataFieldKeys || self::$dataFieldKeys->has('root_server_id')),
            $this->root_server_id ?? ''
        );
    }

    private function getSourceId()
    {
        return $this->when(
            self::$isAggregatorModeEnabled && (!self::$hasDataFieldKeys || self::$dataFieldKeys->has('source_id')),
            $this->source_id ?? ''
        );
    }
}
