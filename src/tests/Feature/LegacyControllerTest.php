<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\LegacyPathInfo;
use Tests\LegacyTestCase;

class TestLegacyController extends LegacyController
{
    public function testGetPathInfo(Request $request): LegacyPathInfo
    {
        return $this->getPathInfo($request);
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

    private function request(string $uri, bool $bmltAjaxCallback = false): Request
    {
        return Request::create($uri, method: 'POST', parameters: $bmltAjaxCallback ? ['bmlt_ajax_callback' => '1'] : []);
    }

    public function testBare()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/index.php');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/html', $pathInfo->contentType);
    }
    public function testDotJs()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/blah.js');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/blah.js', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/javascript', $pathInfo->contentType);
    }

    public function testDotCss()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/blah/blah/blah.css');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/blah/blah/blah.css', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/css', $pathInfo->contentType);
    }

    public function testDotXml()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/serverInfo.xml');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/serverInfo.xml', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/xml', $pathInfo->contentType);
    }

    public function testSemanticJson()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/json');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/json/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/json/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/json/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
    }

    public function testSemanticJsonp()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/jsonp');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/jsonp/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/javascript', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/jsonp/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/jsonp/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/javascript', $pathInfo->contentType);
    }

    public function testSemanticXml()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/xml');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/xml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/xml/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/xml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticGpx()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/gpx');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/gpx/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/gpx/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/gpx/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticKml()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/kml');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/kml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/kml/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/kml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticXsd()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/xsd/GetLangs.php');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/xsd/GetLangs.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticCsv()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/csv');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/csv/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/csv/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/csv/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
    }

    public function testSemanticPoi()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/client_interface/poi');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/poi/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/poi/');
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/client_interface/poi/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
    }

    public function testAdminApi()
    {
        $controller = new TestLegacyController();
        $request = $this->request('/main_server/', true);
        $pathInfo = $controller->testGetPathInfo($request);
        $this->assertEquals('/legacy/main_server/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
    }
}
