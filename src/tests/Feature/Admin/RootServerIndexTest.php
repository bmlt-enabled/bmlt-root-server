<?php

namespace Tests\Feature\Admin;

use App\LegacyConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RootServerIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    public function test()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $this->createRootServer(123);
        $this->createRootServer(123, 'test2', 'https://test2.com');
        $this->get("/api/v1/rootservers")
            ->assertStatus(200)
            ->assertJsonCount(2);
    }
}
