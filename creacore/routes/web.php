<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users management
        Route::resource('users', UserController::class);
        Route::prefix('users')->name('users.')->group(function () {
            Route::post('{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
            Route::post('{user}/block', [UserController::class, 'block'])->name('block');
            Route::post('{user}/unblock', [UserController::class, 'unblock'])->name('unblock');
            Route::post('{user}/disable-2fa', [UserController::class, 'disable2FA'])->name('disable-2fa');
            Route::post('{user}/force-password-reset', [UserController::class, 'forcePasswordReset'])->name('force-password-reset');
        });
        
        // Roles management
        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');
        
        // Permissions management
        Route::resource('permissions', PermissionController::class);
    });
});