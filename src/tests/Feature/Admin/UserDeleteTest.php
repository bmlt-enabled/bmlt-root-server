<?php

namespace Tests\Feature\Admin;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteUser()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user2->id_bigint")
            ->assertStatus(204);

        $this->assertFalse(User::query()->where('id_bigint', $user2->id_bigint)->exists());
    }

    public function testDeleteUserHasServiceBodies()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $this->createArea('area1', 'area1', 0, adminUserId: $user2->id_bigint);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user2->id_bigint")
            ->assertStatus(409);

        $this->assertTrue(User::query()->where('id_bigint', $user2->id_bigint)->exists());
    }

    public function testDeleteUserAssignedServiceBodies()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $user2 = $this->createServiceBodyAdminUser();
        $area1 = $this->createArea('area1', 'area1', 0, adminUserId: $user->id_bigint, assignedUserIds: [$user->id_bigint, $user2->id_bigint]);
        $area2 = $this->createArea('area2', 'area2', 0, adminUserId: $user->id_bigint, assignedUserIds: [$user2->id_bigint]);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user2->id_bigint")
            ->assertStatus(204);

        $this->assertFalse(User::query()->where('id_bigint', $user2->id_bigint)->exists());

        $area1->refresh();
        $this->assertEquals(strval($user->id_bigint), $area1->editors_string);

        $area2->refresh();
        $this->assertEquals($user->id_bigint, $area2->principal_user_bigint);
        $this->assertEquals('', $area2->editors_string);
    }

    public function testDeleteUserReassignsOrphans()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $user2 = $this->createServiceBodyAdminUser();
        $user2->owner_id_bigint = $user->id_bigint;
        $user2->save();
        $user3 = $this->createServiceBodyObserverUser();
        $user3->owner_id_bigint = $user2->id_bigint;
        $user3->save();

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/users/$user->id_bigint")
            ->assertStatus(204);

        $user2->refresh();
        $this->assertEquals(-1, $user2->owner_id_bigint);

        $user3->refresh();
        $this->assertEquals($user2->id_bigint, $user3->owner_id_bigint);
    }
}
