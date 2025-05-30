<?php
namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;

class ForcePasswordReset
{
    public function __invoke(Request $request, User $user)
    {
        $user->force_password_reset = true;
        $user->save();
        return response()->noContent();
    }
}
