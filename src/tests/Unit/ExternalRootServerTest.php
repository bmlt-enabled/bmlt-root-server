<?php

namespace Tests\Unit;

use App\Repositories\External\ExternalRootServer;
use App\Repositories\External\InvalidRootServerException;
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
}
