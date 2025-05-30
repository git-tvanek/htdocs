<?php

namespace App\Providers;

use App\Actions\Fortify\SearchUsers;
use App\Actions\Fortify\ToggleActiveUser;
use App\Actions\Fortify\BlockUser;
use App\Actions\Fortify\UnblockUser;
use App\Actions\Fortify\DisableTwoFactorAuth;
use App\Actions\Fortify\ForcePasswordReset;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Standardní Fortify akce
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Přihlášení s kontrolou stavu active/blocked
        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();
            if (
                $user &&
                Hash::check($request->password, $user->password) &&
                $user->active &&
                ! $user->blocked
            ) {
                return $user;
            }
        });

        // Rate limiting
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip())
            );
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->session()->get('login.id')
            );
        });

        // Registrace admin-specifických endpointů
        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes(): void
    {
        $router = app('router');

        $router->prefix('admin')
               ->middleware(['auth:sanctum', 'can:viewAny,App\\Models\\User'])
               ->group(function () use ($router) {
                   $router->get('users/search', SearchUsers::class);
                   $router->post('users/{user}/toggle-active', [ToggleActiveUser::class, '__invoke']);
                   $router->post('users/{user}/block', BlockUser::class);
                   $router->post('users/{user}/unblock', UnblockUser::class);
                   $router->post('users/{user}/disable-2fa', DisableTwoFactorAuth::class);
                   $router->post('users/{user}/force-password-reset', ForcePasswordReset::class);
               });
    }
}
