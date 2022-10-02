<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @OA\Post(
 * path="/api/v1/auth/token",
 * summary="Retrieve Token",
 * description="Retrieve token by username, password",
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
 *    description="Returns when user is authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="token", type="string", example="2|tR6PIqa8tiBJWMu4zyb3qw4eECuERjLd7xeLKgBu"),
 *       @OA\Property(property="expires_at", type="integer", example="1667342171"),
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
 *    description="Returns when user is not authenticated",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
 *    )
 * )
 * )
 */

class TokenController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function token(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        $success = false;
        if ($user = $this->userRepository->getByUsername($request->username)) {
            $success = Hash::check($request->password, $user->password_string);
        } else {
            Hash::check($request->password, '');
        }

        if (!$success) {
            return new JsonResponse(['message' => 'The provided credentials are incorrect.'], 401);
        }

        Artisan::call('sanctum:prune-expired', ['--no-interaction' => true]);

        return new JsonResponse($this->createToken($user));
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        // Give the app a tiny 10s grace period to swap tokens. This is only necessary because
        // sanctum does not have proper refresh tokens, and implementing proper oauth would be
        // challenging given that our customers are unlikely to be able to generate and manage
        // a public/private keypair for token signing.
        $currentToken = $user->currentAccessToken();
        $currentToken->expires_at = time() + 10;
        $currentToken->save();

        return new JsonResponse($this->createToken($user));
    }

    private function createToken(User $user)
    {
        $expiresAt = 60 * config('sanctum.expiration', 0);
        $expiresAt += time();

        return [
            'token' => $user->createToken(Str::random(20))->plainTextToken,
            'expires_at' => $expiresAt,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }
}
