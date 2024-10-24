<?php

namespace Tests\Feature\Admin;

use App\Models\Format;

use App\Repositories\FormatRepository;
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

    public function testDeleteFormatHasMeetingsOnly()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = Format::query()->first();
        $this->createMeeting(['formats' => $format->shared_id_bigint]);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(409);

        $this->assertTrue(Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->exists());
    }

    public function testDeleteFormatHasMeetingsFirst()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = Format::query()->first();
        $this->createMeeting(['formats' => "$format->shared_id_bigint,123"]);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(409);

        $this->assertTrue(Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->exists());
    }

    public function testDeleteFormatHasMeetingsMiddle()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = Format::query()->first();
        $this->createMeeting(['formats' => "123,$format->shared_id_bigint,7"]);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(409);

        $this->assertTrue(Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->exists());
    }

    public function testDeleteFormatHasMeetingsLast()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $format = Format::query()->first();
        $this->createMeeting(['formats' => "123,$format->shared_id_bigint"]);

        $this->withHeader('Authorization', "Bearer $token")
            ->delete("/api/v1/formats/$format->shared_id_bigint")
            ->assertStatus(409);

        $this->assertTrue(Format::query()->where('shared_id_bigint', $format->shared_id_bigint)->exists());
    }

    public function testDeleteReservedVenueTypeFormat()
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('test')->plainTextToken;

        $formatRepository = new FormatRepository();
        $virtualFormatId = $formatRepository->getVirtualFormat()->shared_id_bigint;
        $temporarilyClosedFormatId = $formatRepository->getTemporarilyClosedFormat()->shared_id_bigint;
        $hybridFormatId = $formatRepository->getHybridFormat()->shared_id_bigint;

        foreach ([$virtualFormatId, $temporarilyClosedFormatId, $hybridFormatId] as $formatId) {
            $this->withHeader('Authorization', "Bearer $token")
                ->delete("/api/v1/formats/$formatId")
                ->assertStatus(422);
        }
    }
}
