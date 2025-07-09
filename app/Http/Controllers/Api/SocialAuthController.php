<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use App\Http\Resources\SessionResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirection vers Google OAuth
     */
    public function googleRedirect()
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');
        $scope = 'openid email profile';
        $state = Str::random(40);

        session(['oauth_state' => $state]);

        $url = "https://accounts.google.com/o/oauth2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect($url);
    }

    /**
     * Callback Google OAuth
     */
    public function googleCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        // Vérifier le state pour la sécurité CSRF
        if ($request->state !== session('oauth_state')) {
            return ApiResponse::error('State invalide', 400, 'INVALID_STATE');
        }

        try {
            // Échanger le code contre un token d'accès
            $tokenResponse = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $request->code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.google.redirect'),
            ]);

            if (!$tokenResponse->successful()) {
                return ApiResponse::error('Erreur lors de l\'échange du code', 400, 'TOKEN_EXCHANGE_ERROR');
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];

            // Obtenir les informations de l'utilisateur
            $userResponse = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (!$userResponse->successful()) {
                return ApiResponse::error('Erreur lors de la récupération des données utilisateur', 400, 'USER_DATA_ERROR');
            }

            $googleUser = $userResponse->json();

            // Trouver ou créer l'utilisateur
            $user = User::where('email', $googleUser['email'])->first();
            $isNewUser = false;

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'avatar' => $googleUser['picture'] ?? null,
                    'email_verified_at' => now(),
                    'account_id' => 'acc_' . uniqid(),
                ]);
                $isNewUser = true;
            } else {
                // Mettre à jour les informations si nécessaire
                $user->update([
                    'name' => $googleUser['name'],
                    'avatar' => $googleUser['picture'] ?? $user->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $sessionData = (object) [
                'token' => $token,
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
            ];

            return ApiResponse::success([
                'user' => new UserResource($user),
                'session' => new SessionResource($sessionData),
                'isNewUser' => $isNewUser,
            ]);

        } catch (\Exception $e) {
            return ApiResponse::serverError('Erreur lors de l\'authentification Google');
        }
    }
} 