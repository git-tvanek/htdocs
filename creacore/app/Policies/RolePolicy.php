<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('roles.view');
    }

    public function view(User $user, Role $role)
    {
        return $user->hasPermissionTo('roles.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('roles.create');
    }

    public function update(User $user, Role $role)
    {
        // Zabránit editaci admin role ne-admin uživateli
        if ($role->name === 'admin' && !$user->hasRole('admin')) {
            return false;
        }
        
        return $user->hasPermissionTo('roles.update');
    }

    public function delete(User $user, Role $role)
    {
        // Zabránit smazání admin role
        if ($role->name === 'admin') {
            return false;
        }
        
        return $user->hasPermissionTo('roles.delete');
    }
}