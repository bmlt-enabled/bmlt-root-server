<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class SvelteKitController extends Controller
{
    public function all(Request $request): Response
    {
        return self::handle($request);
    }

    public static function handle(Request $request)
    {
        $basePath = $request->getBasePath();
        $startScript = collect(scandir(public_path('/_app/immutable')))
            ->filter(fn ($value) => str_starts_with($value, 'start-') && str_ends_with($value, '.js'))
            ->map(fn ($value) => $basePath . '/_app/immutable/' . $value)
            ->firstOrFail();
        $chunks = collect(scandir(public_path('/_app/immutable/chunks')))
            ->filter(fn ($value) => (str_starts_with($value, 'index-') || str_starts_with($value, 'singletons-')) && str_ends_with($value, '.js'))
            ->map(fn ($value) => $basePath . '/_app/immutable/chunks/' . $value)
            ->toArray();
        return response()->view('sveltekit', [
            'basePath' => $basePath,
            'startScript' => $startScript,
            'chunks' => $chunks,
        ]);
    }
}
