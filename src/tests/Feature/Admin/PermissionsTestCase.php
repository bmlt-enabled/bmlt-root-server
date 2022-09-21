<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTestCase extends TestCase
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
}
