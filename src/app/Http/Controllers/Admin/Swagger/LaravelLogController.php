<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="LaravelLogBase"),
 */
class LaravelLogController extends Controller
{
    /**
     * @OA\Get(path="/api/v1/logs/laravel", summary="Retrieves laravel log", description="Retrieve the laravel log if it exists.", operationId="getLaravelLog", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\MediaType(mediaType="text/plain", @OA\Schema(type="string"))
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no laravel log file exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     * )
     */
    public function index()
    {
    }
}
