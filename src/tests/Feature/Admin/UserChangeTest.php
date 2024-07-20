<?php

namespace Tests\Feature\Admin;

use App\Models\Change;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

class UserChangeTest extends TestCase
{
    use RefreshDatabase;

    public function testNewUserChange()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $data = [
            'username' => 'test',
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'description' => 'test description',
            'email' => 'test@test.com',
            'ownerId' => $user->id_bigint,
        ];

        $newUser = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/users', $data)
            ->json();

        $change = Change::query()->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($newUser['id'], $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals('c_comdef_user', $change->object_class_string);
        $this->assertNull($change->before_id_bigint);
        $this->assertNull($change->before_lang_enum);
        $this->assertEquals($newUser['id'], $change->after_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->after_lang_enum);
        $this->assertEquals('comdef_change_type_new', $change->change_type_enum);
        $this->assertNull($change->before_object);
        $this->assertNotNull($change->after_object);
    }

    public function testChangedUserChange()
    {
        $admin = $this->createAdminUser();
        $token = $admin->createToken('test')->plainTextToken;
        $user = $this->createServiceBodyAdminUser();
        $data = [
            'username' => 'test',
            'password' => 'this is a valid password',
            'type' => User::USER_TYPE_ADMIN,
            'displayName' => 'pretty name',
            'description' => 'test description',
            'email' => 'test@test.com',
            'ownerId' => $user->id_bigint,
        ];

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint", $data)
            ->assertStatus(204);

        $change = Change::query()->first();
        $this->assertEquals($admin->id_bigint, $change->user_id_bigint);
        $this->assertEquals($user->id_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals('c_comdef_user', $change->object_class_string);
        $this->assertEquals($user->id_bigint, $change->before_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->before_lang_enum);
        $this->assertEquals($user->id_bigint, $change->after_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->after_lang_enum);
        $this->assertEquals('comdef_change_type_change', $change->change_type_enum);
        $this->assertNotNull($change->before_object);
        $this->assertNotNull($change->after_object);

        $object = $change->before_object;
        $this->assertEquals($user->id_bigint, $object[0]);
        $this->assertEquals($user->user_level_tinyint, $object[1]);
        $this->assertEquals($user->email_address_string, $object[2]);
        $this->assertEquals($user->login_string, $object[3]);
        $this->assertEquals($user->password_string, $object[4]);
        $this->assertEquals($user->name_string, $object[6]);
        $this->assertEquals($user->description_string, $object[7]);
        $this->assertEquals(-1, $object[8]);
        $this->assertEquals(App::currentLocale(), $object[9]);
        $object = $change->after_object;
        $this->assertEquals($user->id_bigint, $object[0]);
        $this->assertEquals(User::USER_TYPE_TO_USER_LEVEL_MAP[$data['type']], $object[1]);
        $this->assertEquals($data['email'], $object[2]);
        $this->assertEquals($data['username'], $object[3]);
        $this->assertNotEquals($user->password_string, $object[4]);
        $this->assertEquals($data['displayName'], $object[6]);
        $this->assertEquals($data['description'], $object[7]);
        $this->assertEquals($data['ownerId'], $object[8]);
        $this->assertEquals(App::currentLocale(), $object[9]);
    }

    public function testDeletedUserChange()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(204);

        $change = Change::query()->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($user->id_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals('c_comdef_user', $change->object_class_string);
        $this->assertEquals($user->id_bigint, $change->before_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->before_lang_enum);
        $this->assertNull($change->after_id_bigint);
        $this->assertNull($change->after_lang_enum);
        $this->assertEquals('comdef_change_type_delete', $change->change_type_enum);
        $this->assertNotNull($change->before_object);
        $this->assertNull($change->after_object);
    }
}
