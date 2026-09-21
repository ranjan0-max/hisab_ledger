<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add session_timeout_minutes to clients table for per-client configurable session timeout.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Default 120 minutes (2 hours). Admins can set per-client value.
            $table->unsignedSmallInteger('session_timeout_minutes')->default(120)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('session_timeout_minutes');
        });
    }
};
