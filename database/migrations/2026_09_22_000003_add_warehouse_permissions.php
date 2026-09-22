<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['key' => 'warehouses.view', 'name' => 'View Warehouses', 'module' => 'warehouses'],
        ['key' => 'warehouses.manage', 'name' => 'Manage Warehouses', 'module' => 'warehouses'],
        ['key' => 'warehouse_items.view', 'name' => 'View Warehouse Items', 'module' => 'warehouse_items'],
        ['key' => 'warehouse_items.manage', 'name' => 'Manage Warehouse Items', 'module' => 'warehouse_items'],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $permission['key']],
                ['name' => $permission['name'], 'module' => $permission['module']],
            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('key', array_column($this->permissions, 'key'))
            ->delete();
    }
};
