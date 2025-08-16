<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhoneAuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\ActorController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\WatchHistoryController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Api\Admin\StatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques (sans authentification)
Route::prefix('auth')->group(function () {
    // Authentification par email/mot de passe
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Authentification par téléphone (OTP)
    Route::post('/phone/request-otp', [PhoneAuthController::class, 'requestOtp']);
    Route::post('/phone/verify-otp', [PhoneAuthController::class, 'verifyOtp']);
    
    // OAuth Google
    Route::get('/google/redirect', [SocialAuthController::class, 'googleRedirect']);
    Route::post('/google/callback', [SocialAuthController::class, 'googleCallback']);
})->withoutMiddleware('auth:sanctum');

// Routes protégées (avec authentification) middleware('auth:sanctum')->
Route::group([], function () {
    
    // ========================================
    // AUTHENTIFICATION ET GESTION DE SESSION
    // ========================================
    Route::prefix('auth')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // ========================================
    // GESTION DU PROFIL UTILISATEUR
    // ========================================
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/change-password', [UserController::class, 'changePassword']);
        Route::delete('/account', [UserController::class, 'deleteAccount']);
    });

    // ========================================
    // GESTION DES VIDÉOS
    // ========================================
    Route::prefix('videos')->group(function () {
        Route::get('/home', [VideoController::class, 'home']);
        Route::get('/search', [VideoController::class, 'search']);
        Route::get('/sections', [VideoController::class, 'sections']);
        Route::get('/genre/{slug}', [VideoController::class, 'byGenre']);
        Route::get('/{id}', [VideoController::class, 'show']);
    });

    // ========================================
    // GESTION DES ACTEURS
    // ========================================
    Route::prefix('actors')->group(function () {
        Route::get('/', [ActorController::class, 'index']);
        Route::get('/search', [ActorController::class, 'search']);
        Route::get('/popular', [ActorController::class, 'popular']);
        Route::get('/{id}', [ActorController::class, 'show']);
    });

    // ========================================
    // GESTION DES FAVORIS
    // ========================================
    Route::prefix('user/favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/{videoId}', [FavoriteController::class, 'toggle']);
        Route::get('/check/{videoId}', [FavoriteController::class, 'check']);
        Route::delete('/{videoId}', [FavoriteController::class, 'destroy']);
        Route::delete('/', [FavoriteController::class, 'clear']);
    });

    // ========================================
    // HISTORIQUE DE VISIONNAGE
    // ========================================
    Route::prefix('user/watch-history')->group(function () {
        Route::get('/', [WatchHistoryController::class, 'index']);
        Route::post('/', [WatchHistoryController::class, 'store']);
        Route::get('/{videoId}', [WatchHistoryController::class, 'show']);
        Route::put('/{videoId}/complete', [WatchHistoryController::class, 'markCompleted']);
        Route::delete('/{videoId}', [WatchHistoryController::class, 'destroy']);
        Route::delete('/', [WatchHistoryController::class, 'clear']);
    });

    // ========================================
    // GESTION DES TÉLÉCHARGEMENTS
    // ========================================
    Route::prefix('user/downloads')->group(function () {
        Route::get('/', [DownloadController::class, 'index']);
        Route::post('/{videoId}', [DownloadController::class, 'request']);
        Route::get('/{downloadId}', [DownloadController::class, 'show']);
        Route::delete('/{downloadId}', [DownloadController::class, 'destroy']);
        Route::delete('/expired', [DownloadController::class, 'clearExpired']);
    });

    // Route spéciale pour le téléchargement de fichiers (peut être publique avec token)
    Route::get('/downloads/file/{token}', [DownloadController::class, 'downloadFile'])
        ->name('api.downloads.file')
        ->withoutMiddleware('auth:sanctum');

    // ========================================
    // ACTIVITÉ UTILISATEUR
    // ========================================
    Route::prefix('user/activity')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::post('/', [ActivityController::class, 'store']);
        Route::get('/stats', [ActivityController::class, 'stats']);
        Route::get('/{activityId}', [ActivityController::class, 'show']);
        Route::delete('/{activityId}', [ActivityController::class, 'destroy']);
        Route::delete('/type/{type}', [ActivityController::class, 'clearByType']);
        Route::delete('/', [ActivityController::class, 'clear']);
    });

    // ========================================
    // NOTIFICATIONS
    // ========================================
    Route::prefix('user/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::post('/', [NotificationController::class, 'create']);
        Route::get('/{notificationId}', [NotificationController::class, 'show']);
        Route::put('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/{notificationId}/unread', [NotificationController::class, 'markAsUnread']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notificationId}', [NotificationController::class, 'destroy']);
        Route::delete('/read', [NotificationController::class, 'clearRead']);
        Route::delete('/', [NotificationController::class, 'clear']);
    });

    // ========================================
    // ADMINISTRATION (avec middleware admin)
    // ========================================
    Route::prefix('admin')->middleware('admin')->group(function () {
        
        // Gestion des vidéos (admin)
        Route::prefix('videos')->group(function () {
            Route::get('/', [AdminVideoController::class, 'index']);
            Route::post('/', [AdminVideoController::class, 'store']);
            Route::get('/{videoId}', [AdminVideoController::class, 'show']);
            Route::put('/{videoId}', [AdminVideoController::class, 'update']);
            Route::delete('/{videoId}', [AdminVideoController::class, 'destroy']);
            Route::put('/bulk/visibility', [AdminVideoController::class, 'bulkUpdateVisibility']);
            Route::delete('/bulk', [AdminVideoController::class, 'bulkDelete']);
        });

        // Statistiques
        Route::prefix('statistics')->group(function () {
            Route::get('/', [StatisticsController::class, 'index']);
            Route::get('/users', [StatisticsController::class, 'users']);
            Route::get('/videos', [StatisticsController::class, 'videos']);
            Route::get('/creators', [StatisticsController::class, 'creators']);
            Route::get('/engagement', [StatisticsController::class, 'engagement']);
            Route::get('/popular', [StatisticsController::class, 'popular']);
            Route::get('/performance', [StatisticsController::class, 'performance']);
        });
    });
});

// ========================================
// ROUTES DE FALLBACK ET GESTION D'ERREURS
// ========================================

// Route pour gérer les endpoints non trouvés
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'ENDPOINT_NOT_FOUND',
            'message' => 'Endpoint non trouvé',
        ]
    ], 404);
}); 