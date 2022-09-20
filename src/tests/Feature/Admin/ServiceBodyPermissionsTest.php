<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ServiceBody;

class ServiceBodyPermissionsTest extends PermissionsTestCase
{
    use RefreshDatabase;

    private function createZone(string $name, string $description, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'ZF', 0, $uri, $helpline, $worldId, $email, $userId, $editorUserIds);
    }

    private function createRegion(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'RS', $sbOwner, $uri, $helpline, $worldId, $email, $userId, $editorUserIds);
    }

    private function createArea(string $name, string $description, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return $this->createServiceBody($name, $description, 'AS', $sbOwner, $uri, $helpline, $worldId, $email, $userId, $editorUserIds);
    }

    private function createServiceBody(string $name, string $description, string $sbType, int $sbOwner, string $uri = null, string $helpline = null, string $worldId = null, string $email = null, int $userId = null, array $editorUserIds = null)
    {
        return ServiceBody::create([
            'sb_owner' => $sbOwner,
            'name_string' => $name,
            'description_string' => $description,
            'sb_type' => $sbType,
            'uri_string' => $uri,
            'kml_file_uri_string' => $helpline,
            'worldid_mixed' => $worldId,
            'sb_meeting_email' => $email ?? '',
            'principal_user_bigint' => $userId,
            'editors_string' => !is_null($editorUserIds) ? implode(',', $editorUserIds) : null,
        ]);
    }

    // index
    //
    //
    public function testIndexNotAuthenticated()
    {
        $this->get('/api/v1/servicebodies')
            ->assertStatus(401);
    }

    public function testIndexAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/servicebodies')
            ->assertStatus(403);
    }

    public function testIndexAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $region = $this->createRegion('region', 'region', 0);
        $area1 = $this->createArea('area1', 'area1', $region->id_bigint, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/servicebodies')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $area1->id_bigint]);
    }

    public function testIndexAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $region = $this->createRegion('region', 'region', 0);
        $area1 = $this->createArea('area1', 'area1', $region->id_bigint, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/servicebodies')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $area1->id_bigint]);
    }

    public function testIndexAsAdmin()
    {
        $this->createZone('zone', 'zone');
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/servicebodies')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    // show
    //
    //
    public function testShowAsUnauthenticated()
    {
        $this->get('/api/v1/servicebodies/1')
            ->assertStatus(401);
    }

    public function testShowAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testShowAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    // store
    //
    //
    public function testStoreAsUnauthenticated()
    {
        $this->post('/api/v1/servicebodies')
            ->assertStatus(401);
    }

    public function testStoreAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/servicebodies")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/servicebodies")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/servicebodies")
            ->assertStatus(403);
    }

    public function testStoreAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/servicebodies")
            ->assertStatus(200);
    }

    // update
    //
    //
    public function testUpdateAsUnauthenticated()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $this->put("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(401);
    }

    public function testUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyAdminDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyAdminAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    public function testUpdateAsAdminAllowed()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    // partial update
    //
    //
    public function testPartialUpdateAsUnauthenticated()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $this->patch("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(401);
    }

    public function testPartialUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyAdminDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyAdminAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    public function testPartialUpdateAsAdminAllowed()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }

    // delete
    //
    //
    public function testDeleteAsUnauthenticated()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $this->delete("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(401);
    }

    public function testDeleteAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$area1->id_bigint")
            ->assertStatus(200);
    }
}
