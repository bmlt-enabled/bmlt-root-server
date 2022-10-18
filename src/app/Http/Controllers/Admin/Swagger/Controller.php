<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Info(title="BMLT", description="BMLT Admin API Documentation", version="1.0.0",
 *    @OA\License(name="MIT", url="https://github.com/bmlt-enabled/bmlt-root-server/blob/main/LICENSE")
 * )
 * @OA\SecurityScheme(securityScheme="bmltToken", type="oauth2",
 *     @OA\Flow(flow="password", tokenUrl="api/v1/auth/token", refreshUrl="api/v1/auth/refresh", scopes={})
 * )
 */
class Controller
{
}
