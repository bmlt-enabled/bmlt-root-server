<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="BMLT - OpenAPI 3.0",
 *    description="BMLT Admin API Documentation",
 *    version="1.0.0",
 *    @OA\License(
 *        name="MIT",
 *        url="https://github.com/bmlt-enabled/bmlt-root-server/blob/main/LICENSE"
 *    )
 * )
 * @OA\Server(
 *      url="https://latest.aws.bmlt.app/main_server",
 *      description="BMLT Admin API Server"
 * )
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer"
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
