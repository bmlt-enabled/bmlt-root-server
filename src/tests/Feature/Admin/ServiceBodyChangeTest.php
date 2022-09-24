<?php

namespace Tests\Feature\Admin;

use App\Models\Change;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

class ServiceBodyChangeTest extends TestCase
{
    use RefreshDatabase;

    public function testNewServiceBodyChange()
    {
        $user = $this->createAdminUser();
        $user2 = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone', 'zone');
        $data = [
            'parentId' => $zone->id_bigint,
            'name' => 'test name',
            'description' => 'test description',
            'type' => 'AS',
            'userId' => $user->id_bigint,
            'editorUserIds' => [$user->id_bigint, $user2->id_bigint],
            'url' => 'http://blah.com',
            'helpline' => '555-555-5555',
            'worldId' => 'test world id',
            'email' => 'test@test.com',
        ];

        $serviceBody = $this
            ->withHeader('Authorization', "Bearer $token")
            ->post('/api/v1/servicebodies', $data)
            ->json();

        $change = Change::query()->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($serviceBody['id'], $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals($change->object_class_string, 'c_comdef_service_body');
        $this->assertNull($change->before_id_bigint);
        $this->assertNull($change->before_lang_enum);
        $this->assertEquals($change->after_id_bigint, $serviceBody['id']);
        $this->assertEquals(App::currentLocale(), $change->after_lang_enum);
        $this->assertEquals($change->change_type_enum, 'comdef_change_type_new');
        $this->assertNull($change->before_object);
        $this->assertNotNull($change->after_object);
    }

    public function testChangedServiceBodyChange()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone name', 'zone description', userId: $user->id_bigint);
        $data = [
            'parentId' => $zone->id_bigint,
            'name' => $zone->name_string . ' modified',
            'description' => $zone->description_string,
            'type' => $zone->sb_type,
            'userId' => $zone->principal_user_bigint,
            'editorUserIds' => [],
            'url' => 'http://blah.com',
            'helpline' => 'new helpline',
            'worldId' => 'new worldId',
            'email' => 'test@test.com',
        ];

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$zone->id_bigint", $data)
            ->assertStatus(204);

        $change = Change::query()->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($zone->id_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals('c_comdef_service_body', $change->object_class_string);
        $this->assertEquals($zone->id_bigint, $change->before_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->before_lang_enum);
        $this->assertEquals($zone->id_bigint, $change->after_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->after_lang_enum);
        $this->assertEquals('comdef_change_type_change', $change->change_type_enum);
        $this->assertNotNull($change->before_object);
        $this->assertNotNull($change->after_object);

        $object = $change->before_object;
        $this->assertEquals('', $object[12]);
        $object = $change->after_object;
        $this->assertEquals($data['email'], $object[12]);
    }

    public function testDeletedServiceBodyChange()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('zone name', 'zone description');

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$zone->id_bigint")
            ->assertStatus(204);

        $change = Change::query()->first();
        $this->assertEquals($user->id_bigint, $change->user_id_bigint);
        $this->assertEquals($zone->id_bigint, $change->service_body_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->lang_enum);
        $this->assertEquals('c_comdef_service_body', $change->object_class_string);
        $this->assertEquals($zone->id_bigint, $change->before_id_bigint);
        $this->assertEquals(App::currentLocale(), $change->before_lang_enum);
        $this->assertNull($change->after_id_bigint);
        $this->assertNull($change->after_lang_enum);
        $this->assertEquals('comdef_change_type_delete', $change->change_type_enum);
        $this->assertNotNull($change->before_object);
        $this->assertNull($change->after_object);
    }

    public function testServiceBodyChangeSerializationNulls()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $zone = $this->createZone('zone name', 'zone description');
        $zone->worldid_mixed = null;
        $zone->kml_file_uri_string = null;
        $zone->principal_user_bigint = null;
        $zone->editors_string = null;
        $zone->uri_string = null;
        $zone->sb_type = null;
        $zone->sb_owner = null;
        $zone->sb_owner_2 = null;
        $zone->save();

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$zone->id_bigint")
            ->assertStatus(204);

        $change = Change::query()->first();
        $object = $change->before_object;
        $this->assertEquals($zone->id_bigint, $object[0]);
        $this->assertEquals('', $object[1]);
        $this->assertEquals('', $object[2]);
        $this->assertEquals('', $object[3]);
        $this->assertEquals('', $object[4]);
        $this->assertEquals('', $object[5]);
        $this->assertEquals($zone->name_string, $object[6]);
        $this->assertEquals($zone->description_string, $object[7]);
        $this->assertEquals('en', $object[8]);
        $this->assertEquals('', $object[9]);
        $this->assertEquals('', $object[10]);
        $this->assertEquals('', $object[11]);
        $this->assertEquals($zone->sb_meeting_email, $object[12]);
    }

    public function testServiceBodyChangeSerializationNoNulls()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();

        // null out everything that is nullable
        $zone = $this->createZone('zone', 'zone');
        $area = $this->createArea('zone name', 'zone description', $zone->id_bigint);
        $area->worldid_mixed = 'test world id';
        $area->kml_file_uri_string = 'test helpline number';
        $area->principal_user_bigint = $user->id_bigint;
        $area->editors_string = "$user->id_bigint,$user2->id_bigint";
        $area->uri_string = 'http://blah.com';
        $area->sb_type = 'AS';
        $area->sb_owner = $zone->id_bigint;
        $area->sb_owner_2 = $zone->id_bigint;
        $area->sb_meeting_email = 'test@test.com';
        $area->lang_enum = 'en';
        $area->save();

        $this
            ->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(204);

        $change = Change::query()->first();
        $object = $change->before_object;
        $this->assertEquals($area->id_bigint, $object[0]);
        $this->assertEquals($area->principal_user_bigint, $object[1]);
        $this->assertEquals(collect($area->editors_string)->join(','), $object[2]);
        $this->assertEquals($area->kml_file_uri_string, $object[3]);
        $this->assertEquals($area->uri_string, $object[4]);
        $this->assertEquals($area->worldid_mixed, $object[5]);
        $this->assertEquals($area->name_string, $object[6]);
        $this->assertEquals($area->description_string, $object[7]);
        $this->assertEquals($area->lang_enum, $object[8]);
        $this->assertEquals($area->sb_type, $object[9]);
        $this->assertEquals($area->sb_owner, $object[10]);
        $this->assertEquals($area->sb_owner_2, $object[11]);
        $this->assertEquals($area->sb_meeting_email, $object[12]);
    }
}
