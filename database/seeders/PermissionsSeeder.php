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
            // Tenant Settings
            'tenant-settings.view',
            'tenant-settings.update',

            // Users & Roles
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            // Performance
            'performance.view',
            'performance.create',
            'performance.update',
            'performance.delete',
            'performance.import',
            'performance.export',

            // Repair Orders (Asset Maintenance)
            'repair-orders.view',
            'repair-orders.create',
            'repair-orders.update',
            'repair-orders.delete',
            'repair-orders.import',
            'repair-orders.export',

            // Safety Data
            'safety-data.view',
            'safety-data.create',
            'safety-data.update',
            'safety-data.delete',
            'safety-data.import',
            'safety-data.export',

            // Trucks
            'trucks.view',
            'trucks.create',
            'trucks.update',
            'trucks.delete',
            'trucks.import',
            'trucks.export',

            // On-Time / Delays
            'delays.view',
            'delays.create',
            'delays.update',
            'delays.delete',
            'delays.import',
            'delays.export',

            // Drivers
            'drivers.view',
            'drivers.create',
            'drivers.update',
            'drivers.delete',
            'drivers.import',
            'drivers.export',

            // Acceptance / Rejections
            'acceptance.view',
            'acceptance.create',
            'acceptance.update',
            'acceptance.delete',
            'acceptance.import',
            'acceptance.export',

            // Miles Driven
            'miles-driven.view',
            'miles-driven.create',
            'miles-driven.update',
            'miles-driven.delete',

            // Feedback
            'feedback.view',
            'feedback.show',
            'feedback.create',
            'feedback.delete',

            // Support Tickets
            'support-tickets.view',
            'support-tickets.show',
            'support-tickets.create',
            'support-tickets.update-status',
            'support-tickets.delete',
            'support-responses.create',

            // SMS Coaching (Safety Thresholds)
            'sms-coaching.view',
            'sms-coaching.update',
            'sms-coaching.delete',
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
