<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

class TokenTest extends PermissionsTestCase
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

    public function testRefreshUnauthenticated()
    {
        $this->post('/api/v1/auth/refresh')->assertStatus(401);
    }

    public function testRefreshSuccess()
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
        $originalTokenModel = PersonalAccessToken::findToken($originalToken);
        $this->assertNotNull($originalTokenModel->expires_at);
        $this->assertLessThanOrEqual(time() + 20, strtotime($originalTokenModel->expires_at->toDateTimeString()));
    }

    public function testExpiredToken()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $tokenModel = PersonalAccessToken::findToken($token);
        $tokenModel->expires_at = time() - 10;
        $tokenModel->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/servicebodies')
            ->assertStatus(401);
    }
}
