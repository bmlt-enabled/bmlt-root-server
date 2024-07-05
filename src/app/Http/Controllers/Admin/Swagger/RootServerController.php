<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="RootServerBase",
 *     @OA\Property(property="sourceId", type="integer", example="0"),
 *     @OA\Property(property="name", type="string", example="string"),
 *     @OA\Property(property="url", type="string", example="https://example.com/main_server"),
 *     @OA\Property(property="statistics", type="object", required={"serviceBodies", "meetings"},
 *         @OA\Property(property="serviceBodies", type="object", required={"numZones", "numRegions", "numAreas", "numGroups"},
 *             @OA\Property(property="numZones", type="integer", example="0"),
 *             @OA\Property(property="numRegions", type="integer", example="0"),
 *             @OA\Property(property="numAreas", type="integer", example="0"),
 *             @OA\Property(property="numGroups", type="integer", example="0"),
 *         ),
 *         @OA\Property(property="meetings", type="object", required={"numTotal", "numInPerson", "numVirtual", "numHybrid", "numUnknown"},
 *             @OA\Property(property="numTotal", type="integer", example="0"),
 *             @OA\Property(property="numInPerson", type="integer", example="0"),
 *             @OA\Property(property="numVirtual", type="integer", example="0"),
 *             @OA\Property(property="numHybrid", type="integer", example="0"),
 *             @OA\Property(property="numUnknown", type="integer", example="0"),
 *         ),
 *     ),
 *     @OA\Property(property="serverInfo", type="string", example="string"),
 *     @OA\Property(property="lastSuccessfulImport", type="string", format="date-time", example="2022-11-25 04:16:26")
 * ),
 * @OA\Schema(schema="RootServer", required={"id", "sourceId", "name", "url", "lastSuccessfulImport"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/RootServerBase") },
 *     @OA\Property(property="id", type="integer", example="0"),
 * ),
 * @OA\Schema(schema="RootServerCollection", type="array",
 *     @OA\Items(ref="#/components/schemas/RootServer")
 * ),
 */
class RootServerController extends Controller
{

    /**
     * @OA\Get(path="/api/v1/rootservers", summary="Retrieves root servers", description="Retrieve root servers.", operationId="getRootServers", tags={"rootServer"},
     *     @OA\Response(response=200, description="Successful response.",
     *         @OA\JsonContent(ref="#/components/schemas/RootServerCollection")
     *     ),
     *     @OA\Response(response=404, description="Returns when aggregator mode is disabled.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(path="/api/v1/rootservers/{rootServerId}", summary="Retrieves a root server", description="Retrieve a single root server id.", operationId="getRootServer", tags={"rootServer"},
     *     @OA\Parameter(description="ID of root server", in="path", name="rootServerId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=200, description="Successful response.",
     *         @OA\JsonContent(ref="#/components/schemas/RootServer")
     *     ),
     *     @OA\Response(response=404, description="Returns when no root server exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     * )
     */
    public function show()
    {
    }
}
