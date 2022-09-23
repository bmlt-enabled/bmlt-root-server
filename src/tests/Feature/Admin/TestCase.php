<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceBody;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected string $userPassword = 'goodpassword';

    protected function createAdminUser(): User
    {
        return User::create([
            'user_level_tinyint' => USER::USER_LEVEL_ADMIN,
            'name_string' => 'admin',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'admin',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    protected function createDisabledUser(): User
    {
        return User::create([
            'user_level_tinyint' => USER::USER_LEVEL_DISABLED,
            'name_string' => 'disabled',
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'disabled',
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
            'description_string' => '',
            'email_address_string' => '',
            'login_string' => 'sbadmin',
            'password_string' => password_hash($this->userPassword, PASSWORD_BCRYPT),
        ]);
    }

    protected function createZone(string $name, string $description, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'ZF', 0, $uri, $helpline, $worldId, $email, $userId, $editorUserIds);
    }

    protected function createRegion(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'RS', $sbOwner, $uri, $helpline, $worldId, $email, $userId, $editorUserIds);
    }

    protected function createArea(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'AS', $sbOwner, $uri, $helpline, $worldId, $email, $userId, $editorUserIds);
    }

    protected function createServiceBody(string $name, string $description, string $sbType, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
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
            'principal_user_bigint' => $userId,
            'editors_string' => !is_null($editorUserIds) ? implode(',', $editorUserIds) : null,
        ]);
    }
}
