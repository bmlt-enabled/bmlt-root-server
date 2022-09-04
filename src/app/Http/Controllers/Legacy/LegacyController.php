<?php

namespace App\Http\Controllers\Legacy;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class LegacyController extends Controller
{
    public function all(Request $request): Response
    {
        return self::handle($request);
    }

    public static function handle(Request $request): Response
    {
        $pathInfo = LegacyPathInfo::parse($request);

        if (file_exists($pathInfo->path)) {
            return response()
                ->view('legacy', ['includePath' => $pathInfo->path])
                ->header('Content-Type', $pathInfo->contentType);
        }

        abort(404);
    }
}
