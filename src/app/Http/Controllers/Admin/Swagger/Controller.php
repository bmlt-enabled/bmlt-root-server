<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Info(title="BMLT - OpenAPI 3.0", description="BMLT Admin API Documentation", version="1.0.0",
 *    @OA\License(name="MIT", url="https://github.com/bmlt-enabled/bmlt-root-server/blob/main/LICENSE")
 * )
 * @OA\Server(url="https://latest.aws.bmlt.app/main_server/", description="BMLT Admin API Latest Server")
 * @OA\Server(url="https://unstable.aws.bmlt.app/main_server/", description="BMLT Admin API Unstable Server")
 * @OA\Server(url="https://gyro.sezf.org/main_server/", description="BMLT Admin API Gyro Server")
 * @OA\Server(url="http://localhost:8000/main_server/", description="BMLT Admin API Local Server")
 * @OA\Server(url="https://{domain}", description="BMLT Admin API Custom Server",
 *     @OA\ServerVariable(serverVariable="domain", default="")
 * )
 * @OA\SecurityScheme(securityScheme="oauth2", type="oauth2",
 *     @OA\Flow(flow="password", tokenUrl="api/v1/auth/token", refreshUrl="api/v1/auth/refresh", scopes={})
 * )
 */
class Controller
{
}
