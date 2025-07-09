<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use App\Http\Resources\SessionResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Inscription par email/mot de passe
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'account_id' => 'acc_' . uniqid(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $sessionData = (object) [
            'token' => $token,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
        ];

        return ApiResponse::success([
            'user' => new UserResource($user),
            'session' => new SessionResource($sessionData),
        ], null, 201);
    }

    /**
     * Connexion par email/mot de passe
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::unauthorized('Identifiants incorrects');
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        $sessionData = (object) [
            'token' => $token,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
        ];

        return ApiResponse::success([
            'user' => new UserResource($user),
            'session' => new SessionResource($sessionData),
        ]);
    }

    /**
     * Rafraîchir le token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Révoquer le token actuel
        $request->user()->currentAccessToken()->delete();
        
        // Créer un nouveau token
        $token = $user->createToken('auth_token')->plainTextToken;

        $sessionData = (object) [
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ];

        return ApiResponse::success([
            'token' => $token,
            'expiresAt' => now()->addDays(7)->toISOString(),
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Déconnexion réussie');
    }

    /**
     * Obtenir l'utilisateur actuel
     */
    public function me(Request $request)
    {
        return ApiResponse::success([
            'user' => new UserResource($request->user()),
        ]);
    }
} 