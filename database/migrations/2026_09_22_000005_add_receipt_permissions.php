<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        ['key' => 'receipts.view', 'name' => 'View Receipts', 'module' => 'receipts'],
        ['key' => 'receipts.manage', 'name' => 'Manage Receipts', 'module' => 'receipts'],
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
