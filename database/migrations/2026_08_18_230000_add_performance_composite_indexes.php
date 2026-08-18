<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add composite indexes for multi-tenant query optimization.
     */
    public function up(): void
    {
        // 1. ledger_transactions table indexes
        if (Schema::hasTable('ledger_transactions')) {
            Schema::table('ledger_transactions', function (Blueprint $table) {
                $table->index(['client_id', 'contact_id', 'status'], 'idx_ledger_tx_client_contact_status');
                $table->index(['client_id', 'transaction_date'], 'idx_ledger_tx_client_date');
            });
        }

        // 2. daily_entries table indexes
        if (Schema::hasTable('daily_entries')) {
            Schema::table('daily_entries', function (Blueprint $table) {
                $table->index(['client_id', 'status', 'entry_date'], 'idx_daily_entries_client_status_date');
            });
        }

        // 3. contacts table indexes
        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index(['client_id', 'type', 'is_active'], 'idx_contacts_client_type_active');
                $table->index(['client_id', 'khata_number'], 'idx_contacts_client_khata');
            });
        }

        // 4. audit_logs table indexes
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['client_id', 'created_at'], 'idx_audit_logs_client_created');
            });
        }

        // 5. slow_query_logs table indexes
        if (Schema::hasTable('slow_query_logs')) {
            Schema::table('slow_query_logs', function (Blueprint $table) {
                $table->index(['client_id', 'executed_at'], 'idx_slow_query_client_executed');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('slow_query_logs')) {
            Schema::table('slow_query_logs', function (Blueprint $table) {
                $table->dropIndex('idx_slow_query_client_executed');
            });
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_audit_logs_client_created');
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropIndex('idx_contacts_client_khata');
                $table->dropIndex('idx_contacts_client_type_active');
            });
        }

        if (Schema::hasTable('daily_entries')) {
            Schema::table('daily_entries', function (Blueprint $table) {
                $table->dropIndex('idx_daily_entries_client_status_date');
            });
        }

        if (Schema::hasTable('ledger_transactions')) {
            Schema::table('ledger_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_ledger_tx_client_date');
                $table->dropIndex('idx_ledger_tx_client_contact_status');
            });
        }
    }
};
