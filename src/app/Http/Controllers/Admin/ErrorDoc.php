<?php

namespace App\Http\Controllers\Admin;

/**
 * @OA\Schema(
 *     schema="ErrorIncorrectCredentials",
 *     @OA\Property(property="message", type="string", example="The provided credentials are incorrect.")
 * ),
 * @OA\Schema(
 *     schema="ErrorUnauthenticated",
 *     @OA\Property(property="message", type="string", example="Unauthenticated.")
 * ),
 * @OA\Schema(
 *     schema="ErrorUnauthorized",
 *     @OA\Property(property="message", type="string", example="This action is unauthorized.")
 * )
 */

class ErrorDoc extends ResourceController
{

}
