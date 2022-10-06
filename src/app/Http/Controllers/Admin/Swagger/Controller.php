<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Info(title="BMLT - OpenAPI 3.0", description="BMLT Admin API Documentation", version="1.0.0",
 *    @OA\License(name="MIT", url="https://github.com/bmlt-enabled/bmlt-root-server/blob/main/LICENSE")
 * )
 * @OA\SecurityScheme(securityScheme="bmltToken", type="oauth2",
 *     @OA\Flow(flow="password", tokenUrl="auth/token", refreshUrl="auth/refresh", scopes={})
 * )
 */
class Controller
{
}
