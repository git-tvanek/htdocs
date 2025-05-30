<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Permission::class, 'permission');
    }

    public function index()
    {
        return Permission::with('roles')
            ->paginate(10);
    }

    public function store(PermissionRequest $request)
    {
        $permission = Permission::create($request->validated());

        activity()
            ->causedBy(auth()->user())
            ->performedOn($permission)
            ->log('Created permission');

        return response()->json($permission, 201);
    }

    public function show(Permission $permission)
    {
        return $permission->load(['roles', 'users']);
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        activity()
            ->causedBy(auth()->user())
            ->performedOn($permission)
            ->log('Updated permission');

        return response()->json($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($permission)
            ->log('Deleted permission');

        return response()->noContent();
    }

    public function grouped()
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $resource = $parts[0];
            $action = $parts[1] ?? 'other';

            if (!isset($grouped[$resource])) {
                $grouped[$resource] = [];
            }

            $grouped[$resource][] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $action
            ];
        }

        return response()->json($grouped);
    }
}