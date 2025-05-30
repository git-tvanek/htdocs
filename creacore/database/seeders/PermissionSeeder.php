<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.block',
            'users.activate',
            'users.force-password-reset',
            'users.disable-2fa',
            
            // Role permissions
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign-permissions',
            
            // Permission permissions
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            
            // Dashboard permissions
            'dashboard.view',
            'dashboard.stats',
            'dashboard.reports',
            
            // Profile permissions
            'profile.update',
            'profile.delete',
            
            // API permissions
            'api.access',
            'api.tokens.create',
            'api.tokens.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);
        $userRole = Role::create(['name' => 'user']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        
        $editorRole->givePermissionTo([
            'users.view',
            'users.update',
            'roles.view',
            'permissions.view',
            'dashboard.view',
            'dashboard.stats',
            'profile.update',
            'api.access'
        ]);
        
        $userRole->givePermissionTo([
            'dashboard.view',
            'profile.update',
            'api.access'
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create editor user
        $editor = User::create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $editor->assignRole('editor');

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');
    }
}