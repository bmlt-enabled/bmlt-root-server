<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(
 *     schema="MeetingTemplate",
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
 *     @OA\Property(property="duration", type="string", example="01:00"),
 *     @OA\Property(property="timeZone", type="string", example="America/New_York"),
 *     @OA\Property(property="latitude", type="float", example="35.698741"),
 *     @OA\Property(property="longitude", type="float", example="-81.26273"),
 *     @OA\Property(property="published", type="bool", example="true"),
 *     @OA\Property(property="email", type="string", example="string"),
 *     @OA\Property(property="worldId", type="string", example="string"),
 *     @OA\Property(property="name", type="string", example="string"),
 *     @OA\Property(property="location_text", type="string", example="string"),
 *     @OA\Property(property="location_info", type="string", example="string"),
 *     @OA\Property(property="location_street", type="string", example="string"),
 *     @OA\Property(property="location_neighborhood", type="string", example="string"),
 *     @OA\Property(property="location_city_subsection", type="string", example="string"),
 *     @OA\Property(property="location_municipality", type="string", example="string"),
 *     @OA\Property(property="location_sub_province", type="string", example="string"),
 *     @OA\Property(property="location_province", type="string", example="string"),
 *     @OA\Property(property="location_postal_code_1", type="string", example="string"),
 *     @OA\Property(property="location_nation", type="string", example="string"),
 *     @OA\Property(property="phone_meeting_number", type="string", example="string"),
 *     @OA\Property(property="virtual_meeting_link", type="string", example="string"),
 *     @OA\Property(property="virtual_meeting_additional_info", type="string", example="string"),
 *     @OA\Property(property="contact_name_1", type="string", example="string"),
 *     @OA\Property(property="contact_name_2", type="string", example="string"),
 *     @OA\Property(property="contact_phone_1", type="string", example="string"),
 *     @OA\Property(property="contact_phone_2", type="string", example="string"),
 *     @OA\Property(property="contact_email_1", type="string", example="string"),
 *     @OA\Property(property="contact_email_2", type="string", example="string"),
 *     @OA\Property(property="bus_lines", type="string", example="string"),
 *     @OA\Property(property="train_line", type="string", example="string"),
 *     @OA\Property(property="comments", type="string", example="string"),
 * ),
 * @OA\Schema(
 *     schema="NoMeetingExists",
 *      description="Returns when no Meeting exists.",
 *      @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Meeting]"),
 * ),
 * @OA\Schema(
 *     schema="MeetingsResponse",
 *             type="array",
 *                @OA\Items(ref="#/components/schemas/MeetingResponse"),
 * ),
 * @OA\Schema(
 *     schema="MeetingResponse",
 *     allOf={
 *         @OA\Schema(
 *             @OA\Property(property="id", type="integer", example="0")
 *         ),
 *         @OA\Schema(ref="#/components/schemas/MeetingTemplate")
 *     }
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
     * @OA\Parameter(
     *    description="int delimited",
     *    in="query",
     *    name="meetingIds",
     *    required=false,
     *    example="1,2",
     *    @OA\Schema(
     *       type="string"
     *    )
     * ),
     * @OA\Parameter(
     *    description="int delimited",
     *    in="query",
     *    name="days",
     *    required=false,
     *    example="0,1",
     *    @OA\Schema(
     *       type="string"
     *    )
     * ),
     * @OA\Parameter(
     *    description="int delimited",
     *    in="query",
     *    name="serviceBodyIds",
     *    required=false,
     *    example="3,4",
     *    @OA\Schema(
     *       type="string"
     *    )
     * ),
     * @OA\Parameter(
     *    description="string",
     *    in="query",
     *    name="searchString",
     *    required=false,
     *    example="Just for Today",
     *    @OA\Schema(
     *       type="string"
     *    )
     * ),
     * @OA\Response(
     *     response=200,
     *     description="Returns when user is authenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/MeetingsResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *   )
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(
     * path="/api/v1/meetings/{meetingId}",
     * summary="Retrieve a single meeting",
     * description="Retrieve single meeting.",
     * operationId="getSingleMeeting",
     * tags={"meetings"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of meeting",
     *    in="path",
     *    name="meetingId",
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
     *     @OA\JsonContent(ref="#/components/schemas/MeetingResponse")
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Returns when not authenticated.",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Returns when user is unauthorized to perform action.",
     *      @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="Returns when no format exists.",
     *      @OA\JsonContent(ref="#/components/schemas/NoMeetingExists")
     *   )
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Post(
     * path="/api/v1/meetings",
     * summary="Create Meeting",
     * description="Cretaes a meeting.",
     * operationId="createMeeting",
     * tags={"meetings"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in meeting object",
     *    @OA\JsonContent(ref="#/components/schemas/MeetingTemplate"),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Returns when POST is successful.",
     *    @OA\JsonContent(ref="#/components/schemas/MeetingResponse")
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The latitude must be a number."),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="latitude",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The latitude must be a number.",
     *              )
     *           )
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when user is unauthorized to perform action.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * )
     * )
     */
    public function store()
    {
    }

    /**
     * @OA\Put(
     * path="/api/v1/meetings/{meetingId}",
     * summary="Update single meeting",
     * description="Updates a single meeting.",
     * operationId="updateMeeting",
     * tags={"meetings"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of meeting",
     *    in="path",
     *    name="meetingId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in meeting object",
     *    @OA\JsonContent(ref="#/components/schemas/MeetingTemplate"),
     * ),
     * @OA\Response(
     *    response=204,
     *    description="Returns when PUT is successful."
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error.",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The latitude must be a number."),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="latitude",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The latitude must be a number.",
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
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Returns when no meeting exists.",
     *    @OA\JsonContent(ref="#/components/schemas/NoMeetingExists")
     * )
     * )
     */
    public function update()
    {
    }

    /**
     * @OA\Patch(
     * path="/api/v1/meetings/{meetingId}",
     * summary="Patches a single meeting",
     * description="Patches a single meeting by id",
     * operationId="patchMeeting",
     * tags={"meetings"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of meeting",
     *    in="path",
     *    name="meetingId",
     *    required=true,
     *    example="1",
     *    @OA\Schema(
     *       type="integer",
     *       format="int64"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass in meeting attributes",
     *    @OA\JsonContent(
     *     @OA\Property(property="name", type="string", example="string"),
     *    ),
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
     *    description="Returns when unauthorized",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     *  @OA\Response(
     *     response=404,
     *     description="Returns when no meeting exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoMeetingExists")
     *  )
     * )
     */
    public function partialUpdate()
    {
    }

    /**
     * @OA\Delete(
     * path="/api/v1/meetings/{meetingId}",
     * summary="Deletes a single meeting",
     * description="Deletes a single meeting by id.",
     * operationId="deleteMeeting",
     * tags={"meetings"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    description="ID of meeting",
     *    in="path",
     *    name="meetingId",
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
     *     description="Returns when no meeting for id exists.",
     *     @OA\JsonContent(ref="#/components/schemas/NoMeetingExists")
     *  )
     * )
     */
    public function destroy()
    {
    }
}
