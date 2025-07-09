<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Obtenir le profil de l'utilisateur actuel
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['favorites', 'downloads', 'watchHistory']);

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar ? url($user->avatar) : null,
            'joinDate' => $user->created_at?->toISOString(),
            'statistics' => $user->statistics,
        ]);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'avatar' => ['sometimes', 'string'], // Base64 image data
            'phone' => ['sometimes', 'string', 'max:20', 'unique:users,phone,' . $request->user()->id],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();
        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
            // Reset phone verification if phone changed
            if ($user->phone !== $request->phone) {
                $updateData['phone_verified_at'] = null;
            }
        }

        if ($request->has('avatar')) {
            // Handle base64 image upload
            $avatarPath = $this->handleAvatarUpload($request->avatar, $user->id);
            if ($avatarPath) {
                $updateData['avatar'] = $avatarPath;
            }
        }

        $user->update($updateData);

        return ApiResponse::success([
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error('Mot de passe actuel incorrect', 400, 'INVALID_PASSWORD');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return ApiResponse::success(null, 'Mot de passe mis à jour avec succès');
    }

    /**
     * Supprimer le compte utilisateur
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors()->toArray());
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return ApiResponse::error('Mot de passe incorrect', 400, 'INVALID_PASSWORD');
        }

        // Supprimer l'avatar s'il existe
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Supprimer tous les tokens
        $user->tokens()->delete();

        // Supprimer l'utilisateur
        $user->delete();

        return ApiResponse::success(null, 'Compte supprimé avec succès');
    }

    /**
     * Gérer l'upload d'avatar en base64
     */
    private function handleAvatarUpload(string $base64Data, int $userId): ?string
    {
        try {
            // Vérifier que c'est du base64 valide
            if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                return null;
            }

            $imageType = $matches[1];
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                return null;
            }

            // Générer un nom de fichier unique
            $fileName = 'avatars/' . $userId . '_' . time() . '.' . $imageType;

            // Sauvegarder le fichier
            Storage::disk('public')->put($fileName, $imageData);

            return $fileName;

        } catch (\Exception $e) {
            return null;
        }
    }
} 