<?php

namespace App\Http\Controllers\Admin\Swagger;

class TokenController extends Controller
{

    /**
     * @OA\Post(
     * path="/api/v1/auth/refresh",
     * summary="Refresh Token",
     * description="Refresh token.",
     * operationId="authRefresh",
     * tags={"auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *    response=200,
     *    description="Returns when token is valid.",
     *    @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string", example="2|tR6PIqa8tiBJWMu4zyb3qw4eECuERjLd7xeLKgBu"),
     *       @OA\Property(property="expires_at", type="integer", example="1667342171"),
     *       @OA\Property(property="token_type", type="string", example="bearer"),
     *    )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when token is invalid or missing.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated."),
     *    )
     * )
     * )
     */
    public function refresh()
    {
    }

    /**
     * @OA\Post(
     * path="/api/v1/auth/token",
     * summary="Retrieve Token",
     * description="Retrieve token by username, password.",
     * operationId="authToken",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"username","password"},
     *       @OA\Property(property="username", type="string", format="username", example="MyUsername"),
     *       @OA\Property(property="password", type="string", format="password", example="PassWord12345")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Returns when user is authenticated.",
     *    @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string", example="2|tR6PIqa8tiBJWMu4zyb3qw4eECuERjLd7xeLKgBu"),
     *       @OA\Property(property="expires_at", type="integer", example="1667342171"),
     *       @OA\Property(property="token_type", type="string", example="bearer"),
     *    )
     * ),
     * @OA\Response(
     *     response=422,
     *     description="Validation error",
     *     @OA\JsonContent(
     *        @OA\Property(property="message", type="string", example="The username field is required. (and 1 more error)"),
     *        @OA\Property(
     *           property="errors",
     *           type="object",
     *           @OA\Property(
     *              property="username",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The email field is required.",
     *              )
     *           ),
     *           @OA\Property(
     *              property="password",
     *              type="array",
     *              @OA\Items(
     *                 type="string",
     *                 example="The password field is required.",
     *              )
     *           )
     *        )
     *     )
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated.",
     *    @OA\JsonContent(ref="#/components/schemas/ErrorIncorrectCredentials")
     * )
     * )
     */
    private function createToken()
    {
    }

    /**
     * @OA\Post(
     * path="/api/v1/auth/logout",
     * summary="Revoke Token",
     * description="Revoke token and logout.",
     * operationId="authLogout",
     * tags={"auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     *    response=200,
     *    description="Returns when token is valid.",
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when token is invalid or missing.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated."),
     *    )
     * )
     * )
     */
    public function logout()
    {
    }
}
