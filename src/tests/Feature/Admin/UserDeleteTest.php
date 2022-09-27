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
}
