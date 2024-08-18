<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    public function laravel(Request $request): JsonResponse|StreamedResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $filePath = storage_path('logs/laravel.log');

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Log file not found.'], 404);
        }

        return response()->streamDownload(function () use ($filePath) {
            $file = fopen($filePath, 'r');
            $chunkSize = 65536; // 64KB chunks
            while (!feof($file)) {
                $chunk = fread($file, $chunkSize);
                echo gzencode($chunk, 9);
            }
            fclose($file);
        }, 'laravel.log.gz', [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Encoding' => 'gzip',
        ]);
    }
}
