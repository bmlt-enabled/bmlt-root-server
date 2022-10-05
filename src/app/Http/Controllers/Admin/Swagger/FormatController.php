<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(
 *     schema="FormatResponse",
 *     @OA\Property(property="worldId", type="string", example="string"),
 *     @OA\Property(property="type", type="string", example="string"),
 *     @OA\Property(
 *        property="translations",
 *        type="array",
 *        @OA\Items(ref="#/components/schemas/FormatLangModel")
 *     ),
 *    ),
 * ),
 * @OA\Schema(
 *     schema="FormatLangModel",
 *     required={"key", "name", "description", "language"},
 *     @OA\Property(
 *         property="key",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="language",
 *         type="string"
 *     )
 * ),
 * @OA\Schema(
 *     schema="NoFormatExists",
 *      description="Returns when no format exists.",
 *      @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Format]"),
 * ),
 * @OA\Schema(
 *     schema="GetFormatsResponse",
 *             type="array",
 *                example={{
 *                  "id": 0,
 *                  "worldId": "string",
 *                  "type": "string",
 *                  "translations": {
 *                    "key": "string",
 *                    "name": "string",
 *                    "description": "string",
 *                    "language": "string"
 *                  }
 *                }},
 *                @OA\Items(
 *     @OA\Property(property="id", type="integer", example="0"),
 *     ref="#/components/schemas/FormatResponse"),
 * )
 */
class FormatController extends Controller
{

    /**
     * @OA\Get(
     * path="/api/v1/formats",
     * summary="Retrieve formats",
     * description="Retrieve formats for server.",
     * operationId="getFormats",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/GetFormatsResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *   )
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Get(
     * path="/api/v1/formats/{formatId}",
     * summary="Retrieve a single format",
     * description="Retrieve single format.",
     * operationId="getSingleFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Returns with successful request.",
     *     @OA\JsonContent(
     *     @OA\Property(property="id", type="integer", example="0"),
     *     @OA\Property(property="worldId", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(
     *        property="translations",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FormatLangModel")
     *     ),
     *   )
     * )
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Returns when no format exists.",
     *      @OA\JsonContent(ref="#/components/schemas/NoFormatExists")
     *   )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Post(
     * path="/api/v1/formats",
     * summary="Create Format",
     * description="Cretaes a format.",
     * operationId="createFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in format object",
     *    @OA\JsonContent(
     *       required={"translations"},
     *     @OA\Property(property="worldId", type="string", example="string"),
     *     @OA\Property(property="type", type="string", example="string"),
     *     @OA\Property(
     *        property="translations",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/FormatLangModel")
     *     ),
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Returns when POST is successful.",
     *    @OA\JsonContent(
     *     @OA\Property(property="id", type="integer", example="0"),
     *     ref="#/components/schemas/FormatResponse")
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The translations.0.key field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="translations.0.key",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.key field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="translations.0.name",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.name field is required.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorIncorrectCredentials")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     * )
     * )
     */
    public function store()
    {
    }

    /**
     * @OA\Put(
     * path="/api/v1/formats/{formatId}",
     * summary="Update single format",
     * description="Updates a single format.",
     * operationId="updateFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in format object",
     *    @OA\JsonContent(ref="#/components/schemas/FormatResponse"),
     * ),
     * @OA\Response(
     *    response=204,
     *    description="Returns when PUT is successful."
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The translations.0.key field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="translations.0.key",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.key field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="translations.0.name",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The translations.0.name field is required.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorIncorrectCredentials")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     * )
     * )
     */
    public function update()
    {
    }

    /**
     * @OA\Patch(
     * path="/api/v1/formats/{formatId}",
     * summary="Patches a single format",
     * description="Patches a single format by id.",
     * operationId="patchFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in format attributes.",
     *    @OA\JsonContent(ref="#/components/schemas/FormatResponse"),
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(
     *       @OA\Property(ref="#/components/schemas/ErrorUnauthorized"),
     *    )
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no service body exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoFormatExists")
     *  )
     * )
     */
    public function partialUpdate()
    {
    }

    /**
     * @OA\Delete(
     * path="/api/v1/formats/{formatId}",
     * summary="Deletes a single format",
     * description="Deletes a single format by id.",
     * operationId="deleteFormat",
     * tags={"formats"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of format",
     *    in="path",
     *    name="formatId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\Response(
     *     response=204,
     *     description="Returns with successful request."
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when not authenticated",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no format for id exists.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Format]"),
     *     )
     *  )
     * )
     */
    public function destroy()
    {
    }
}
