<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\Legacy\LegacyPathInfo;
use Tests\LegacyTestCase;

class LegacyPathInfoTest extends LegacyTestCase
{
    private function request(string $uri, bool $bmltAjaxCallback = false): Request
    {
        return Request::create($uri, method: 'POST', parameters: $bmltAjaxCallback ? ['bmlt_ajax_callback' => '1'] : []);
    }

    public function testBare()
    {
        $request = $this->request('/index.php');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/html', $pathInfo->contentType);
    }
    public function testDotJs()
    {
        $request = $this->request('/blah.js');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/blah.js', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/javascript', $pathInfo->contentType);
    }

    public function testDotCss()
    {
        $request = $this->request('/blah/blah/blah.css');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/blah/blah/blah.css', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/css', $pathInfo->contentType);
    }

    public function testDotXml()
    {
        $request = $this->request('/main_server/client_interface/serverInfo.xml');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/serverInfo.xml', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/xml', $pathInfo->contentType);
    }

    public function testSemanticJson()
    {
        $request = $this->request('/main_server/client_interface/json');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/json/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/json/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/json/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
    }

    public function testSemanticJsonp()
    {
        $request = $this->request('/main_server/client_interface/jsonp');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/jsonp/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/javascript', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/jsonp/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/jsonp/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/javascript', $pathInfo->contentType);
    }

    public function testSemanticXml()
    {
        $request = $this->request('/main_server/client_interface/xml');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/xml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/xml/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/xml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticGpx()
    {
        $request = $this->request('/main_server/client_interface/gpx');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/gpx/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/gpx/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/gpx/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticKml()
    {
        $request = $this->request('/main_server/client_interface/kml');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/kml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/kml/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/kml/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticXsd()
    {
        $request = $this->request('/main_server/client_interface/xsd/GetLangs.php');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/xsd/GetLangs.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }

    public function testSemanticCsv()
    {
        $request = $this->request('/main_server/client_interface/csv');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/csv/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/csv/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/csv/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
    }

    public function testSemanticPoi()
    {
        $request = $this->request('/main_server/client_interface/poi');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/poi/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
        $request = $this->request('/main_server/client_interface/poi/');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/client_interface/poi/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('text/csv', $pathInfo->contentType);
    }

    public function testAdminUIApi()
    {
        $request = $this->request('/main_server/', true);
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/index.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
    }

    public function testServerAdminApiJson()
    {
        $request = $this->request('/main_server/server_admin/json.php', true);
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/server_admin/json.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/json', $pathInfo->contentType);
    }

    public function testServerAdminApiXml()
    {
        $request = $this->request('/main_server/server_admin/xml.php');
        $pathInfo = LegacyPathInfo::parse($request);
        $this->assertEquals('/legacy/main_server/server_admin/xml.php', str_replace(base_path(), '', $pathInfo->path));
        $this->assertEquals('application/xml', $pathInfo->contentType);
    }
}
