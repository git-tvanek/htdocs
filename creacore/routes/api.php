<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Vrátí přihlášeného uživatele
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Základní CRUD pro uživatele
    Route::apiResource('users', UserController::class);

    // Pokročilé admin endpointy
    Route::get('users/search', [UserController::class, 'search']);
    Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive']);
    Route::post('users/{user}/block', [UserController::class, 'block']);
    Route::post('users/{user}/unblock', [UserController::class, 'unblock']);
    Route::post('users/{user}/disable-2fa', [UserController::class, 'disableTwoFactor']);
    Route::post('users/{user}/force-password-reset', [UserController::class, 'forcePasswordReset']);
});
