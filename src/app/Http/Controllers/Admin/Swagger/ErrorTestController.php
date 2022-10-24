<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="ErrorTest",
 *     @OA\Property(property="arbitrary_string", type="string", example="string"),
 *     @OA\Property(property="arbitrary_int", type="integer", example="123"),
 *     @OA\Property(property="force_server_error", type="boolean", example="true"),
 * ),
 */
class ErrorTestController extends Controller
{
    /**
     * @OA\Post(path="/api/v1/errortest", summary="Tests some errors", description="Tests some errors.", operationId="createErrorTest", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\RequestBody(required=true, description="Pass in error test object.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorTest"),
     *     ),
     *     @OA\Response(response=201, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorTest")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(response=500, description="Server error.",
     *         @OA\JsonContent(ref="#/components/schemas/ServerError")
     *     ),
     * )
     */
    public function store()
    {
    }
}
