<?php

namespace App\Http\Controllers\Admin\Swagger;

/**
 * @OA\Schema(schema="Token", required={"access_token", "expires_at", "token_type", "user_id"},
 *     @OA\Property(property="access_token", type="string", example="2|tR6PIqa8tiBJWMu4zyb3qw4eECuERjLd7xeLKgBu"),
 *     @OA\Property(property="expires_at", type="integer", example="1667342171"),
 *     @OA\Property(property="token_type", type="string", example="bearer"),
 *     @OA\Property(property="user_id", type="integer", example="1"),
 * ),
 * @OA\Schema(schema="TokenCredentials", required={"username","password"},
 *     @OA\Property(property="username", type="string", format="username", example="MyUsername"),
 *     @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
 * ),
 */
class TokenController extends Controller
{
    /**
     * @OA\Post(path="/api/v1/auth/token", summary="Creates a token", description="Exchange credentials for a new token", operationId="authToken", tags={"rootServer"},
     *     @OA\RequestBody(required=true, description="User credentials",
     *         @OA\JsonContent(ref="#/components/schemas/TokenCredentials"),
     *     ),
     *     @OA\Response(response=200, description="Returns when POST is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/Token")
     *     ),
     *     @OA\Response(response=401, description="Returns when credentials are incorrect.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     *     @OA\Response(response=403, description="Returns when user is deactivated.",
     * *         @OA\JsonContent(ref="#/components/schemas/DeactivatedError")
     * *     ),
     *     @OA\Response(response=422, description="Validation error.",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     * )
     */
    private function token()
    {
    }

    /**
     * @OA\Post(path="/api/v1/auth/refresh", summary="Revokes and issues a new token", description="Refresh token.", operationId="authRefresh", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when refresh is successful.",
     *         @OA\JsonContent(ref="#/components/schemas/Token")
     *     ),
     *     @OA\Response(response=401, description="Returns when request is unauthenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     * )
     */
    public function refresh()
    {
    }

    /**
     * @OA\Post(path="/api/v1/auth/logout", summary="Revokes a token", description="Revoke token and logout.", operationId="authLogout", tags={"rootServer"}, security={{"bmltToken":{}}},
     *     @OA\Response(response=200, description="Returns when token was logged out."),
     *     @OA\Response(response=401, description="Returns when request is unauthenticated.",
     *         @OA\JsonContent(ref="#/components/schemas/AuthenticationError")
     *     ),
     * )
     */
    public function logout()
    {
    }
}
