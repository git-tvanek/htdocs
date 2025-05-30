<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Hash;

class UserController extends Controller
{
    public function index() { return User::with('roles')->paginate(10); }

    public function store(UserRequest $req)
    {
        $d=$req->validated();
        $d['password']=Hash::make($d['password']);
        $u=User::create($d);
        $u->syncRoles($req->roles);
        activity()->causedBy(auth()->user())->performedOn($u)->log('Created user');
        return response()->json($u->load('roles'),201);
    }

    public function show(User $user) { return $user->load('roles'); }

    public function update(UserRequest $req, User $user)
    {
        $d=$req->validated();
        if(isset($d['password'])) {$d['password']=Hash::make($d['password']); } else { unset($d['password']); }
        $user->update($d);
        $user->syncRoles($req->roles);
        activity()->causedBy(auth()->user())->performedOn($user)->log('Updated user');
        return response()->json($user->load('roles'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        activity()->causedBy(auth()->user())->performedOn($user)->log('Deleted user');
        return response()->noContent();
    }

    // pokročilé
    public function search(Request $r) { $q=$r->input('q'); return User::where('name','like',"%{\$q}%")->orWhere('email','like',"%{\$q}%")->with('roles')->paginate(10); }
    public function toggleActive(User $user) { $user->active=!$user->active; $user->save(); activity()->causedBy(auth()->user())->performedOn($user)->log('Toggled active'); return response()->json(['active'=>$user->active]); }
    public function block(User $user) { $user->blocked=true; $user->save(); activity()->causedBy(auth()->user())->performedOn($user)->log('Blocked user'); return response()->json(['blocked'=>true]); }
    public function unblock(User $user) { $user->blocked=false; $user->save(); activity()->causedBy(auth()->user())->performedOn($user)->log('Unblocked user'); return response()->json(['blocked'=>false]); }
    public function disableTwoFactor(User $user) { $user->two_factor_secret=null; $user->two_factor_confirmed_at=null; $user->save(); activity()->causedBy(auth()->user())->performedOn($user)->log('Disabled 2FA'); return response()->noContent(); }
    public function forcePasswordReset(User $user) { $user->force_password_reset=true; $user->save(); activity()->causedBy(auth()->user())->performedOn($user)->log('Forced password reset'); return response()->noContent(); }
}