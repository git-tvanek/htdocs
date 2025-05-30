<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index()
    {
        return Role::with('permissions')
            ->withCount('users')
            ->paginate(10);
    }

    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log('Created role');

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role)
    {
        return $role->load(['permissions', 'users']);
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log('Updated role');

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return response()->json(['message' => 'Admin role cannot be deleted'], 403);
        }

        $role->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log('Deleted role');

        return response()->noContent();
    }

    public function permissions()
    {
        return Permission::all();
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->syncPermissions($request->permissions);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log('Updated role permissions');

        return response()->json($role->load('permissions'));
    }
}