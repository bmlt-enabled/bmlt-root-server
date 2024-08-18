<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LogController extends Controller
{
    public function laravel(Request $request): JsonResponse|Response
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $filePath = storage_path('logs/laravel.log');

        if (file_exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $gzippedContent = gzencode($fileContent, 9); // 9 is Highest Compression Level
            return response($gzippedContent, 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Content-Encoding', 'gzip')
                ->header('Content-Disposition', 'attachment; filename=laravel.log.gz')
                ->header('Content-Length', strlen($gzippedContent));
        } else {
            return response()->json(['message' => 'Log file not found.'], 404);
        }
    }
}
