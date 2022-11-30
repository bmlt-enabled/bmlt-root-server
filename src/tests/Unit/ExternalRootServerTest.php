<?php

namespace Tests\Unit;

use App\Models\RootServer;
use App\Repositories\External\ExternalRootServer;
use App\Repositories\External\InvalidRootServerException;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

class ExternalRootServerTest extends TestCase
{
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

    public function testValid()
    {
        $values = $this->validValues();
        $rootServer = new ExternalRootServer($values);
        $this->assertEquals($values['id'], $rootServer->id);
        $this->assertEquals($values['name'], $rootServer->name);
        $this->assertEquals($values['rootURL'], $rootServer->url);
    }

    public function testMissingId()
    {
        $this->expectException(InvalidRootServerException::class);
        $values = $this->validValues();
        unset($values['id']);
        new ExternalRootServer($values);
    }

    public function testInvalidId()
    {
        $this->expectException(InvalidRootServerException::class);
        $values = $this->validValues();
        $values['id'] = 'string';
        new ExternalRootServer($values);
    }

    public function testMissingName()
    {
        $this->expectException(InvalidRootServerException::class);
        $values = $this->validValues();
        unset($values['name']);
        new ExternalRootServer($values);
    }

    public function testInvalidName()
    {
        $this->expectException(InvalidRootServerException::class);
        $values = $this->validValues();
        $values['name'] = 123;
        new ExternalRootServer($values);
    }

    public function testMissingUrl()
    {
        $this->expectException(InvalidRootServerException::class);
        $values = $this->validValues();
        unset($values['rootURL']);
        new ExternalRootServer($values);
    }

    public function testInvalidUrl()
    {
        $this->expectException(InvalidRootServerException::class);
        $values = $this->validValues();
        $values['rootURL'] = 'string';
        new ExternalRootServer($values);
    }

    // isEqual
    //
    //
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
