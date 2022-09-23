<?php

namespace Tests\Feature\Admin;

use App\Models\ServiceBody;

use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteServiceBodyTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteServiceBodySuccess()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('test', 'test');

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$zone->id_bigint")
            ->assertStatus(204);

        $this->assertFalse(ServiceBody::query()->where('id_bigint', $zone->id_bigint)->exists());
    }

    public function testDeleteServiceBodyHasChildren()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('test', 'test');
        $this->createRegion('region', 'region', $zone->id_bigint);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$zone->id_bigint")
            ->assertStatus(409);

        $this->assertTrue(ServiceBody::query()->where('id_bigint', $zone->id_bigint)->exists());
    }

    public function testDeleteServiceBodyHasMeetings()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $zone = $this->createZone('test', 'test');
        $this->createMeeting(['service_body_bigint' => $zone->id_bigint]);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/servicebodies/$zone->id_bigint")
            ->assertStatus(409);

        $this->assertTrue(ServiceBody::query()->where('id_bigint', $zone->id_bigint)->exists());
    }
}
