<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    /**
     * Réponse de succès
     */
    public static function success($data = null, string $message = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($data !== null) {
            if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
                $response['data'] = $data->toArray(request());
            } else {
                $response['data'] = $data;
            }
        }

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    /**
     * Réponse d'erreur
     */
    public static function error(
        string $message, 
        int $status = 400, 
        string $code = null, 
        array $details = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
            ],
        ];

        if ($code) {
            $response['error']['code'] = $code;
        }

        if ($details) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $status);
    }

    /**
     * Réponse de validation échouée
     */
    public static function validationError(array $errors, string $message = 'Les données fournies ne sont pas valides'): JsonResponse
    {
        return self::error($message, 400, 'VALIDATION_ERROR', $errors);
    }

    /**
     * Réponse d'authentification échouée
     */
    public static function unauthorized(string $message = 'Non authentifié'): JsonResponse
    {
        return self::error($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Réponse d'accès refusé
     */
    public static function forbidden(string $message = 'Accès refusé'): JsonResponse
    {
        return self::error($message, 403, 'FORBIDDEN');
    }

    /**
     * Réponse de ressource non trouvée
     */
    public static function notFound(string $message = 'Ressource non trouvée'): JsonResponse
    {
        return self::error($message, 404, 'NOT_FOUND');
    }

    /**
     * Réponse de trop de requêtes
     */
    public static function tooManyRequests(string $message = 'Trop de requêtes'): JsonResponse
    {
        return self::error($message, 429, 'TOO_MANY_REQUESTS');
    }

    /**
     * Réponse d'erreur serveur
     */
    public static function serverError(string $message = 'Erreur serveur interne'): JsonResponse
    {
        return self::error($message, 500, 'INTERNAL_ERROR');
    }
}