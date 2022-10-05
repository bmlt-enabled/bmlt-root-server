<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(schema="ErrorIncorrectCredentials",
 *     @OA\Property(property="message", type="string", example="The provided credentials are incorrect.")
 * ),
 * @OA\Schema(schema="ErrorUnauthenticated",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(schema="ErrorUnauthorized",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * ),
 * @OA\Schema(schema="ValidationError",
 *     @OA\Property(property="message", type="string", example="The field is required. (and 1 more error)"),
 *     @OA\Property(property="errors", type="object",
 *         @OA\AdditionalProperties(type="array",
 *             @OA\Items(type="string", example="error details")
 *         )
 *     ),
 * )
 */

class ErrorDoc extends ResourceController
{

}
