<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

class TokenTest extends TestCase
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
        $this->assertTrue($data['token_type'] == 'bearer');
        $this->assertEquals($data['user_id'], $user->id_bigint);

        // try to use the token
        $token = $data['access_token'];
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
        $originalToken = $this->createAdminUser()->createToken('test')->plainTextToken;

        // refresh originalToken, get newToken
        $newToken = $this->withHeader('Authorization', "Bearer $originalToken")
            ->post('/api/v1/auth/refresh')
            ->assertStatus(200)
            ->json()['access_token'];

        // verify newToken works
        $this->withHeader('Authorization', "Bearer $newToken")
            ->get('/api/v1/servicebodies')
            ->assertStatus(200);

        // make sure the originalToken expires soon
        $originalTokenModel = PersonalAccessToken::findToken($originalToken);
        $this->assertNotNull($originalTokenModel->expires_at);
        $this->assertLessThanOrEqual(time() + 20, strtotime($originalTokenModel->expires_at->toDateTimeString()));
    }

    public function testLogout()
    {
        $token = $this->createAdminUser()->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/auth/logout')
            ->assertStatus(200);

        $this->assertNull(PersonalAccessToken::findToken($token));
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

    public function testMigratePasswordHash()
    {
        $user = $this->createAdminUser();
        $user->password_string = crypt($this->userPassword, 'ab');
        $user->save();

        $this->post('/api/v1/auth/token', ['username' => $user->login_string, 'password' => $this->userPassword])
            ->assertStatus(200);

        $oldPasswordhash = $user->password_string;
        $user->refresh();
        $this->assertNotEmpty($user->password_string);
        $this->assertNotEquals($oldPasswordhash, $user->password_string);
    }

    public function testTokenAsDeactivated()
    {
        $user = $this->createDeactivatedUser();
        $this->post('/api/v1/auth/token', ['username' => $user->login_string, 'password' => $this->userPassword])
            ->assertStatus(403)
            ->assertJson([
                'message' => 'User is deactivated.'
            ]);
    }
}
