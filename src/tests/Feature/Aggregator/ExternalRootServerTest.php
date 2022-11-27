<?php

namespace Tests\Feature\Aggregator;

use App\Models\RootServer;
use App\Repositories\External\ExternalRootServer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class ExternalRootServerTest extends TestCase
{
    use RefreshDatabase;

    private function validValues(): array
    {
        return [
            'id' => 1,
            'name' => 'test',
            'rootURL' => 'https://blah.com/blah',
        ];
    }

    private function getModel(array $validValues): RootServer
    {
        return new RootServer(['source_id' => $validValues['id'], 'name' => $validValues['name'], 'url' => $validValues['rootURL']]);
    }

    public function testNoDifferences()
    {
        $values = $this->validValues();
        $external = new ExternalRootServer($values);
        $db = $this->getModel($values);
        $this->assertTrue($external->isEqual($db));
    }

    public function testId()
    {
        $values = $this->validValues();
        $external = new ExternalRootServer($values);
        $db = $this->getModel($values);
        $db->source_id = 999;
        $this->assertFalse($external->isEqual($db));
    }

    public function testName()
    {
        $values = $this->validValues();
        $external = new ExternalRootServer($values);
        $db = $this->getModel($values);
        $db->name = 'some name';
        $this->assertFalse($external->isEqual($db));
    }

    public function testUrl()
    {
        $values = $this->validValues();
        $external = new ExternalRootServer($values);
        $db = $this->getModel($values);
        $db->url = 'https://adifferenturl';
        $this->assertFalse($external->isEqual($db));
    }
}
