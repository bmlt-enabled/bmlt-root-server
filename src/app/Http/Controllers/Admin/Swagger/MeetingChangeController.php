<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(
 *     schema="MeetingChangeResource",
 *     type="object",
 *     @OA\Property(
 *         property="date_string",
 *         type="string",
 *         example="3:35 PM, 10/14/2024",
 *         description="Human-readable date and time."
 *     ),
 *     @OA\Property(
 *         property="user_name",
 *         type="string",
 *         example="Greater New York Regional Administrator",
 *         description="Name of the user who made the change."
 *     ),
 *     @OA\Property(
 *         property="service_body_name",
 *         type="string",
 *         example="Bronx Area Service",
 *         description="Name of the service body related to the meeting."
 *     ),
 *     @OA\Property(
 *         property="details",
 *         type="string",
 *         example="email_contact was deleted. time_zone was added as America/New_York. Meeting Name was changed from Just for Today #1 to Just for Today 1.",
 *         description="Details about the changes."
 *     )
 * )
 *
 */

class MeetingChangeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/meetings/{meetingId}/changes",
     *     summary="Retrieve changes for a meeting",
     *     description="Retrieve all changes made to a specific meeting.",
     *     operationId="getMeetingChanges",
     *     tags={"rootServer"},
     *     security={{"bmltToken":{}}},
     *     @OA\Parameter(
     *         description="ID of the meeting",
     *         in="path",
     *         name="meetingId",
     *         required=true,
     *         example="1",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of changes for the meeting.",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/MeetingChangeResource"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorizationError")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Meeting not found.",
     *         @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *     )
     * )
     */
    public function index()
    {
    }
}
