<?php
namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;

class DisableTwoFactorAuth
{
    public function __invoke(Request $request, User $user)
    {
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
        return response()->noContent();
    }
}
