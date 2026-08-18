<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('daily_entries')) {
            Schema::table('daily_entries', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_entries', 'mobile_number')) {
                    $table->string('mobile_number', 20)->nullable()->after('customer_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('daily_entries')) {
            Schema::table('daily_entries', function (Blueprint $table) {
                if (Schema::hasColumn('daily_entries', 'mobile_number')) {
                    $table->dropColumn('mobile_number');
                }
            });
        }
    }
};
