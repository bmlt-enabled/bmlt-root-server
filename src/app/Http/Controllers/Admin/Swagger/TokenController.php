<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="Token",
 *       @OA\Property(property="access_token", type="string", example="2|tR6PIqa8tiBJWMu4zyb3qw4eECuERjLd7xeLKgBu"),
 *       @OA\Property(property="expires_at", type="integer", example="1667342171"),
 *       @OA\Property(property="token_type", type="string", example="bearer"),
 * ),
 * @OA\Schema(schema="TokenCredentials", required={"username","password"},
 *     @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 *     @OA\Property(property="username", type="string", format="username", example="MyUsername"),
 * ),
 */
class TokenController extends Controller
{
    /**
     * @OA\Post(path="/api/v1/auth/token", summary="Creates a token", description="Exchange credentials for a new token", operationId="authToken", tags={"auth"},
     *     @OA\RequestBody(required=true, description="User credentials",
     *         @OA\JsonContent(ref="#/components/schemas/TokenCredentials"),
     *     ),
     *     @OA\Response(response=201, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/Token")
     *     ),
     *     @OA\Response(response=401, description="Returns when credentials are incorrect.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorIncorrectCredentials")
     *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    private function token()
    {
    }

    /**
     * @OA\Post(path="/api/v1/auth/refresh", summary="Revokes and issues a new token", description="Refresh token.", operationId="authRefresh", tags={"auth"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when refresh is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/Token")
     *     ),
     *     @OA\Response(response=401, description="Returns when request is unauthenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     * )
     */
    public function refresh()
    {
    }

    /**
     * @OA\Post(path="/api/v1/auth/logout", summary="Revokes a token", description="Revoke token and logout.", operationId="authLogout", tags={"auth"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when token was logged out."),
     *     @OA\Response(response=401, description="Returns when request is unauthenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorUnauthenticated")
     *     ),
     * )
     */
    public function logout()
    {
    }
}
