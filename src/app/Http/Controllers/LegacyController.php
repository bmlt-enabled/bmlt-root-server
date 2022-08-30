<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;


class LegacyController extends Controller
{
    public function all(Request $request): Response
    {
        $pathInfo = $this->getPathInfo($request);

        if (file_exists($pathInfo->path)) {
            $response = response()->view('legacy', ['includePath' => $pathInfo->path]);
            if ($pathInfo->contentType) {
                $response = $response->header('Content-Type', $pathInfo->contentType);
            }
            return $response;
        }

        abort(404);
    }

    private function getPathInfo(Request $request): LegacyPathInfo {
        // TODO unit test this
        $path = trim($request->path(), "/");
        while (str_contains($path, '..')) {
            // I don't think this is possible... but as a just in case security measure...
            $path = str_replace("..", '', $path);
        }
        $path = rtrim(base_path('legacy'), '/') . '/' . $path;
        $filename = explode("/", $path);
        $filename = $filename[array_key_last($filename)];
        $contentType = null;
        if (str_contains($filename, '.')) {
            $extension = explode('.', $filename);
            $extension = $extension[array_key_last($extension)];
            if ($extension == 'js') {
                $contentType = 'text/javascript';
            } elseif ($extension == 'css') {
                $contentType = 'text/css';
            }
            return new LegacyPathInfo($path, $contentType);
        }

        return new LegacyPathInfo($path . '/index.php', $contentType);
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

