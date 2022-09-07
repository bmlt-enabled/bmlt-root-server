<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ServerInfoXmlTest extends TestCase
{
    private ?string $originalVersion;

    public function testXml()
    {
        $newVersion = "2.0.0";
        $expectedContent = <<<VERSION
<?xml version="1.0" encoding="utf-8"?>
<bmltInfo>
  <serverVersion>
    <readableString>$newVersion</readableString>
  </serverVersion>
</bmltInfo>
VERSION;
        Config::set('app.version', $newVersion);
        $content = $this->get('/client_interface/serverInfo.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->content();
        $this->assertXmlStringEqualsXmlString($expectedContent, $content);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->originalVersion = Config::get('app.version');
    }

    public function tearDown(): void
    {
        Config::set('app.version', $this->originalVersion);
        parent::tearDown();
    }
}
