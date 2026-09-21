<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE contacts MODIFY COLUMN khata_number BIGINT UNSIGNED NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE contacts MODIFY COLUMN khata_number VARCHAR(50) NOT NULL'
        );
    }
};
