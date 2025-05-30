<?php
namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;

class UnblockUser
{
    public function __invoke(Request $request, User $user)
    {
        $user->blocked = false;
        $user->save();
        return response()->json(['blocked' => false]);
    }
}
