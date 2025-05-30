<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\DashboardController;

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

Route::middleware(['auth:sanctum'])->group(function () {
    // Current user
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['roles.permissions']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/charts', [DashboardController::class, 'charts']);
    });

    // Users management
    Route::apiResource('users', UserController::class);
    Route::prefix('users')->group(function () {
        Route::get('/search', [UserController::class, 'search']);
        Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive']);
        Route::post('/{user}/block', [UserController::class, 'block']);
        Route::post('/{user}/unblock', [UserController::class, 'unblock']);
        Route::post('/{user}/disable-2fa', [UserController::class, 'disableTwoFactor']);
        Route::post('/{user}/force-password-reset', [UserController::class, 'forcePasswordReset']);
    });

    // Roles management
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    Route::get('roles-all', [RoleController::class, 'all']); // Without pagination

    // Permissions management
    Route::apiResource('permissions', PermissionController::class);
    Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
    Route::get('permissions-all', [PermissionController::class, 'all']); // Without pagination
});