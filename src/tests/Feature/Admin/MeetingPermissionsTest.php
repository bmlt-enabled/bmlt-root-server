<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;

class MeetingPermissionsTest extends TestCase
{
    use RefreshDatabase;

    // index
    //
    //
    public function testIndexNotAuthenticated()
    {
        $this->get('/api/v1/meetings')
            ->assertStatus(401);
    }

    public function testIndexAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings')
            ->assertStatus(403);
    }

    public function testIndexAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, editorUserIds: [$user->id_bigint]);
        $area2 = $this->createArea('area2', 'area2', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $meeting1->id_bigint]);
    }

    public function testIndexAsServiceBodyAdminOwner()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $area2 = $this->createArea('area2', 'area2', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $meeting1->id_bigint]);
    }

    public function testIndexAsServiceBodyAdminEditor()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, editorUserIds: [$user->id_bigint]);
        $area2 = $this->createArea('area2', 'area2', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $meeting1->id_bigint]);
    }

    public function testIndexAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $area2 = $this->createArea('area2', 'area2', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $meeting2 = $this->createMeeting(['service_body_bigint' => $area2->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/meetings')
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    // show
    //
    //
    public function testShowAsUnauthenticated()
    {
        $this->get('/api/v1/meetings/1')
            ->assertStatus(401);
    }

    public function testShowAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testShowAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, editorUserIds: [$user->id_bigint]);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsServiceBodyAdminOwner()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsServiceBodyAdminEditor()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, editorUserIds: [$user->id_bigint]);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(200);
    }

    // store
    //
    //
    public function testStoreAsUnauthenticated()
    {
        $this->post('/api/v1/meetings')
            ->assertStatus(401);
    }

    public function testStoreAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyAdminOwnerAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $data = ['serviceBodyId' => $area1->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings", $data)
            ->assertStatus(422);
    }

    public function testStoreAsServiceBodyAdminOwnerDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $data = ['serviceBodyId' => $area1->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings", $data)
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyAdminEditorAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, editorUserIds: [$user->id_bigint]);
        $data = ['serviceBodyId' => $area1->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings", $data)
            ->assertStatus(422);
    }

    public function testStoreAsServiceBodyAdminEditorDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $data = ['serviceBodyId' => $area1->id_bigint];
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings", $data)
            ->assertStatus(403);
    }

    public function testStoreAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/meetings")
            ->assertStatus(422);
    }

    // update
    //
    //
    public function testUpdateAsUnauthenticated()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->put("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(401);
    }

    public function testUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyAdminDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyAdminAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(422);
    }

    public function testUpdateAsAdminAllowed()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(422);
    }

    // partial update
    //
    //
    public function testPartialUpdateAsUnauthenticated()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->patch("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(401);
    }

    public function testPartialUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyAdminDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyAdminAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(422);
    }

    public function testPartialUpdateAsAdminAllowed()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(422);
    }

    // delete
    //
    //
    public function testDeleteAsUnauthenticated()
    {
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->delete("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(401);
    }

    public function testDeleteAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyAdminDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyAdminAllowed()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0, userId: $user->id_bigint);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(204);
    }

    public function testDeleteAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $area1 = $this->createArea('area1', 'area1', 0);
        $meeting1 = $this->createMeeting(['service_body_bigint' => $area1->id_bigint]);
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/meetings/$meeting1->id_bigint")
            ->assertStatus(204);
    }
}
