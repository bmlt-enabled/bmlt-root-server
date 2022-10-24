<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="AuthenticationError", required={"message"},
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(schema="AuthorizationError", required={"message"},
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * ),
 * @OA\Schema(schema="NotFoundError", required={"message"},
 *     @OA\Property(property="message", type="string", example="The requested resource was not found.")
 * ),
 * @OA\Schema(schema="ValidationError", required={"message", "errors"},
 *     @OA\Property(property="message", type="string", example="The field is required. (and 1 more error)"),
 *     @OA\Property(property="errors", type="object",
 *         @OA\AdditionalProperties(type="array",
 *             @OA\Items(type="string", example="error details")
 *         )
 *     ),
 * ),
 * @OA\Schema(schema="ServerError", required={"message"},
 *     @OA\Property(property="message", type="string", example="Server Error")
 * ),
 */
class Errors extends Controller
{
}
