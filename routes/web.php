<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Admin\CreatorController as AdminCreatorController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Route de déconnexion
Route::post('logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');

// Routes d'administration
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Tableau de bord
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des utilisateurs
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::post('users/{user}/verify-email', [AdminUserController::class, 'verifyEmail'])->name('users.verify-email');
    Route::post('users/{user}/verify-phone', [AdminUserController::class, 'verifyPhone'])->name('users.verify-phone');
    
    // Gestion des vidéos
    Route::resource('videos', AdminVideoController::class);
    Route::post('videos/bulk-visibility', [AdminVideoController::class, 'bulkUpdateVisibility'])->name('videos.bulk-visibility');
    Route::delete('videos/bulk-delete', [AdminVideoController::class, 'bulkDelete'])->name('videos.bulk-delete');
    
    // Gestion des créateurs
    Route::resource('creators', AdminCreatorController::class);
    Route::post('creators/{creator}/toggle-verification', [AdminCreatorController::class, 'toggleVerification'])->name('creators.toggle-verification');
});

require __DIR__.'/auth.php';
