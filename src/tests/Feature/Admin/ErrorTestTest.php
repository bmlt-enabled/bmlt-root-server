<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ErrorTestTest extends TestCase
{
    use RefreshDatabase;

    public function testArbitraryString()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $data = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(200);

        $data = ['arbitrary_string' => 123];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(422)
            ->assertExactJson([
                'message' => 'The arbitrary string must be a string.',
                'errors' => [
                    'arbitrary_string' => ['The arbitrary string must be a string.']
                ],
            ]);

        $data = ['arbitrary_string' => 'a string'];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(200);
    }

    public function testArbitraryInt()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $data = [];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(200);

        $data = ['arbitrary_int' => 'a string'];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(422)
            ->assertExactJson([
                'message' => 'The arbitrary int must be an integer.',
                'errors' => [
                    'arbitrary_int' => ['The arbitrary int must be an integer.']
                ],
            ]);

        $data = ['arbitrary_int' => 123];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(200);
    }

    public function testForceServerError()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $data = ['force_server_error' => true];
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/errortest', $data)
            ->assertStatus(500)
            ->assertExactJson([
                'message' => 'Server Error'
            ]);
    }
}
