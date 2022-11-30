<?php

namespace Tests\Feature\Admin;

use App\LegacyConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nette\Utils\DateTime;

class RootServerShowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    public function testNullLastSuccessfulImport()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer = $this->createRootServer(123);
        $this->get("/api/v1/rootservers/$rootServer->id")
            ->assertStatus(200)
            ->assertExactJson([
                'id' => $rootServer->id,
                'sourceId' => $rootServer->source_id,
                'name' => $rootServer->name,
                'url' => $rootServer->url,
                'lastSuccessfulImport' => null,
            ]);
    }

    public function testNonNullLastSuccessfulImport()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer = $this->createRootServer(123);
        $rootServer->last_successful_import = $rootServer->updated_at;
        $rootServer->save();
        $this->get("/api/v1/rootservers/$rootServer->id")
            ->assertStatus(200)
            ->assertExactJson([
                'id' => $rootServer->id,
                'sourceId' => $rootServer->source_id,
                'name' => $rootServer->name,
                'url' => $rootServer->url,
                'lastSuccessfulImport' => $rootServer->last_successful_import->format('Y-m-d H:i:s'),
            ]);
    }
}
