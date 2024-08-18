<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    public function laravel(Request $request): JsonResponse|StreamedResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $logs = Storage::disk('logs');

        if ($logs->missing('laravel.log')) {
            return response()->json(['message' => 'Log file not found.'], 404);
        }

        return response()->streamDownload(
            function () use ($logs) {
                $stream = $logs->readStream('laravel.log');
                $chunkSize = 65536; // 64KB chunks
                while (!feof($stream)) {
                    $chunk = fread($stream, $chunkSize);
                    echo gzencode($chunk, 9);
                }
                fclose($stream);
            },
            'laravel.log.gz',
            [
                'Content-Type' => 'text/plain; charset=UTF-8'
            ]
        );
    }
}
