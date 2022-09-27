<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    // index
    //
    //
    public function testIndexNotAuthenticated()
    {
        $this->get('/api/v1/users')
            ->assertStatus(401);
    }

    public function testIndexAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/users')
            ->assertStatus(403);
    }

    public function testIndexAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $user->id_bigint]);
    }

    public function testIndexAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $user3 = $this->createDisabledUser();

        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $user->id_bigint])
            ->assertJsonFragment(['id' => $user2->id_bigint]);
    }

    public function testIndexAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->createServiceBodyObserverUser();
        $this->createDisabledUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    // show
    //
    //
    public function testShowAsUnauthenticated()
    {
        $this->get('/api/v1/users/1')
            ->assertStatus(401);
    }

    public function testShowAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user->id_bigint")
            ->assertStatus(403);
    }

    public function testShowSelfAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user->id_bigint")
            ->assertStatus(200);
    }

    public function testShowOtherAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testShowOwnedAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testShowSelfAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user->id_bigint")
            ->assertStatus(200);
    }

    public function testShowOtherAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testShowOwnedAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user2->id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/users/$user2->id_bigint")
            ->assertStatus(200);
    }

    // store
    //
    //
    public function testStoreAsUnauthenticated()
    {
        $this->post('/api/v1/users')
            ->assertStatus(401);
    }

    public function testStoreAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/users")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/users")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/users")
            ->assertStatus(403);
    }

    public function testStoreAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/users")
            ->assertStatus(422);
    }

    // update
    //
    //
    public function testUpdateAsUnauthenticated()
    {
        $user = $this->createAdminUser();
        $this->put("/api/v1/users/$user->id_bigint")
            ->assertStatus(401);
    }

    public function testUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateSelfAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint")
            ->assertStatus(200);
    }

    public function testUpdateOtherAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateOwnedAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateSelfAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user->id_bigint")
            ->assertStatus(200);
    }

    public function testUpdateOtherAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateOwnedAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/users/$user2->id_bigint")
            ->assertStatus(200);
    }

    // partial update
    //
    //
    public function testPartialUpdateAsUnauthenticated()
    {
        $user = $this->createAdminUser();
        $this->patch("/api/v1/users/$user->id_bigint")
            ->assertStatus(401);
    }

    public function testPartialUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateSelfAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint")
            ->assertStatus(200);
    }

    public function testPartialUpdateOtherAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateOwnedAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateSelfAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user->id_bigint")
            ->assertStatus(200);
    }

    public function testPartialUpdateOtherAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateOwnedAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyObserverUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/users/$user2->id_bigint")
            ->assertStatus(200);
    }


    // delete
    //
    //
    public function testDeleteAsUnauthenticated()
    {
        $user = $this->createAdminUser();
        $this->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(401);
    }

    public function testDeleteAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(204);
    }
}
