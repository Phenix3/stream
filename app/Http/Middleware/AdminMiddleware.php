<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non autorisé'], 401);
            }
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur est un administrateur
        $user = auth()->user();
        if (!$user->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé. Privilèges administrateur requis.'], 403);
            }
            abort(403, 'Accès refusé. Privilèges administrateur requis.');
        }

        return $next($request);
    }
} 