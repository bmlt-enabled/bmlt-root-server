<?php

namespace App\Http\Resources;

use App\Models\Meeting;
use App\Models\MeetingData;
use App\Repositories\ServiceBodyRepository;
use Illuminate\Support\Collection;

class MeetingResource extends JsonResource
{
    private static ?Collection $meetingDataTemplates = null;
    private static bool $areDataFieldKeysLoaded = false;
    private static ?Collection $dataFieldKeys = null;
    private static bool $hasDataFieldKeys = false;
    private static ?Collection $serviceBodyPermissions = null;
    private static bool $userIsAuthenticated = false;
    private static bool $userIsAdmin = false;
    private static bool $arePermissionsLoaded = false;

    // this is really only for tests
    public static function resetStaticVariables()
    {
        self::$meetingDataTemplates = null;
        self::$areDataFieldKeysLoaded = false;
        self::$dataFieldKeys = null;
        self::$hasDataFieldKeys = false;
        self::$serviceBodyPermissions = null;
        self::$arePermissionsLoaded = false;
        self::$userIsAuthenticated = false;
        self::$userIsAdmin = false;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->loadDataFieldKeys($request);
        $this->loadPermissions($request);

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
        ];

        // data table keys
        $meetingData = $this->data->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])->toBase()
            ->merge(
                $this->longdata->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_blob])->toBase()
            );

        foreach ($this->getMeetingDataTemplates() as $meetingDataTemplate) {
            if (self::$hasDataFieldKeys && !self::$dataFieldKeys->has($meetingDataTemplate->key)) {
                continue;
            }

            if ($meetingDataTemplate->visibility == 1) {
                if (!(self::$userIsAuthenticated && (self::$userIsAdmin || self::$serviceBodyPermissions?->has($this->service_body_bigint)))) {
                    $meeting[$meetingDataTemplate->key] = '';
                    continue;
                }
            }

            $meeting[$meetingDataTemplate->key] = $meetingData->get($meetingDataTemplate->key, '');
        }

        // keys the old server always had at the end
        if (!self::$hasDataFieldKeys) {
            $meeting['published'] = strval($this->published);
            $meeting['root_server_uri'] = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
            $meeting['format_shared_id_list'] = $this->getCalculatedFormatSharedIds() ?? '';
        }

        return $meeting;
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
            $this->start_time ?? ''
        );
    }

    private function getDurationTime()
    {
        return $this->when(
            !self::$hasDataFieldKeys || self::$dataFieldKeys->has('duration_time'),
            $this->duration_time ?? ''
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

    private function loadPermissions($request)
    {
        if (self::$arePermissionsLoaded) {
            return;
        }

        $user = $request->user();
        if (!is_null($user) && $user->user_level_tinyint != 4) {
            self::$userIsAuthenticated = true;
            if ($user->user_level_tinyint == 1) {
                self::$userIsAdmin = true;
            } else {
                $serviceBodyRepository = new ServiceBodyRepository();
                self::$serviceBodyPermissions = $serviceBodyRepository
                    ->getServiceBodyIdsForUser($user->id_bigint)
                    ->mapWithKeys(fn ($sbId, $_) => [$sbId => null]);
            }
        }

        self::$arePermissionsLoaded = true;
    }

    private function loadDataFieldKeys($request)
    {
        if (self::$areDataFieldKeysLoaded) {
            return;
        }

        $dataFieldKeys = $request->input('data_field_key');
        $dataFieldKeys = collect(!is_null($dataFieldKeys) ? explode(',', $dataFieldKeys) : []);
        $dataFieldKeys = $this->getMeetingDataTemplates()
            ->mapWithKeys(fn ($data, $_) => [$data->key => $data->data_string])
            ->keys()
            ->merge(Meeting::$mainFields)
            ->merge(['distance_in_miles', 'distance_in_km'])
            ->intersect($dataFieldKeys)
            ->mapWithKeys(fn ($key, $_) => [$key => $key]);
        self::$hasDataFieldKeys = $dataFieldKeys->isNotEmpty();
        self::$dataFieldKeys = $dataFieldKeys;
        self::$areDataFieldKeysLoaded = true;
    }

    private function getMeetingDataTemplates(): Collection
    {
        if (is_null(self::$meetingDataTemplates)) {
            self::$meetingDataTemplates = MeetingData::query()->where('meetingid_bigint', 0)->get();
        }

        return self::$meetingDataTemplates;
    }
}
