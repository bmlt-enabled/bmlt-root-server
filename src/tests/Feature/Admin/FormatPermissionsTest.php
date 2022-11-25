<?php

namespace Tests\Feature\Admin;

use App\LegacyConfig;
use App\Models\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormatPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    // index
    //
    //
    public function testIndexNotAuthenticated()
    {
        $this->get('/api/v1/formats')
            ->assertStatus(401);
    }

    public function testIndexAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/formats')
            ->assertStatus(403);
    }

    public function testIndexAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/formats')
            ->assertStatus(200);
    }

    public function testIndexAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/formats')
            ->assertStatus(200);
    }

    public function testIndexAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/v1/formats')
            ->assertStatus(200);
    }

    // show
    //
    //
    public function testShowAsUnauthenticated()
    {
        $this->get('/api/v1/formats/1')
            ->assertStatus(401);
    }

    public function testShowAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testShowAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200);
    }

    public function testShowAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(200);
    }

    // store
    //
    //
    public function testStoreAsUnauthenticated()
    {
        $this->post('/api/v1/formats')
            ->assertStatus(401);
    }

    public function testStoreAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/formats")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/formats")
            ->assertStatus(403);
    }

    public function testStoreAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/formats")
            ->assertStatus(403);
    }

    public function testStoreAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/formats")
            ->assertStatus(422);
    }

    public function testStoreWithAggregatorEnabledAsAdmin()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer $token")
            ->post("/api/v1/formats")
            ->assertStatus(403);
    }

    // update
    //
    //
    public function testUpdateAsUnauthenticated()
    {
        $format = Format::query()->first();
        $this->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(401);
    }

    public function testUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyAdminDenied()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testUpdateAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(422);
    }

    public function testUpdateWithAggregatorEnabledAsAdmin()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->put("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    // partial update
    //
    //
    public function testPartialUpdateAsUnauthenticated()
    {
        $format = Format::query()->first();
        $this->patch("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(401);
    }

    public function testPartialUpdateAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testPartialUpdateAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(204);
    }

    public function testPartialUpdateWithAggregatorEnabledAsAdmin()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->patch("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    // delete
    //
    //
    public function testDeleteAsUnauthenticated()
    {
        $format = Format::query()->first();
        $this->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(401);
    }

    public function testDeleteAsDisabled()
    {
        $user = $this->createDisabledUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyObserver()
    {
        $user = $this->createServiceBodyObserverUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsServiceBodyAdmin()
    {
        $user = $this->createServiceBodyAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }

    public function testDeleteAsAdmin()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(204);
    }

    public function testDeleteWithAggregatorEnabledAsAdmin()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();
        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(403);
    }
}
