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
