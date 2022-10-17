<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Legacy\LegacyPathInfo;

class CatchAllController extends Controller
{
    public function all(Request $request): Response
    {
        return self::handle($request);
    }

    public static function handle(Request $request): Response
    {
        if (legacy_config('new_ui_enabled')) {
            return response()->view('frontend');
        }

        $pathInfo = LegacyPathInfo::parse($request);

        if (file_exists($pathInfo->path)) {
            return response()
                ->view('legacy', ['includePath' => $pathInfo->path])
                ->header('Content-Type', $pathInfo->contentType);
        }

        abort(404);
    }
}
