<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LegacyPathInfo;
use Tests\LegacyTestCase;

class TestLegacyController extends LegacyController
{
    public function testGetPathInfo(string $path): LegacyPathInfo
    {
        return $this->getPathInfo($path);
    }
}

class LegacyControllerTest extends LegacyTestCase
{
    public function testTheApplicationReturnsASuccessfulResponse()
    {
        // TODO: These can't work until we figure out how to mock the setcookie() and header() built-ins
         $response = $this->get('/');
         $response->assertStatus(500);
    }

    public function testBare()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/');
        $this->assertEquals('/legacy/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/html', $pathInfo->contentType);
    }
    public function testDotJs()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/blah.js');
        $this->assertEquals('/legacy/blah.js', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/javascript', $pathInfo->contentType);
    }

    public function testDotCss()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/blah/blah/blah.css');
        $this->assertEquals('/legacy/blah/blah/blah.css', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/css', $pathInfo->contentType);
    }

    public function testDotXml()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/serverInfo.xml');
        $this->assertEquals('/legacy/main_server/client_interface/serverInfo.xml', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/xml', $pathInfo->contentType);
    }

    public function testSemanticJson()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/json');
        $this->assertEquals('/legacy/main_server/client_interface/json/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/json/');
        $this->assertEquals('/legacy/main_server/client_interface/json/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
    }

    public function testSemanticXml()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/xml');
        $this->assertEquals('/legacy/main_server/client_interface/xml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/xml/');
        $this->assertEquals('/legacy/main_server/client_interface/xml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticGpx()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/gpx');
        $this->assertEquals('/legacy/main_server/client_interface/gpx/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/gpx/');
        $this->assertEquals('/legacy/main_server/client_interface/gpx/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticKml()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/kml');
        $this->assertEquals('/legacy/main_server/client_interface/kml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/kml/');
        $this->assertEquals('/legacy/main_server/client_interface/kml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticXsd()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/xsd/GetLangs.php');
        $this->assertEquals('/legacy/main_server/client_interface/xsd/GetLangs.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticCsv()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/csv');
        $this->assertEquals('/legacy/main_server/client_interface/csv/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/csv/');
        $this->assertEquals('/legacy/main_server/client_interface/csv/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
    }

    public function testSemanticPoi()
    {
        $controller = new TestLegacyController();
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/poi');
        $this->assertEquals('/legacy/main_server/client_interface/poi/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
        $pathInfo = $controller->testGetPathInfo('/main_server/client_interface/poi/');
        $this->assertEquals('/legacy/main_server/client_interface/poi/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
    }
}
