<?php

namespace Tests\Unit;

use App\Models\RootServer;
use PHPUnit\Framework\TestCase;

class RootServerTest extends TestCase
{
    private function rootServerWithInfo(?array $info): RootServer
    {
        return new RootServer(['server_info' => is_null($info) ? null : json_encode($info)]);
    }

    public function testMapCenterFromServerInfo()
    {
        $server = $this->rootServerWithInfo([
            'centerLatitude' => '36.065752051707',
            'centerLongitude' => '-79.793701171875',
        ]);
        $this->assertEquals(
            ['latitude' => 36.065752051707, 'longitude' => -79.793701171875],
            $server->map_center
        );
    }

    public function testMapCenterNullWhenServerInfoEmpty()
    {
        $this->assertNull($this->rootServerWithInfo(null)->map_center);
        $server = new RootServer(['server_info' => '']);
        $this->assertNull($server->map_center);
    }

    public function testMapCenterNullWhenServerInfoUnparseable()
    {
        $server = new RootServer(['server_info' => 'not json']);
        $this->assertNull($server->map_center);
    }

    public function testMapCenterNullWhenCenterMissing()
    {
        $server = $this->rootServerWithInfo(['version' => '4.2.3']);
        $this->assertNull($server->map_center);
    }

    public function testMapCenterNullWhenCenterNonNumeric()
    {
        $server = $this->rootServerWithInfo([
            'centerLatitude' => 'NULL',
            'centerLongitude' => 'NULL',
        ]);
        $this->assertNull($server->map_center);
    }
}
