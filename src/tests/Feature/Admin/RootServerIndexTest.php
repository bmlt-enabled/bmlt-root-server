<?php

namespace Tests\Feature\Admin;

use App\LegacyConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RootServerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        try {
            $this->createRootServer(123);
            $this->createRootServer(123, 'test2', 'https://test2.com');
            $this->get("/api/v1/rootservers")
                ->assertStatus(200)
                ->assertJsonCount(2);
        } finally {
            LegacyConfig::reset();
        }
    }
}
