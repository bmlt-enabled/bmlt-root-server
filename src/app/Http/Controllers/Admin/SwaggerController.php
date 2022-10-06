<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use Illuminate\Http\Request;

class SwaggerController extends Controller
{
    public function openapi(Request $request)
    {
        $server = new \stdClass;
        $server->url = rtrim($request->getBaseUrl(), '/') . '/';
        $server->description = 'this server';

        $json = json_decode(\File::get(storage_path('api-docs/api-docs.json')));
        $json->servers = [$server];

        return new JsonResponse($json);
    }
}
