<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = Permission::with('roles')->paginate(10);

        return Inertia::render('Admin/Permissions', [
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Permission::class);

        return Inertia::render('Admin/Permissions/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Permission::class);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\PermissionController::class)->store(
            app(\App\Http\Requests\PermissionRequest::class)
        );
    }

    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);

        return Inertia::render('Admin/Permissions/Show', [
            'permission' => $permission->load(['roles', 'users']),
        ]);
    }

    public function edit(Permission $permission)
    {
        $this->authorize('update', $permission);

        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $this->authorize('update', $permission);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\PermissionController::class)->update(
            app(\App\Http\Requests\PermissionRequest::class),
            $permission
        );
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('delete', $permission);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\PermissionController::class)->destroy($permission);
    }
}