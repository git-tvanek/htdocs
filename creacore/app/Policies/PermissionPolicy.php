<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('permissions.view');
    }

    public function view(User $user, Permission $permission)
    {
        return $user->hasPermissionTo('permissions.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('permissions.create');
    }

    public function update(User $user, Permission $permission)
    {
        return $user->hasPermissionTo('permissions.update');
    }

    public function delete(User $user, Permission $permission)
    {
        return $user->hasPermissionTo('permissions.delete');
    }
}