<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);
        $roles = Role::all();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\UserController::class)->store(
            app(\App\Http\Requests\UserRequest::class)
        );
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user->load(['roles', 'permissions']),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\UserController::class)->update(
            app(\App\Http\Requests\UserRequest::class),
            $user
        );
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Use the API controller to handle the logic
        return app(\App\Http\Controllers\Api\UserController::class)->destroy($user);
    }

    // Advanced actions - these will be called via AJAX
    public function toggleActive(User $user)
    {
        $this->authorize('update', $user);
        return app(\App\Http\Controllers\Api\UserController::class)->toggleActive($user);
    }

    public function block(User $user)
    {
        $this->authorize('update', $user);
        return app(\App\Http\Controllers\Api\UserController::class)->block($user);
    }

    public function unblock(User $user)
    {
        $this->authorize('update', $user);
        return app(\App\Http\Controllers\Api\UserController::class)->unblock($user);
    }

    public function disable2FA(User $user)
    {
        $this->authorize('update', $user);
        return app(\App\Http\Controllers\Api\UserController::class)->disableTwoFactor($user);
    }

    public function forcePasswordReset(User $user)
    {
        $this->authorize('update', $user);
        return app(\App\Http\Controllers\Api\UserController::class)->forcePasswordReset($user);
    }
}