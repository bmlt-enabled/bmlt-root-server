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

class TokenController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    private function pruneExpiredTokens()
    {
        Artisan::call('sanctum:prune-expired', ['--no-interaction' => true, '--hours' => 1]);
    }

    public function token(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        $success = false;
        $user = $this->userRepository->getByUsername($request->username);
        if ($user) {
            $success = Hash::check($request->password, $user->password_string);
            if ($success && Hash::needsRehash($user->password_string)) {
                $this->userRepository->updatePassword($user->id_bigint, $request->password);
            }
        } else {
            Hash::check($request->password, '');
        }

        if (!$success) {
            return new JsonResponse(['message' => 'The provided credentials are incorrect.'], 401);
        }

        if ($user->isDeactivated()) {
            return new JsonResponse(['message' => 'User is deactivated.'], 403);
        }

        $this->pruneExpiredTokens();

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

        $this->pruneExpiredTokens();

        return new JsonResponse($this->createToken($user));
    }

    private function createToken(User $user)
    {
        $expiresAt = 60 * config('sanctum.expiration', 0);
        $expiresAt += time();

        return [
            'access_token' => $user->createToken(Str::random(20))->plainTextToken,
            'expires_at' => $expiresAt,
            'token_type' => 'bearer',
            'user_id' => $user->id_bigint,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }
}
