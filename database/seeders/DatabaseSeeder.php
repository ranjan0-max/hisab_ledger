<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Permissions
        $permissions = [
            // Clients
            ['key' => 'clients.view', 'name' => 'View Clients', 'module' => 'clients'],
            ['key' => 'clients.manage', 'name' => 'Manage Clients', 'module' => 'clients'],
            
            // Customers
            ['key' => 'customers.view', 'name' => 'View Customers', 'module' => 'customers'],
            ['key' => 'customers.manage', 'name' => 'Manage Customers', 'module' => 'customers'],
            
            // Suppliers
            ['key' => 'suppliers.view', 'name' => 'View Suppliers', 'module' => 'suppliers'],
            ['key' => 'suppliers.manage', 'name' => 'Manage Suppliers', 'module' => 'suppliers'],
            
            // Daily Entries
            ['key' => 'daily.view', 'name' => 'View Daily Entries', 'module' => 'daily'],
            ['key' => 'daily.manage', 'name' => 'Manage Daily Entries', 'module' => 'daily'],
            
            // Users
            ['key' => 'users.view', 'name' => 'View Users', 'module' => 'users'],
            ['key' => 'users.manage', 'name' => 'Manage Users', 'module' => 'users'],
            
            // Roles
            ['key' => 'roles.view', 'name' => 'View Roles', 'module' => 'roles'],
            ['key' => 'roles.manage', 'name' => 'Manage Roles', 'module' => 'roles'],
            
            // Audit
            ['key' => 'audit.view', 'name' => 'View Audit Logs', 'module' => 'audit'],
            
            // Slow Query
            ['key' => 'slow_query.view', 'name' => 'View Slow Query Logs', 'module' => 'slow_query'],
            ['key' => 'slow_query.manage', 'name' => 'Manage Slow Query Settings', 'module' => 'slow_query'],
            
            // Backups
            ['key' => 'backup.view', 'name' => 'View Database Backups', 'module' => 'backup'],
            ['key' => 'backup.manage', 'name' => 'Manage & Trigger Backups', 'module' => 'backup'],

            // Transactions Void
            ['key' => 'transactions.void', 'name' => 'Void Transactions', 'module' => 'transactions'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $perm['key']],
                ['name' => $perm['name'], 'module' => $perm['module']]
            );
        }

        // 2. Seed Default System Roles
        $superAdminRoleId = DB::table('roles')->where('name', 'SuperAdmin')->value('id');
        if (!$superAdminRoleId) {
            $superAdminRoleId = DB::table('roles')->insertGetId([
                'name' => 'SuperAdmin',
                'description' => 'System Super Administrator with full unrestricted access',
                'is_system_role' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('id');
        if (!$adminRoleId) {
            $adminRoleId = DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'description' => 'Shop Manager with operational permissions',
                'is_system_role' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Assign all permissions to Admin role by default
        $allPermissionIds = DB::table('permissions')->pluck('id');
        foreach ($allPermissionIds as $permId) {
            DB::table('role_permissions')->updateOrInsert([
                'role_id' => $adminRoleId,
                'permission_id' => $permId,
            ]);
        }

        // 4. Read credentials dynamically from .env
        $superAdminUsername = env('SUPER_ADMIN_USERNAME', 'superadmin');
        $superAdminPassword = env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!');

        // Seed SuperAdmin User from .env
        DB::table('users')->updateOrInsert(
            ['username' => $superAdminUsername],
            [
                'password_hash' => Hash::make($superAdminPassword),
                'role_id' => $superAdminRoleId,
                'client_id' => null,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 5. Seed Default Slow Query Setting
        DB::table('slow_query_settings')->updateOrInsert(
            ['id' => 1],
            ['threshold_ms' => 500]
        );
    }
}
