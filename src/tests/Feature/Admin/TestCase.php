<?php

namespace Tests\Feature\Admin;

use App\Models\Meeting;
use App\Models\MeetingData;
use App\Models\RootServer;
use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    // meetings
    //
    //
    private static $meetingMainFieldDefaults = [
        'worldid_mixed' => 'worldid_mixed_default',
        'service_body_bigint' => 1,
        'weekday_tinyint' => 1,
        'venue_type' => 1,
        'start_time' => '19:00:00',
        'duration_time' => '01:00:00',
        'time_zone' => '',
        'formats' => '17,29,30', // O,To,Tr
        'lang_enum' => 'en',
        'longitude' => -79.793701171875,
        'latitude' => 36.065752051707,
        'published' => 1,
    ];

    private static $meetingDataFieldDefaults = [
        'meeting_name' => 'NA Meeting',
    ];

    protected function createRootServer(int $sourceId, string $name = 'test', string $url = 'https://test.com'): RootServer
    {
        return RootServer::create([
            'source_id' => $sourceId,
            'name' => $name,
            'url' => $url
        ]);
    }

    protected function createMeeting(array $mainFields = [], array $dataFields = [], array $longDataFields = [])
    {
        static $dataFieldTemplates;
        if (!isset($dataFieldTemplates)) {
            $dataFieldTemplates = MeetingData::query()
                ->where('meetingid_bigint', 0)
                ->get()
                ->mapWithKeys(fn ($value, $_) => [$value->key => $value]);
        }

        $meeting = Meeting::create(array_merge(self::$meetingMainFieldDefaults, $mainFields));

        $dataFields = array_merge(self::$meetingDataFieldDefaults, $dataFields);
        foreach (array_keys($longDataFields) as $fieldName) {
            unset($dataFields[$fieldName]);
        }

        foreach ($dataFields as $fieldName => $fieldValue) {
            $fieldTemplate = $dataFieldTemplates->get($fieldName);
            if (is_null($fieldTemplate)) {
                throw new \Exception("unknown field '$fieldName' specified in test meeting");
            }

            $meeting->data()->create([
                'key' => $fieldName,
                'field_prompt' => $fieldTemplate->field_prompt,
                'lang_enum' => 'en',
                'data_string' => $fieldValue,
                'visibility' => $fieldTemplate->visibility,
            ]);
        }

        foreach ($longDataFields as $fieldName => $fieldValue) {
            $fieldTemplate = $dataFieldTemplates->get($fieldName);
            if (is_null($fieldTemplate)) {
                throw new \Exception("unknown field '$fieldName' specified in test meeting");
            }

            $meeting->longdata()->create([
                'key' => $fieldName,
                'field_prompt' => $fieldTemplate->field_prompt,
                'lang_enum' => 'en',
                'data_blob' => $fieldValue,
                'visibility' => $fieldTemplate->visibility,
            ]);
        }

        return $meeting;
    }

    // users
    //
    //
    protected string $userPassword = 'goodpassword';

    protected function createAdminUser(): User
    {
        return User::create([
            'user_level_tinyint' => USER::USER_LEVEL_ADMIN,
            'name_string' => 'admin',
            'description_string' => 'nice description',
            'email_address_string' => 'email@email.com',
            'login_string' => 'admin',
            'password_string' => password_hash($this->userPassword, PASSWORD_DEFAULT),
        ]);
    }

    protected function createDeactivatedUser(): User
    {
        return User::create([
            'user_level_tinyint' => USER::USER_LEVEL_DEACTIVATED,
            'name_string' => 'deactivated',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'deactivated',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    protected function createServiceBodyObserverUser(): User
    {
        return User::create([
            'user_level_tinyint' => USER::USER_LEVEL_OBSERVER,
            'name_string' => 'sbobserver',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'sbobserver',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    protected function createServiceBodyAdminUser(): User
    {
        return User::create([
            'user_level_tinyint' => USER::USER_LEVEL_SERVICE_BODY_ADMIN,
            'name_string' => 'sbadmin',
            'description_string' => 'a description',
            'email_address_string' => 'email@email.com',
            'login_string' => 'sbadmin',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    // service bodies
    //
    //
    protected function createZone(string $name, string $description, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $adminUserId = null, array $assignedUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'ZF', 0, $uri, $helpline, $worldId, $email, $adminUserId, $assignedUserIds);
    }

    protected function createRegion(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $adminUserId = null, array $assignedUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'RS', $sbOwner, $uri, $helpline, $worldId, $email, $adminUserId, $assignedUserIds);
    }

    protected function createArea(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $adminUserId = null, array $assignedUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'AS', $sbOwner, $uri, $helpline, $worldId, $email, $adminUserId, $assignedUserIds);
    }

    protected function createServiceBody(string $name, string $description, string $sbType, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $adminUserId = null, array $assignedUserIds = null)
    {
        return ServiceBody::create([
            'sb_owner' => $sbOwner,
            'name_string' => $name,
            'description_string' => $description,
            'sb_type' => $sbType,
            'uri_string' => $uri,
            'kml_file_uri_string' => $helpline,
            'worldid_mixed' => $worldId,
            'sb_meeting_email' => $email ?? '',
            'principal_user_bigint' => $adminUserId,
            'editors_string' => !is_null($assignedUserIds) ? implode(',', $assignedUserIds) : null,
        ]);
    }
}
