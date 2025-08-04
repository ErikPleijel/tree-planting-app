<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache (important)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view planting locations',
            'edit planting locations',
            'delete records',
            'view reports',
            'manage users', // Admin
            'manage admins',  // SuperAdmin
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles with guard_name explicitly (avoid conflicts)
        $Grower = Role::firstOrCreate(['name' => 'Grower', 'guard_name' => 'web']);
        $Monitor = Role::firstOrCreate(['name' => 'Monitor', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);

        $Grower->syncPermissions([
            'view reports',
        ]);

        $Monitor->syncPermissions([
            'view planting locations',
            'edit planting locations',
            'view reports',
        ]);

        // Give Admin its permissions
        $admin->syncPermissions([
            'view planting locations',
            'edit planting locations',
            'delete records',
            'view reports',
            'manage users',
        ]);

        // Give SuperAdmin all permissions
        $superAdmin->syncPermissions($permissions);


    }
}
