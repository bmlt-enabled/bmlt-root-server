<?php

namespace Tests\Feature\Admin;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function toPayload(User $user): array
    {
        $values = [
            'username' => $user->login_string,
            'password' => 'this is a password',
            'type' => User::USER_LEVEL_TO_USER_TYPE_MAP[$user->user_level_tinyint],
            'displayName' => $user->name_string,
            'ownerId' => $user->owner_id_bigint == -1 ? null : $user->owner_id_bigint,
        ];

        if (!empty($user->description_string)) {
            $values['description'] = $user->description_string;
        }

        if (!empty($user->email_address_string)) {
            $values['email'] = $user->email_address_string;
        }

        return $values;
    }

    public function testUpdateUserAsAdmin()
    {
        $user1 = $this->createAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $data = [
            'username' => 'new username',
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'description' => 'test description',
            'email' => 'test@test.com',
            'ownerId' => $user1->id_bigint,
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);

        $oldPasswordHash = $user2->password_string;
        $user2->refresh();
        $this->assertEquals($data['username'], $user2->login_string);
        $this->assertNotEquals($oldPasswordHash, $user2->password_string);
        $this->assertEquals($data['type'], User::USER_LEVEL_TO_USER_TYPE_MAP[$user2->user_level_tinyint]);
        $this->assertEquals($data['displayName'], $user2->name_string);
        $this->assertEquals($data['description'], $user2->description_string);
        $this->assertEquals($data['email'], $user2->email_address_string);
        $this->assertEquals($data['ownerId'], $user2->owner_id_bigint);

        // validate nulling out ownerId comes out as -1 in the database
        $data['ownerId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);

        $user2->refresh();
        $this->assertEquals(-1, $user2->owner_id_bigint);
    }

    public function testUpdateUserAsServiceBodyAdmin()
    {
        $user1 = $this->createServiceBodyAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $user2->owner_id_bigint = $user1->id_bigint;
        $user2->save();

        $data = [
            'username' => 'new username',
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'description' => 'test description',
            'email' => 'test@test.com',
            'ownerId' => $user1->id_bigint,
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);

        $oldPasswordHash = $user2->password_string;
        $oldType = $user2->user_level_tinyint;
        $oldOwner = $user2->owner_id_bigint;
        $user2->refresh();

        // assert these did change
        $this->assertEquals($data['username'], $user2->login_string);
        $this->assertNotEquals($oldPasswordHash, $user2->password_string);
        $this->assertEquals($data['displayName'], $user2->name_string);
        $this->assertEquals($data['description'], $user2->description_string);
        $this->assertEquals($data['email'], $user2->email_address_string);
        // assert these did not change
        $this->assertEquals($oldType, $user2->user_level_tinyint);
        $this->assertEquals($oldOwner, $user2->owner_id_bigint);
    }

    public function testUpdateUserAsServiceBodyObserver()
    {
        $user1 = $this->createServiceBodyObserverUser();
        $user1->owner_id_bigint = -1;
        $user1->save();
        $token = $user1->createToken('test')->plainTextToken;

        $data = [
            'username' => 'new username',
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'description' => 'test description',
            'email' => 'test@test.com',
            'ownerId' => $user1->id_bigint,
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user1->id_bigint", $data)
            ->assertStatus(204);

        $oldUsername = $user1->login_string;
        $oldPasswordHash = $user1->password_string;
        $oldType = $user1->user_level_tinyint;
        $oldOwner = $user1->owner_id_bigint;
        $user1->refresh();

        // assert these did change
        $this->assertNotEquals($oldPasswordHash, $user1->password_string);
        $this->assertEquals($data['displayName'], $user1->name_string);
        $this->assertEquals($data['description'], $user1->description_string);
        $this->assertEquals($data['email'], $user1->email_address_string);
        // assert these did not change
        $this->assertEquals($oldUsername, $user1->login_string);
        $this->assertEquals($oldType, $user1->user_level_tinyint);
        $this->assertEquals($oldOwner, $user1->owner_id_bigint);
    }

    public function testUpdateUserExcludeOptionalFieldsFromPayload()
    {
        $user1 = $this->createAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $user2->description_string = 'a description';
        $user2->email_address_string = 'test@test.com';
        $user2->save();

        $data = [
            'username' => 'new username',
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'ownerId' => $user1->id_bigint,
        ];

        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);

        $user2->refresh();
        $this->assertEquals('', $user2->description_string);
        $this->assertEquals('', $user2->email_address_string);
    }

    public function testUpdateUserValidateUsername()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it is required
        unset($data['username']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['username'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['username'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be more than 255 characters long
        $data['username'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['username'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateUserValidatePassword()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it is required
        unset($data['password']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['password'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['password'] = 11111111111111111;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be less than 12 characters long
        $data['password'] = str_repeat('t', 11);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 12 characters long
        $data['password'] = str_repeat('t', 12);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateUserValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it is required
        unset($data['type']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid type
        $data['type'] = 'asdf';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be any valid type
        foreach (array_keys(User::USER_TYPE_TO_USER_LEVEL_MAP) as $type) {
            $data['type'] = $type;
            $this->withHeader('Authorization', "Bearer $token")
                ->put("/api/v1/users/$user->id_bigint", $data)
                ->assertStatus(204);
        }
    }

    public function testUpdateUserValidateDisplayName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it is required
        unset($data['displayName']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be null
        $data['displayName'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['displayName'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be more than 255 characters long
        $data['displayName'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['displayName'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateUserValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it can't be null
        $data['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['description'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be more than 1024 characters long
        $data['description'] = str_repeat('t', 1025);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['description'] = str_repeat('t', 1024);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        // it can be omitted
        unset($data['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateUserValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it can't be null
        $data['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid email
        $data['email'] = 'not a valid email';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid email
        $data['email'] = 'test@test.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        // it can be omitted
        unset($data['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testUpdateUserValidateOwnerId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = $this->toPayload($user);

        // it can't be an invalid user id
        $data['ownerId'] = 99999;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be null
        $data['ownerId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        // it can be a valid user id
        $user2 = $this->createServiceBodyAdminUser();
        $data['ownerId'] = $user2->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }
}
