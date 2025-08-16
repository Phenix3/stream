<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OtpVerification;
use App\Http\Resources\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\WhatsAppOtpService;
use App\Http\Resources\SessionResource;
use Illuminate\Support\Facades\Validator;

class PhoneAuthController extends Controller
{
    public function __construct(private WhatsAppOtpService $whatsappOtpService)
    {

    }

    /**
     * Demander un code OTP
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phoneNumber' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
        ]);


        $phone = $request->phoneNumber;

        if (!$this->whatsappOtpService->validatePhoneNumber($phone)) {
            return ApiResponse::validationError([
                'phoneNumber' => 'Invalid phone number'
            ]);
        }

        $result = $this->whatsappOtpService->sendOtp($phone);

        if (!$result['success']) {
            return ApiResponse::error($result['error'], $result['status_code'], 'OTP_SEND_FAILED');
        }

        return ApiResponse::success([
            'userId' => $result['data']['temp_user_id'],
            'message' => 'Code OTP envoyé',
            'expiresIn' => 3000, // 5 minutes
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
            'phoneNumber' => ['required', 'string'],
        ]);

        $phone = $request->phoneNumber;

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $result = $this->whatsappOtpService->verifyOtp($phone, $request->otp);

        if (!$result['success']) {
            return ApiResponse::error($result['error'], $result['status_code'], 'OTP_VERIFY_FAILED');
        }


        // Vérifier si l'utilisateur existe déjà
        $user = User::where('phone', $phone)->first();
        $isNewUser = false;

        // if (!$user) {
        //     // Créer un nouvel utilisateur
        //     $user = User::create([
        //         'name' => 'Utilisateur ' . substr($phone, -4),
        //         'phone' => $phone,
        //         'phone_verified_at' => now(),
        //         'account_id' => 'acc_' . uniqid(),
        //     ]);
        //     $isNewUser = true;
        // } else {
        //     // Marquer le téléphone comme vérifié
        //     $user->update(['phone_verified_at' => now()]);
        // }

        $token = $user->createToken('auth_token')->plainTextToken;

        $sessionData = (object) [
            'token' => $token,
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
        ];

        return ApiResponse::success([
            'user' => new UserResource($user),
            'session' => new SessionResource($sessionData),
            'isNewUser' => $isNewUser,
        ]);
    }
}
