<?php
namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;

class SearchUsers
{
    public function __invoke(Request $request)
    {
        $q = $request->input('q');
        return User::where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->with('roles')
                   ->paginate(10);
    }
}