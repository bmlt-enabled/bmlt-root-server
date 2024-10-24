<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="FormatBase",
 *     @OA\Property(property="worldId", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(property="translations", type="array",
 *         @OA\Items(ref="#/components/schemas/FormatTranslation")
 *     ),
 * ),
 * @OA\Schema(schema="FormatTranslation", required={"key", "name", "description", "language"},
 *     @OA\Property(property="key", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="language", type="string")
 * ),
 * @OA\Schema(schema="Format", required={"id", "worldId", "type", "translations"},
 *     @OA\Property(property="id", type="integer", example="0"),
 *     allOf={ @OA\Schema(ref="#/components/schemas/FormatBase") }
 * ),
 * @OA\Schema(schema="FormatCreate", required={"translations"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/FormatBase") }
 * ),
 * @OA\Schema(schema="FormatUpdate", required={"translations"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/FormatBase") }
 * ),
 * @OA\Schema(schema="FormatPartialUpdate",
 *     allOf={ @OA\Schema(ref="#/components/schemas/FormatBase") }
 * ),
 * @OA\Schema(schema="FormatCollection", type="array",
 *     @OA\Items(ref="#/components/schemas/Format")
 * ),
 */
class FormatController extends Controller
{

    /**
     * @OA\Get(path="/api/v1/formats", summary="Retrieves formats", description="Retrieve formats", operationId="getFormats", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/FormatCollection")
     *     ),
     *     @OA\Response(response=401, description="Returns when not authenticated",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(path="/api/v1/formats/{formatId}", summary="Retrieves a format", description="Retrieve a format", operationId="getFormat", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of format", in="path", name="formatId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/Format")
     *     ),
     *     @OA\Response(response=401, description="Returns when not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no format exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Post(path="/api/v1/formats", summary="Creates a format", description="Creates a format.", operationId="createFormat", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\RequestBody(required=true, description="Pass in format object",
     *         @OA\JsonContent(ref="#/components/schemas/FormatCreate"),
     *     ),
     *     @OA\Response(response=201, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/Format")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no format exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function store()
    {
    }

    /**
     * @OA\Put( path="/api/v1/formats/{formatId}", summary="Updates a format", description="Updates a format.", operationId="updateFormat", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of format", in="path", name="formatId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in format object",
     *         @OA\JsonContent(ref="#/components/schemas/FormatUpdate"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no format exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function update()
    {
    }

    /**
     * @OA\Patch(path="/api/v1/formats/{formatId}", summary="Patches a format", description="Patches a single format by id.", operationId="patchFormat", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of format", in="path", name="formatId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in fields you want to update.",
     *         @OA\JsonContent(ref="#/components/schemas/FormatPartialUpdate"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401,description="Returns when not authenticated",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no format exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function partialUpdate()
    {
    }

    /**
     * @OA\Delete(path="/api/v1/formats/{formatId}", summary="Deletes a format", description="Deletes a format by id.", operationId="deleteFormat", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of format", in="path", name="formatId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401,description="Returns when not authenticated",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no format exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     *     @OA\Response(response=409, description="Returns when format has meetings assigned.",
     *         @OA\JsonContent(ref="#/components/schemas/ConflictError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function destroy()
    {
    }
}
