<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LaravelLogController extends Controller
{
    public function laravelLog(Request $request): JsonResponse|BinaryFileResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $filePath = storage_path('logs/laravel.log');

        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            return response()->json(['message' => 'Log file not found.'], 404);
        }
    }
}
