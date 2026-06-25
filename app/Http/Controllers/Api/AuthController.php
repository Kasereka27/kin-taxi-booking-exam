<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
            return ApiResponse::error('Identifiants incorrects.', 401);
        }

        if (! $user->is_active) {
            return ApiResponse::error('Votre compte est désactivé.', 403);
        }

        if ($user->two_factor_enabled) {
            return ApiResponse::error(
                'La double authentification est activée. Connectez-vous via l\'application web ou désactivez le 2FA pour utiliser l\'API.',
                403,
            );
        }

        $deviceName = $request->string('device_name')->toString() ?: 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ], 'Connexion réussie.');
    }

    public function logout(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken !== null) {
            PersonalAccessToken::findToken($bearerToken)?->delete();
        } else {
            $token = $request->user()?->currentAccessToken();

            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }
        }

        return ApiResponse::success(null, 'Déconnexion réussie.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
        ];
    }
}
