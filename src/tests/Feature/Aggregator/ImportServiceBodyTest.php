<?php

namespace Tests\Feature\Aggregator;

use App\LegacyConfig;
use App\Models\ServiceBody;
use App\Repositories\External\ExternalServiceBody;
use App\Repositories\ServiceBodyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\TestCase;

class ImportServiceBodyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    private function external(): ExternalServiceBody
    {
        return new ExternalServiceBody([
            'id' => '171',
            'parent_id' => '0',
            'name' => 'Trans Umbrella Area',
            'description' => 'description',
            'type' => 'AS',
            'url' => 'http://transuana.org',
            'helpline' => 'helpline',
            'world_id' => 'AR6339',
        ]);
    }

    private function create(int $rootServerId, int $sourceId): ServiceBody
    {
        $repository = new ServiceBodyRepository();
        return $repository->create([
            'root_server_id' => $rootServerId,
            'source_id' => $sourceId,
            'name_string' => 'some name',
            'description_string' => 'some description',
            'sb_type' => 'some type',
            'uri_string' => 'https://otherurl.com',
            'kml_file_uri_string' => 'some helpline',
            'worldid_mixed' => 'some world id',
            'sb_meeting_email' => '',
        ]);
    }

    public function testCreate()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer1 = $this->createRootServer(1);

        $external = $this->external();

        $repository = new ServiceBodyRepository();
        $repository->import($rootServer1->id, collect([$external]));

        $all = $repository->search();
        $this->assertEquals(1, $all->count());

        $db = $all->first();
        $this->assertEquals($rootServer1->id, $db->root_server_id);
        $this->assertTrue($external->isEqual($db));
    }

    public function testUpdate()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer1 = $this->createRootServer(1);
        $rootServer2 = $this->createRootServer(2);

        $external = $this->external();

        $this->create($rootServer1->id, $external->id);
        $this->create($rootServer2->id, $external->id);

        $repository = new ServiceBodyRepository();
        $repository->import($rootServer1->id, collect([$external]));

        $all = $repository->search();
        $this->assertEquals(2, $all->count());

        $db = $all->firstWhere('root_server_id', $rootServer1->id);
        $this->assertNotNull($db);
        $this->assertEquals($rootServer1->id, $db->root_server_id);
        $this->assertTrue($external->isEqual($db));

        $db = $all->firstWhere('root_server_id', $rootServer2->id);
        $this->assertNotNull($db);
        $this->assertEquals($rootServer2->id, $db->root_server_id);
        $this->assertFalse($external->isEqual($db));
    }

    public function testDelete()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer1 = $this->createRootServer(1);
        $rootServer2 = $this->createRootServer(2);
        $rootServer3 = $this->createRootServer(3);

        $external = $this->external();

        $this->create($rootServer1->id, $external->id);
        $this->create($rootServer1->id, $external->id + 1);
        $this->create($rootServer2->id, $external->id);
        $this->create($rootServer3->id, $external->id);

        $repository = new ServiceBodyRepository();
        $repository->import($rootServer1->id, collect([$external]));

        $all = $repository->search();
        $this->assertEquals(3, $all->count());

        $this->assertEquals(1, $all->where('root_server_id', $rootServer1->id)->count());
        $db = $all->firstWhere('root_server_id', $rootServer1->id);
        $this->assertNotNull($db);
        $this->assertEquals($rootServer1->id, $db->root_server_id);
        $this->assertTrue($external->isEqual($db));

        $this->assertEquals(1, $all->where('root_server_id', $rootServer2->id)->count());
        $db = $all->firstWhere('root_server_id', $rootServer2->id);
        $this->assertNotNull($db);
        $this->assertEquals($rootServer2->id, $db->root_server_id);
        $this->assertFalse($external->isEqual($db));

        $this->assertEquals(1, $all->where('root_server_id', $rootServer3->id)->count());
        $db = $all->firstWhere('root_server_id', $rootServer3->id);
        $this->assertNotNull($db);
        $this->assertEquals($rootServer3->id, $db->root_server_id);
        $this->assertFalse($external->isEqual($db));
    }

    public function testSbOwnerAssignment()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        $rootServer1 = $this->createRootServer(1);
        $rootServer2 = $this->createRootServer(2);

        $externalTop = $this->external();
        $externalTop->id = 1;
        $externalTop->parentId = 0;

        $externalMiddle = $this->external();
        $externalMiddle->id = 2;
        $externalMiddle->parentId = 1;

        $externalBottom = $this->external();
        $externalBottom->id = 3;
        $externalBottom->parentId = 2;

        $this->create($rootServer2->id, $externalTop->id);
        $this->create($rootServer2->id, $externalMiddle->id);
        $this->create($rootServer2->id, $externalBottom->id);

        $repository = new ServiceBodyRepository();
        $repository->import($rootServer1->id, collect([$externalTop, $externalMiddle, $externalBottom]));

        $all = $repository->search(rootServersInclude: [$rootServer1->id]);
        $this->assertEquals(3, $all->count());
        $serviceBodyTop = $all->firstWhere('source_id', $externalTop->id);
        $serviceBodyMiddle = $all->firstWhere('source_id', $externalMiddle->id);
        $serviceBodyBottom = $all->firstWhere('source_id', $externalBottom->id);
        $this->assertEquals(0, $serviceBodyTop->sb_owner);
        $this->assertEquals($serviceBodyTop->id_bigint, $serviceBodyMiddle->sb_owner);
        $this->assertEquals($serviceBodyMiddle->id_bigint, $serviceBodyBottom->sb_owner);

        $all = $repository->search(rootServersInclude: [$rootServer2->id]);
        $this->assertEquals(3, $all->count());
        $serviceBodyTop = $all->firstWhere('source_id', $externalTop->id);
        $serviceBodyMiddle = $all->firstWhere('source_id', $externalMiddle->id);
        $serviceBodyBottom = $all->firstWhere('source_id', $externalBottom->id);
        $this->assertEquals(0, $serviceBodyTop->sb_owner);
        $this->assertEquals(0, $serviceBodyMiddle->sb_owner);
        $this->assertEquals(0, $serviceBodyBottom->sb_owner);
    }
}
