<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use App\Http\Resources\SessionResource;
use App\Models\User;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PhoneAuthController extends Controller
{
    /**
     * Demander un code OTP
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $phone = $request->phone;
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $userId = 'temp_' . Str::random(10);

        // Supprimer les anciens codes OTP pour ce numéro
        OtpVerification::where('phone', $phone)
            ->where('expires_at', '<', now())
            ->delete();

        // Créer un nouveau code OTP
        $otpVerification = OtpVerification::create([
            'user_id' => null, // Sera défini après vérification
            'phone' => $phone,
            'otp_code' => $otp,
            'temp_user_id' => $userId,
            'expires_at' => now()->addMinutes(5),
        ]);

        // TODO: Intégrer l'envoi par WhatsApp via Baileys
        // Pour le moment, on simule l'envoi
        
        return ApiResponse::success([
            'userId' => $userId,
            'message' => 'Code OTP envoyé',
            'expiresIn' => 300, // 5 minutes
        ]);
    }

    /**
     * Vérifier le code OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
            'phone' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $otpVerification = OtpVerification::where('temp_user_id', $request->userId)
            ->where('phone', $request->phone)
            ->where('otp_code', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpVerification) {
            return ApiResponse::error('Code OTP invalide ou expiré', 400, 'INVALID_OTP');
        }

        // Vérifier si l'utilisateur existe déjà
        $user = User::where('phone', $request->phone)->first();
        $isNewUser = false;

        if (!$user) {
            // Créer un nouvel utilisateur
            $user = User::create([
                'name' => 'Utilisateur ' . substr($request->phone, -4),
                'phone' => $request->phone,
                'phone_verified_at' => now(),
                'account_id' => 'acc_' . uniqid(),
            ]);
            $isNewUser = true;
        } else {
            // Marquer le téléphone comme vérifié
            $user->update(['phone_verified_at' => now()]);
        }

        // Marquer l'OTP comme utilisé
        $otpVerification->update(['user_id' => $user->id]);
        $otpVerification->delete();

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
    }
} 