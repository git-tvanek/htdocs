<?php
namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;

class BlockUser
{
    public function __invoke(Request $request, User $user)
    {
        $user->blocked = true;
        $user->save();
        return response()->json(['blocked' => true]);
    }
}
