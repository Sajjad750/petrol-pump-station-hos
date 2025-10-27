<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // User Management
            ['name' => 'view-users', 'display_name' => 'View Users', 'description' => 'Can view user list'],
            ['name' => 'create-users', 'display_name' => 'Create Users', 'description' => 'Can create new users'],
            ['name' => 'edit-users', 'display_name' => 'Edit Users', 'description' => 'Can edit existing users'],
            ['name' => 'delete-users', 'display_name' => 'Delete Users', 'description' => 'Can delete users'],

            // Dashboard
            ['name' => 'view-dashboard', 'display_name' => 'View Dashboard', 'description' => 'Can view dashboard'],

            // Pump Transactions
            ['name' => 'view-pump-transactions', 'display_name' => 'View Pump Transactions', 'description' => 'Can view pump transactions'],
            ['name' => 'export-pump-transactions', 'display_name' => 'Export Pump Transactions', 'description' => 'Can export pump transactions'],

            // Tank Management
            ['name' => 'view-tank-measurements', 'display_name' => 'View Tank Measurements', 'description' => 'Can view tank measurements'],
            ['name' => 'view-tank-deliveries', 'display_name' => 'View Tank Deliveries', 'description' => 'Can view tank deliveries'],
            ['name' => 'view-tank-inventories', 'display_name' => 'View Tank Inventories', 'description' => 'Can view tank inventories'],
            ['name' => 'export-tank-data', 'display_name' => 'Export Tank Data', 'description' => 'Can export tank related data'],

            // Reports
            ['name' => 'view-product-wise-summaries', 'display_name' => 'View Product Summaries', 'description' => 'Can view product wise summaries'],
            ['name' => 'view-payment-mode-summaries', 'display_name' => 'View Payment Summaries', 'description' => 'Can view payment mode wise summaries'],
            ['name' => 'view-shift-reports', 'display_name' => 'View Shift Reports', 'description' => 'Can view shift reports'],
            ['name' => 'export-reports', 'display_name' => 'Export Reports', 'description' => 'Can export reports'],

            // Settings
            ['name' => 'view-fuel-grades', 'display_name' => 'View Fuel Grades', 'description' => 'Can view fuel grades'],
            ['name' => 'view-pumps', 'display_name' => 'View Pumps', 'description' => 'Can view pumps'],
            ['name' => 'view-shift-templates', 'display_name' => 'View Shift Templates', 'description' => 'Can view shift templates'],
            ['name' => 'view-pts-users', 'display_name' => 'View PTS Users', 'description' => 'Can view PTS users'],
        ];

        // Create permissions
        $createdPermissions = [];

        foreach ($permissions as $permission) {
            $createdPermissions[$permission['name']] = Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'description' => $permission['description'],
                ]
            );
        }

        // Define roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full access to all features',
                'permissions' => array_keys($createdPermissions), // All permissions
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Can view and export all data',
                'permissions' => [
                    'view-dashboard',
                    'view-pump-transactions',
                    'export-pump-transactions',
                    'view-tank-measurements',
                    'view-tank-deliveries',
                    'view-tank-inventories',
                    'export-tank-data',
                    'view-product-wise-summaries',
                    'view-payment-mode-summaries',
                    'view-shift-reports',
                    'export-reports',
                    'view-fuel-grades',
                    'view-pumps',
                    'view-shift-templates',
                    'view-pts-users',
                ],
            ],
            [
                'name' => 'operator',
                'display_name' => 'Operator',
                'description' => 'Can view basic data',
                'permissions' => [
                    'view-dashboard',
                    'view-pump-transactions',
                    'view-tank-measurements',
                    'view-tank-deliveries',
                    'view-tank-inventories',
                    'view-product-wise-summaries',
                    'view-payment-mode-summaries',
                    'view-shift-reports',
                ],
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to dashboard and basic reports',
                'permissions' => [
                    'view-dashboard',
                    'view-pump-transactions',
                    'view-shift-reports',
                ],
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                ]
            );

            // Assign permissions to role
            foreach ($roleData['permissions'] as $permissionName) {
                if (isset($createdPermissions[$permissionName])) {
                    $role->givePermissionTo($createdPermissions[$permissionName]);
                }
            }
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
