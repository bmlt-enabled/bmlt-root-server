<?php

namespace Tests\Feature\Admin;

use App\Models\Format;

use Illuminate\Foundation\Testing\RefreshDatabase;

class FormatDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteFormat()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;
        $format = Format::query()->first();

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(204);

        $this->assertFalse(Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->exists());
    }
}
