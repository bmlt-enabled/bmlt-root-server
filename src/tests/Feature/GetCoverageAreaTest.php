<?php

namespace Tests\Feature;

use Tests\TestCase;

class GetCoverageAreaTest extends TestCase
{
    public function testXml()
    {
        $this->get('/client_interface/xml/?switcher=GetCoverageArea')
            ->assertStatus(404);
    }

    public function testJsonp()
    {
        $content = $this->get('/client_interface/jsonp/?switcher=GetCoverageArea&callback=asdf')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8')
            ->content();
        $this->assertStringStartsWith('/**/asdf([', $content);
        $this->assertStringEndsWith(']);', $content);
    }

    public function testIsList()
    {
        $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function testKeys()
    {
        $expectedKeys = ['nw_corner_longitude', 'nw_corner_latitude', 'se_corner_longitude', 'se_corner_latitude'];
        $content = $this->get('/client_interface/json/?switcher=GetCoverageArea')
            ->assertStatus(200)
            ->json();
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $content[0]);
        }
    }
}
