<?php
namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;

class ToggleActiveUser
{
    public function __invoke(Request $request, User $user)
    {
        $user->active = ! $user->active;
        $user->save();
        return response()->json(['active' => $user->active]);
    }
}
