<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(
 *     schema="MeetingResponse",
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
 * ),
 * @OA\Schema(
 *     schema="MeetingsResponse",
 *             type="array",
 *                @OA\Items(ref="#/components/schemas/MeetingResponse"),
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
     *      @OA\JsonContent(ref="#/components/schemas/MeetingErrorUnauthenticated")
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

    public function store()
    {
    }

    public function update()
    {
    }

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
     *    @OA\JsonContent(ref="#/components/schemas/MeetingErrorUnauthenticated")
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Returns when unauthorized.",
     *    @OA\JsonContent(ref="#/components/schemas/MeetingErrorUnauthorized")
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
