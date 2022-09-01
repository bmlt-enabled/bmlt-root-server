<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegacyController extends Controller
{
    public function all(Request $request): Response
    {
        $pathInfo = $this->getPathInfo($request->path());

        if (file_exists($pathInfo->path)) {
            return response()
                ->view('legacy', ['includePath' => $pathInfo->path])
                ->header('Content-Type', $pathInfo->contentType);
        }

        abort(404);
    }

    protected function getPathInfo(string $path): LegacyPathInfo
    {
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
        if (str_ends_with($path, '.php')) {
            if (preg_match('/client_interface\/json/', $path)) {
                $contentType = 'application/json';
            } elseif (preg_match('/client_interface\/(xml|gpx|kml|xsd)/', $path)) {
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


class LegacyPathInfo
{
    public string $path;
    public ?string $contentType;

    public function __construct(string $path, ?string $contentType)
    {
        $this->path = $path;
        $this->contentType = $contentType;
    }
}
