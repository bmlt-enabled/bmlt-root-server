<?php

namespace Tests\Feature\Aggregator;

use App\LegacyConfig;
use App\Models\Format;
use App\Repositories\External\ExternalFormat;
use App\Repositories\FormatRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\TestCase;

class ImportFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        LegacyConfig::reset();
        parent::tearDown();
    }

    private function external(string $language): ExternalFormat
    {
        return new ExternalFormat([
            'id' => '342',
            'key_string' => 'HY',
            'name_string' => 'Hybrid',
            'description_string' => 'Meets virtually and in person',
            'lang' => $language,
            'format_type_enum' =>'FC2',
            'world_id' => 'HYBR',
        ]);
    }

    private function create(int $rootServerId, int $sourceId, string $language, int $sharedId): Format
    {
        $repository = new FormatRepository();
        return $repository->create([[
            'shared_id_bigint' => $sharedId,
            'root_server_id' => $rootServerId,
            'source_id' => $sourceId,
            'key_string' => 'HY',
            'name_string' => 'Hybrid',
            'description_string' => 'Meets virtually and in person',
            'lang_enum' => $language,
            'format_type_enum' => 'FC2',
            'worldid_mixed' => 'HYBR',
        ]]);
    }

    public function testCreate()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        Format::query()->delete();

        $rootServer1 = $this->createRootServer(1);

        $external1 = $this->external('en');
        $external2 = $this->external('es');
        $external3 = $this->external('de');

        $repository = new FormatRepository();
        $repository->import($rootServer1->id, collect([$external1, $external2, $external3]));

        $all = $repository->search(rootServersInclude: [$rootServer1->id], showAll: true);
        $this->assertEquals(3, $all->count());

        $db = $all->where('source_id', $external1->id)->where('lang_enum', $external1->language)->first();
        $this->assertTrue($external1->isEqual($db));

        $db = $all->where('source_id', $external2->id)->where('lang_enum', $external2->language)->first();
        $this->assertTrue($external2->isEqual($db));

        $db = $all->where('source_id', $external3->id)->where('lang_enum', $external3->language)->first();
        $this->assertTrue($external3->isEqual($db));
    }

    public function testUpdate()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        Format::query()->delete();

        $rootServer1 = $this->createRootServer(1);
        $rootServer2 = $this->createRootServer(2);

        $this->create($rootServer1->id, 1, 'en', 100);
        $this->create($rootServer1->id, 1, 'es', 100);
        $this->create($rootServer1->id, 1, 'de', 100);
        $this->create($rootServer1->id, 1, 'fr', 100);

        $this->create($rootServer2->id, 1, 'fa', 100);

        $external1 = $this->external('en');
        $external1->id = 100;
        $external2 = $this->external('es');
        $external2->id = 100;
        $external3 = $this->external('de');
        $external3->id = 100;

        $repository = new FormatRepository();
        $repository->import($rootServer1->id, collect([$external1, $external2, $external3]));

        $all = $repository->search(rootServersInclude: [$rootServer2->id], showAll: true);
        $this->assertEquals(1, $all->count());

        $all = $repository->search(rootServersInclude: [$rootServer1->id], showAll: true);
        $this->assertEquals(3, $all->count());

        $db = $all->where('source_id', $external1->id)->where('lang_enum', $external1->language)->first();
        $this->assertTrue($external1->isEqual($db));

        $db = $all->where('source_id', $external2->id)->where('lang_enum', $external2->language)->first();
        $this->assertTrue($external2->isEqual($db));

        $db = $all->where('source_id', $external3->id)->where('lang_enum', $external3->language)->first();
        $this->assertTrue($external3->isEqual($db));
    }

    public function testDelete()
    {
        LegacyConfig::set('aggregator_mode_enabled', true);
        Format::query()->delete();

        $rootServer1 = $this->createRootServer(1);

        $this->create($rootServer1->id, 1, 'en', 100);
        $this->create($rootServer1->id, 1, 'es', 100);
        $this->create($rootServer1->id, 1, 'de', 100);

        $external1 = $this->external('en');
        $external1->id = 101;
        $external2 = $this->external('es');
        $external2->id = 101;
        $external3 = $this->external('de');
        $external3->id = 101;

        $repository = new FormatRepository();
        $repository->import($rootServer1->id, collect([$external1, $external2, $external3]));

        $all = $repository->search(rootServersInclude: [$rootServer1->id], showAll: true);
        $this->assertEquals(3, $all->count());

        $db = $all->where('source_id', $external1->id)->where('lang_enum', $external1->language)->first();
        $this->assertTrue($external1->isEqual($db));

        $db = $all->where('source_id', $external2->id)->where('lang_enum', $external2->language)->first();
        $this->assertTrue($external2->isEqual($db));

        $db = $all->where('source_id', $external3->id)->where('lang_enum', $external3->language)->first();
        $this->assertTrue($external3->isEqual($db));
    }
}
