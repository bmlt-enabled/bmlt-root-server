<?php

namespace App\Http\Controllers\Admin;

use App\Http\Responses\JsonResponse;
use Illuminate\Http\Request;

class ErrorTestController extends ResourceController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'arbitrary_string' => 'string',
            'arbitrary_int' => 'int',
            'force_server_error' => 'boolean'
        ]);

        if ($validated['force_server_error'] ?? false) {
            1 / 0;
        }

        return new JsonResponse($validated, status: 201);
    }
}
