<?php
namespace App\Http\Controllers\Legacy;

use Illuminate\Http\Request;

class LegacyPathInfo
{
    public string $path;
    public ?string $contentType;

    public function __construct(string $path, ?string $contentType)
    {
        $this->path = $path;
        $this->contentType = $contentType;
    }

    public static function parse(Request $request): LegacyPathInfo
    {
        $path = $request->path();
        while (str_contains($path, '..')) {
            // I don't think this is possible... but as a just in case security measure...
            $path = str_replace("..", '', $path);
        }

        $path = trim($path, "/");
        $path = base_path('legacy') . ($path ? '/' : '') . $path;
        $filename = explode("/", $path);
        $filename = $filename[array_key_last($filename)];
        if (!str_contains($filename, '.')) {
            $filename = 'index.php';
            $path .= '/' . $filename;
        }

        $contentType = 'text/html';
        if (!is_null($request->input('bmlt_ajax_callback'))) {
            $contentType = "application/json";
        } elseif (str_ends_with($path, '.php')) {
            if (str_contains($path, "/client_interface/jsonp/")) {
                $contentType = "application/javascript";
            } elseif (preg_match('/server_admin\/json\.php|client_interface\/json/', $path)) {
                $contentType = 'application/json';
            } elseif (preg_match('/server_admin\/xml\.php|client_interface\/(xml|gpx|kml|xsd)/', $path)) {
                $contentType = 'application/xml';
            } elseif (preg_match('/client_interface\/(csv|poi)/', $path)) {
                $contentType = 'text/csv';
            }
        } else {
            $extension = explode('.', $filename);
            $extension = $extension[array_key_last($extension)];
            if ($extension == 'js') {
                $contentType = 'text/javascript';
            } elseif ($extension == 'css') {
                $contentType = 'text/css';
            } elseif ($extension == 'xml') {
                $contentType = 'text/xml';
            }
        }

        return new LegacyPathInfo($path, $contentType);
    }
}
