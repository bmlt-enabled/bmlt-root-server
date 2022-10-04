<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceBody;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceBodyShowTest extends TestCase
{
    use RefreshDatabase;

    public function testShowServiceBodyName()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['name']);
        $this->assertEquals($area->name_string, $data['name']);
    }

    public function testShowServiceBodyDescription()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['description']);
        $this->assertEquals($area->description_string, $data['description']);
    }

    public function testShowServiceBodyParentIdZero()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['parentId']);
    }

    public function testShowServiceBodyParentIdNotZero()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 1);
        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsInt($data['parentId']);
        $this->assertEquals($area->sb_owner, $data['parentId']);
    }

    public function testShowServiceBodyParentIdNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->sb_owner = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['parentId']);
    }

    public function testShowServiceBodyTypeNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->sb_type = ServiceBody::SB_TYPE_AREA;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['type']);
        $this->assertEquals($area->sb_type, $data['type']);
    }

    public function testShowServiceBodyTypeNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->sb_type = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['type']);
    }

    public function testShowServiceBodyUserIdNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->principal_user_bigint = 123;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsInt($data['adminUserId']);
        $this->assertEquals($area->principal_user_bigint, $data['adminUserId']);
    }

    public function testShowServiceBodyUserIdNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->principal_user_bigint = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['adminUserId']);
    }

    public function testShowServiceBodyassignedUserIdsNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->editors_string = '1,2,3';
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsArray($data['assignedUserIds']);
        $this->assertEquals([1,2,3], $data['assignedUserIds']);
    }

    public function testShowServiceBodyassignedUserIdsNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->editors_string = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsArray($data['assignedUserIds']);
        $this->assertEquals([], $data['assignedUserIds']);
    }

    public function testShowServiceBodyUrlNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->uri_string = 'https://na.org';
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['url']);
        $this->assertEquals($area->uri_string, $data['url']);
    }

    public function testShowServiceBodyUrlNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->uri_string = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['url']);
    }

    public function testShowServiceBodyHelplineNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->kml_file_uri_string = 'test';
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['helpline']);
        $this->assertEquals($area->kml_file_uri_string, $data['helpline']);
    }

    public function testShowServiceBodyHelplineNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->kml_file_uri_string = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['helpline']);
    }

    public function testShowServiceBodyEmail()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->sb_meeting_email = 'test';
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['email']);
        $this->assertEquals($area->sb_meeting_email, $data['email']);
    }

    public function testShowServiceBodyWorldIdNotNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->worldid_mixed = 'test';
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertIsString($data['worldId']);
        $this->assertEquals($area->worldid_mixed, $data['worldId']);
    }

    public function testShowServiceBodyWorldIdNull()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $area = $this->createArea('test name', 'test desc', 0);
        $area->worldid_mixed = null;
        $area->save();

        $data = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area->id_bigint")
            ->assertStatus(200)
            ->json();

        $this->assertNull($data['worldId']);
    }
}
