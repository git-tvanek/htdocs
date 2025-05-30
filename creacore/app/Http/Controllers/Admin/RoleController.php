<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')
            ->withCount('users')
            ->paginate(10);
            
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\RoleController::class)->store(
            app(\App\Http\Requests\RoleRequest::class)
        );
    }

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role->load(['permissions', 'users']),
        ]);
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\RoleController::class)->update(
            app(\App\Http\Requests\RoleRequest::class),
            $role
        );
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\RoleController::class)->destroy($role);
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        return app(\App\Http\Controllers\Api\RoleController::class)->assignPermissions($request, $role);
    }
}