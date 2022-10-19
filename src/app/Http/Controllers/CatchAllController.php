<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Legacy\LegacyPathInfo;

class CatchAllController extends Controller
{
    private static array $allowedLegacyPathEndings = [
        '/semantic/index.php',
        '/client_interface/html/index.php',
    ];

    public function all(Request $request): Response
    {
        return self::handle($request);
    }

    public static function handle(Request $request): Response
    {
        $pathInfo = LegacyPathInfo::parse($request);

        if (legacy_config('new_ui_enabled')) {
            if (self::isAllowedLegacyPath($pathInfo) && file_exists($pathInfo->path)) {
                return self::legacyResponse($pathInfo);
            }

            return response()->view('frontend');
        }

        if (file_exists($pathInfo->path)) {
            return self::legacyResponse($pathInfo);
        }

        abort(404);
    }

    private static function isAllowedLegacyPath(LegacyPathInfo $pathInfo): bool
    {
        foreach (self::$allowedLegacyPathEndings as $allowedPathEnding) {
            if (str_ends_with($pathInfo->path, $allowedPathEnding)) {
                return true;
            }
        }

        return false;
    }

    private static function legacyResponse(LegacyPathInfo $pathInfo): Response
    {
        return response()
            ->view('legacy', ['includePath' => $pathInfo->path])
            ->header('Content-Type', $pathInfo->contentType);
    }
}
