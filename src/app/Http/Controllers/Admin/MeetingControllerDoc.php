<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(
 *     schema="MeetingsResponse",
 *     @OA\Property(property="id", type="integer", example="0"),
 *     @OA\Property(property="serviceBodyId", type="integer", example="0"),
 *     @OA\Property(
 *        property="formatIds",
 *        type="array",
 *        @OA\Items(type="integer")
 *     ),
 *     @OA\Property(property="venueType", type="integer", example="1"),
 *     @OA\Property(property="temporarilyVirtual", type="bool", example="false"),
 *     @OA\Property(property="day", type="integer", example="0"),
 *     @OA\Property(property="startTime", type="string", example="string"),
 * ),
 * @OA\Schema(
 *     schema="MeetingErrorUnauthenticated",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(
 *     schema="MeetingErrorUnauthorized",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * ),
 * @OA\Schema(
 *     schema="NoMeetingExists",
 *      description="Returns when no Meeting exists.",
 *      @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Meeting]"),
 * )
 */

class MeetingControllerDoc extends ResourceController
{

    /**
     * @OA\Get(
     * path="/api/v1/meetings",
     * summary="Retrieve meetings",
     * description="Retrieve meetings for authenticated user.",
     * operationId="getMeetings",
     * tags={"meetings"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/MeetingsResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/MeetingErrorUnauthenticated")
     *   )
     * )
    */
    public function index()
    {
    }

    public function show()
    {
    }

    public function store()
    {
    }

    public function update()
    {
    }

    public function partialUpdate()
    {
    }

    public function destroy()
    {
    }
}
