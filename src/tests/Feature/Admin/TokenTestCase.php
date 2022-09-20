<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

class TokenTestCase extends PermissionsTestCase
{
    use RefreshDatabase;

    public function testLoginDeniedFromUser()
    {
        $user = $this->createAdminUser();
        $this->post('/api/v1/auth/token', ['username' => 'wrong', 'password' => $this->userPassword])
            ->assertStatus(401);
    }

    public function testLoginDeniedFromPassword()
    {
        $user = $this->createAdminUser();
        $this->post('/api/v1/auth/token', ['username' => $user->login_string, 'password' => 'wrong'])
            ->assertStatus(401);
    }

    public function testLoginSuccess()
    {
        $user = $this->createAdminUser();
        $data = $this->post('/api/v1/auth/token', ['username' => $user->login_string, 'password' => $this->userPassword])
            ->assertStatus(200)
            ->json();

        // reported expiration looks reasonable
        $this->assertTrue($data['expires_at'] >= (time() + config('sanctum.expiration')) - 1);

        // try to use the token
        $token = $data['token'];
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/servicebodies')
            ->assertStatus(200);
    }

    public function testRefresh()
    {
        $user = $this->createAdminUser();
        $originalToken = $this->post('/api/v1/auth/token', ['username' => $user->login_string, 'password' => $this->userPassword])
            ->assertStatus(200)
            ->json()['token'];

        // refresh originalToken, get newToken
        $newToken = $this->withHeader('Authorization', "Bearer $originalToken")
            ->post('/api/v1/auth/refresh')
            ->assertStatus(200)
            ->json()['token'];

        // verify newToken works
        $this->withHeader('Authorization', "Bearer $newToken")
            ->get('/api/v1/servicebodies')
            ->assertStatus(200);

        // make sure the originalToken expires soon
        $originalToken = PersonalAccessToken::findToken($originalToken);
        $this->assertNotNull($originalToken->expires_at);
        $this->assertLessThanOrEqual(time() + 20, strtotime($originalToken->expires_at->toDateTimeString()));
    }
}
