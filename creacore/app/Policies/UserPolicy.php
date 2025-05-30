<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth)
    {
        return $auth->hasRole('admin');
    }

    public function view(User $auth, User $user)
    {
        return $auth->hasRole('admin') || $auth->id === $user->id;
    }

    public function create(User $auth)
    {
        return $auth->hasRole('admin');
    }

    public function update(User $auth, User $user)
    {
        return $auth->hasRole('admin') || $auth->id === $user->id;
    }

    public function delete(User $auth, User $user)
    {
        return $auth->hasRole('admin') && $auth->id !== $user->id;
    }
}
