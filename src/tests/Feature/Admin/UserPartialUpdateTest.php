<?php

namespace Tests\Feature\Admin;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPartialUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function testPartialUpdateUserAsAdmin()
    {
        $user1 = $this->createAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();

        $data = ['username' => 'new username'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['username'], $user2->login_string);

        $data = ['password' => 'this is a valid password'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $oldPasswordHash = $user2->password_string;
        $user2->refresh();
        $this->assertNotEquals($oldPasswordHash, $user2->password_string);

        $data = ['type' => User::USER_TYPE_ADMIN];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals(User::USER_TYPE_TO_USER_LEVEL_MAP[$data['type']], $user2->user_level_tinyint);

        $data = ['displayName' => 'pretty name'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['displayName'], $user2->name_string);

        $data = ['description' => 'pretty new description'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['description'], $user2->description_string);

        $data = ['email' => 'new@email.com'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['email'], $user2->email_address_string);

        $data = ['ownerId' => $user1->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['ownerId'], $user2->owner_id_bigint);

        $data = ['ownerId' => null];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals(-1, $user2->owner_id_bigint);
    }

    public function testPartialUpdateUserAsServiceBodyAdmin()
    {
        $user1 = $this->createServiceBodyAdminUser();
        $token = $user1->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $user2->owner_id_bigint = $user1->id_bigint;
        $user2->save();

        $data = ['username' => 'new username'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['username'], $user2->login_string);

        $data = ['password' => 'this is a valid password'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $oldPasswordHash = $user2->password_string;
        $user2->refresh();
        $this->assertNotEquals($oldPasswordHash, $user2->password_string);

        $data = ['displayName' => 'pretty name'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['displayName'], $user2->name_string);

        $data = ['description' => 'pretty new description'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['description'], $user2->description_string);

        $data = ['email' => 'new@email.com'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $user2->refresh();
        $this->assertEquals($data['email'], $user2->email_address_string);

        // did not change
        $data = ['type' => User::USER_TYPE_ADMIN];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $oldType = $user2->user_level_tinyint;
        $user2->refresh();
        $this->assertEquals($oldType, $user2->user_level_tinyint);

        // did not change
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $oldOwner = $user2->owner_id_bigint;
        $user2->refresh();
        $this->assertEquals($oldOwner, $user2->owner_id_bigint);

        // did not change
        $data = ['ownerId' => null];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint", $data)
            ->assertStatus(204);
        $oldOwner = $user2->owner_id_bigint;
        $user2->refresh();
        $this->assertEquals($oldOwner, $user2->owner_id_bigint);
    }
    public function testPartialUpdateUserAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;

        $data = ['password' => 'this is a valid password'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $oldPasswordHash = $user->password_string;
        $user->refresh();
        $this->assertNotEquals($oldPasswordHash, $user->password_string);

        $data = ['displayName' => 'pretty name'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $user->refresh();
        $this->assertEquals($data['displayName'], $user->name_string);

        $data = ['description' => 'pretty new description'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $user->refresh();
        $this->assertEquals($data['description'], $user->description_string);

        $data = ['email' => 'new@email.com'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $user->refresh();
        $this->assertEquals($data['email'], $user->email_address_string);

        // did not change
        $data = ['username' => 'new username'];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $oldUsername = $user->login_string;
        $user->refresh();
        $this->assertEquals($oldUsername, $user->login_string);

        // did not change
        $data = ['type' => User::USER_TYPE_ADMIN];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $oldType = $user->user_level_tinyint;
        $user->refresh();
        $this->assertEquals($oldType, $user->user_level_tinyint);

        // did not change
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $oldOwner = $user->owner_id_bigint;
        $user->refresh();
        $this->assertEquals($oldOwner, $user->owner_id_bigint);

        // did not change
        $data = ['ownerId' => null];
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
        $oldOwner = $user->owner_id_bigint;
        $user->refresh();
        $this->assertEquals($oldOwner, $user->owner_id_bigint);
    }

    public function testPartialUpdateUserValidateUsername()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be null
        $data['username'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['username'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be more than 255 characters long
        $data['username'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['username'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateUserValidatePassword()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be null
        $data['password'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['password'] = 11111111111111111;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be less than 12 characters long
        $data['password'] = str_repeat('t', 11);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 12 characters long
        $data['password'] = str_repeat('t', 12);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateUserValidateType()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be null
        $data['type'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid type
        $data['type'] = 'asdf';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be any valid type
        foreach (array_keys(User::USER_TYPE_TO_USER_LEVEL_MAP) as $type) {
            $data['type'] = $type;
            $this->withHeader('Authorization', "Bearer $token")
                ->patch("/api/v1/users/$user->id_bigint", $data)
                ->assertStatus(204);
        }
    }

    public function testPartialUpdateUserValidateDisplayName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be null
        $data['displayName'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['displayName'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be more than 255 characters long
        $data['displayName'] = str_repeat('t', 256);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['displayName'] = str_repeat('t', 255);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateUserValidateDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be null
        $data['description'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it has to be a string
        $data['description'] = 1;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be more than 1024 characters long
        $data['description'] = str_repeat('t', 1025);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be 255 characters long
        $data['description'] = str_repeat('t', 1024);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        // it can be omitted
        unset($data['description']);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateUserValidateEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be null
        $data['email'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can't be an invalid email
        $data['email'] = 'not a valid email';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be a valid email
        $data['email'] = 'test@test.com';
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        // it can be omitted
        unset($data['email']);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }

    public function testPartialUpdateUserValidateOwnerId()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [];

        // it can't be an invalid user id
        $data['ownerId'] = 99999;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(422);

        // it can be null
        $data['ownerId'] = null;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        // it can be a valid user id
        $user2 = $this->createServiceBodyAdminUser();
        $data['ownerId'] = $user2->id_bigint;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);
    }
}
