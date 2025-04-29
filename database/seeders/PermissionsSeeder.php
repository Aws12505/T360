<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Define the permissions for users and roles management.
        $permissions = [
            'receive_updates',
            // Permissions for Users Management
            'user_create',
            'user_view',
            'user_edit',
            'user_delete',

            // Permissions for Roles Management
            'role_create',
            'role_view',
            'role_edit',
            'role_delete',
        ];

        // Create each permission if it doesn't exist.
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdminRole = Role::withoutGlobalScopes()->firstOrCreate(
            ['name' => 'SuperAdmin'],
            ['guard_name' => 'web']
        );

        // Retrieve all permissions that exist
        $allPermissions = Permission::all();

        // Sync all permissions to the SuperAdmin role
        $superAdminRole->syncPermissions($allPermissions);
    }
}
