<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(
 *     schema="MeetingBase",
 *     @OA\Property(property="serviceBodyId", type="integer", example="0"),
 *     @OA\Property(property="formatIds",type="array",
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
 *     @OA\Property(property="train_lines", type="string", example="string"),
 *     @OA\Property(property="comments", type="string", example="string"),
 *     @OA\Property(property="customFields", type="object", example={"key1": "value1", "key2": "value2"},
 *         @OA\AdditionalProperties(type="string")
 *     ),
 * ),
 * @OA\Schema(schema="Meeting", required={"id", "serviceBodyId", "formatIds", "venueType", "temporarilyVirtual", "day", "startTime", "duration", "timeZone", "latitude", "longitude", "published", "email", "worldId", "name"},
 *     @OA\Property(property="id", type="integer", example="0"),
 *     allOf={ @OA\Schema(ref="#/components/schemas/MeetingBase") }
 * ),
 * @OA\Schema(schema="MeetingCreate", required={"serviceBodyId", "formatIds", "venueType", "day", "startTime", "duration", "latitude", "longitude", "published", "name"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/MeetingBase") }
 * ),
 * @OA\Schema(schema="MeetingUpdate", required={"serviceBodyId", "formatIds", "venueType", "day", "startTime", "duration", "latitude", "longitude", "published", "name"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/MeetingBase") }
 * ),
 * @OA\Schema(schema="MeetingPartialUpdate", required={"serviceBodyId", "formatIds", "venueType", "day", "startTime", "duration", "latitude", "longitude", "published", "name"},
 *     allOf={ @OA\Schema(ref="#/components/schemas/MeetingBase") }
 * ),
 * @OA\Schema(schema="MeetingCollection", type="array",
 *     @OA\Items(ref="#/components/schemas/Meeting")
 * )
 */

class MeetingController extends Controller
{

    /**
     * @OA\Get(path="/api/v1/meetings", summary="Retrieves meetings", description="Retrieve meetings for authenticated user.", operationId="getMeetings", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="comma delimited meeting ids", in="query", name="meetingIds", required=false, example="1,2",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(description="comma delimited day ids between 0-6", in="query", name="days", required=false, example="0,1",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(description="comma delimited service body ids", in="query", name="serviceBodyIds", required=false, example="3,4",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(description="string", in="query", name="searchString", required=false, example="Just for Today",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="List of meetings.",
     *         @OA\JsonContent(ref="#/components/schemas/MeetingCollection")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    public function index()
    {
    }

    /**
     * @OA\Get(path="/api/v1/meetings/{meetingId}", summary="Retrieves a meeting", description="Retrieve a meeting.", operationId="getMeeting", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of meeting", in="path", name="meetingId", required=true, example="1",
     *         @OA\Schema(type="integer",format="int64")
     *     ),
     *     @OA\Response(response=200, description="Returns when user is authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/Meeting")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no meeting exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     * )
     */
    public function show()
    {
    }

    /**
     * @OA\Post(path="/api/v1/meetings", summary="Creates a meeting", description="Creates a meeting.", operationId="createMeeting", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\RequestBody(required=true, description="Pass in meeting object",
     *         @OA\JsonContent(ref="#/components/schemas/MeetingCreate"),
     *     ),
     *     @OA\Response(response=201, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/Meeting")
     *     ),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no meeting body exists.",
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
     * @OA\Put(path="/api/v1/meetings/{meetingId}", summary="Updates a meeting", description="Updates a meeting.", operationId="updateMeeting", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of meeting",in="path", name="meetingId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in meeting object",
     *         @OA\JsonContent(ref="#/components/schemas/MeetingUpdate"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no meeting exists.",
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
     * @OA\Patch(path="/api/v1/meetings/{meetingId}", summary="Patches a meeting", description="Patches a meeting by id", operationId="patchMeeting", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of meeting", in="path", name="meetingId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(required=true, description="Pass in fields you want to update.",
     *         @OA\JsonContent(ref="#/components/schemas/MeetingPartialUpdate"),
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no meeting exists.",
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
     * @OA\Delete(path="/api/v1/meetings/{meetingId}", summary="Deletes a meeting", description="Deletes a meeting by id.", operationId="deleteMeeting", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Parameter(description="ID of meeting", in="path", name="meetingId", required=true, example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(response=204, description="Success."),
     *     @OA\Response(response=401, description="Returns when user is not authenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is unauthorized to perform action.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(response=404, description="Returns when no meeting exists.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     ),
     * )
     */
    public function destroy()
    {
    }
}
