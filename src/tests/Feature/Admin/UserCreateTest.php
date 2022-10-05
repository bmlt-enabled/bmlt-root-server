<?php

namespace Tests\Feature\Admin;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload($username = 'user'): array
    {
        return [
            'username' => $username,
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'description' => 'test description',
            'email' => 'test@test.com',
            'ownerId' => null,
        ];
    }

    public function testCreateSuccessNoOwner()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();
        $data['ownerId'] = null;

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['username' => $data['username']])
            ->assertJsonFragment(['type' => $data['type']])
            ->assertJsonFragment(['displayName' => $data['displayName']])
            ->assertJsonFragment(['description' => $data['description']])
            ->assertJsonFragment(['email' => $data['email']])
            ->assertJsonFragment(['ownerId' => $data['ownerId']])
            ->json();

        $this->assertTrue(!isset($data['password']));

        $user = User::query()->firstWhere('id_bigint', $data['id']);
        $this->assertEquals(-1, $user->owner_id_bigint);
    }

    public function testCreateSuccessWithOwner()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();
        $user2 = $this->createServiceBodyAdminUser();
        $data['ownerId'] = $user2->id_bigint;

        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->assertJsonFragment(['username' => $data['username']])
            ->assertJsonFragment(['type' => $data['type']])
            ->assertJsonFragment(['displayName' => $data['displayName']])
            ->assertJsonFragment(['description' => $data['description']])
            ->assertJsonFragment(['email' => $data['email']])
            ->assertJsonFragment(['ownerId' => $data['ownerId']]);
    }

    public function testStoreUserValidateUsername()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['username']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be null
        $data['username'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it has to be a string
        $data['username'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be more than 255 characters long
        $data['username'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['username'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201);
    }

    public function testStoreUserValidatePassword()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['password']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be null
        $data['password'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it has to be a string
        $data['password'] = 11111111111111111;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be less than 12 characters long
        $data['password'] = str_repeat('t', 11);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be 12 characters long
        $data['password'] = str_repeat('t', 12);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201);
    }

    public function testStoreUserValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['type']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be an invalid type
        $data['type'] = 'asdf';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be any valid type
        foreach (array_keys(User::USER_TYPE_TO_USER_LEVEL_MAP) as $type) {
            $data['type'] = $type;
            $id = $this->withHeader('Authorization', "Bearer $token")
                ->post('/api/v1/users', $data)
                ->assertStatus(201)
                ->json()['id'];
            User::query()->where('id_bigint', $id)->delete();
        }
    }

    public function testStoreUserValidateDisplayName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it is required
        unset($data['displayName']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be null
        $data['displayName'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it has to be a string
        $data['displayName'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be more than 255 characters long
        $data['displayName'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['displayName'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201);
    }

    public function testStoreUserValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it has to be a string
        $data['description'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can't be more than 1024 characters long
        $data['description'] = str_repeat('t', 1025);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['description'] = str_repeat('t', 1024);
        $id = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->json()['id'];
        User::query()->where('id_bigint', $id)->delete();

        // it can be null
        $data['description'] = null;
        $id = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->json()['id'];
        User::query()->where('id_bigint', $id)->delete();

        // it can be omitted
        unset($data['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201);
    }

    public function testStoreUserValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it can't be an invalid email
        $data['email'] = 'not a valid email';
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be a valid email
        $data['email'] = 'test@test.com';
        $id = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->json()['id'];
        User::query()->where('id_bigint', $id)->delete();

        // it can be null
        $data['email'] = null;
        $id = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->json()['id'];
        User::query()->where('id_bigint', $id)->delete();

        // it can be omitted
        unset($data['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201);
    }

    public function testStoreUserValidateOwnerId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->validPayload();

        // it can't be an invalid user id
        $data['ownerId'] = 99999;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(422);

        // it can be null
        $data['ownerId'] = null;
        $id = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201)
            ->json()['id'];
        User::query()->where('id_bigint', $id)->delete();

        // it can be a valid user id
        $user2 = $this->createServiceBodyAdminUser();
        $data['ownerId'] = $user2->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->assertStatus(201);
    }
}
