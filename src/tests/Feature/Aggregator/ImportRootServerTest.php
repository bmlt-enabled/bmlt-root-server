<?php

namespace Tests\Feature\Aggregator;

use App\Models\RootServer;
use App\Repositories\External\ExternalRootServer;
use App\Repositories\RootServerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\TestCase;

class ImportRootServerTest extends TestCase
{
    use RefreshDatabase;

    private function externalRootServer(): ExternalRootServer
    {
        return new ExternalRootServer([
            'id' => 1,
            'name' => 'test',
            'rootURL' => 'https://blah.com/blah',
        ]);
    }

    public function testCreate()
    {
        $rootServerRepository = new RootServerRepository();
        $external = $this->externalRootServer();
        $rootServerRepository->import(collect([$external]));
        $rootServers = $rootServerRepository->search();
        $this->assertEquals(1, $rootServers->count());
        $this->assertTrue($external->isEqual($rootServers->first()));
    }

    public function testUpdate()
    {
        $rootServerRepository = new RootServerRepository();
        $rootServerRepository->create(['source_id' => 1, 'name' => 'test', 'url' => 'https://test.com']);
        $external = $this->externalRootServer();
        $rootServerRepository->import(collect([$external]));
        $rootServers = $rootServerRepository->search();
        $this->assertEquals(1, $rootServers->count());
        $this->assertTrue($external->isEqual($rootServers->first()));
    }

    public function testDelete()
    {
        $rootServerRepository = new RootServerRepository();
        $rootServerRepository->create(['source_id' => 2, 'name' => 'test', 'url' => 'https://test.com']);
        $external = $this->externalRootServer();
        $rootServerRepository->import(collect([$external]));
        $rootServers = $rootServerRepository->search();
        $this->assertEquals(1, $rootServers->count());
        $this->assertTrue($external->isEqual($rootServers->first()));
    }
}
