<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return ApiResponse::unauthorized('Authentification requise');
        }

        // Vérifier si l'utilisateur a les droits d'administration
        // Pour cet exemple, on peut vérifier un champ 'is_admin' ou 'role'
        // Vous devrez adapter cette logique selon votre système de rôles
        
        // Option 1: Vérifier par email (pour la démonstration)
        $adminEmails = [
            'admin@seledjam.com',
            'superadmin@seledjam.com',
        ];

        if (in_array($user->email, $adminEmails)) {
            return $next($request);
        }

        // Option 2: Si vous avez un champ is_admin dans la table users
        // if ($user->is_admin) {
        //     return $next($request);
        // }

        // Option 3: Si vous avez un système de rôles plus complexe
        // if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
        //     return $next($request);
        // }

        return ApiResponse::forbidden('Accès administrateur requis');
    }
} 