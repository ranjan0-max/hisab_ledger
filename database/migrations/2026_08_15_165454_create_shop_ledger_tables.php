<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for the existing shop_ledger database schema.
     */
    public function up(): void
    {
        // 1. clients
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->text('address')->nullable();
                $table->string('mobile_number', 20)->nullable();
                $table->string('gst_number', 30)->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. roles
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80)->unique();
                $table->string('description', 255)->nullable();
                $table->boolean('is_system_role')->default(false);
                $table->boolean('is_active')->default(true);
                $table->json('permission_keys')->nullable();
                $table->timestamps();
            });
        }

        // 3. permissions
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->string('name', 120);
                $table->string('module', 60);
            });
        }

        // 4. role_permissions
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
                $table->primary(['role_id', 'permission_id']);
            });
        }

        // 5. users
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username', 80)->unique();
                $table->text('address')->nullable();
                $table->string('password_hash', 255);
                $table->foreignId('role_id')->constrained('roles');
                $table->foreignId('client_id')->nullable()->constrained('clients');
                $table->boolean('is_active')->default(true);
                $table->rememberToken();
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. contacts
        if (!Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients');
                $table->enum('type', ['REGULAR_CUSTOMER', 'SUPPLIER']);
                $table->string('name', 150);
                $table->string('khata_number', 50);
                $table->text('address')->nullable();
                $table->string('gst_number', 30)->nullable();
                $table->text('notes')->nullable();
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->enum('opening_balance_type', ['DUE', 'ADVANCE'])->default('DUE');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 7. contact_phone_numbers
        if (!Schema::hasTable('contact_phone_numbers')) {
            Schema::create('contact_phone_numbers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
                $table->foreignId('client_id')->constrained('clients');
                $table->enum('contact_type', ['REGULAR_CUSTOMER', 'SUPPLIER']);
                $table->string('phone_number', 20);
                $table->boolean('is_primary')->default(false);
            });
        }

        // 8. ledger_transactions
        if (!Schema::hasTable('ledger_transactions')) {
            Schema::create('ledger_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients');
                $table->foreignId('contact_id')->constrained('contacts');
                $table->enum('transaction_type', ['SALE', 'CUSTOMER_PAYMENT', 'PURCHASE', 'SUPPLIER_PAYMENT', 'ADJUSTMENT']);
                $table->decimal('amount', 15, 2);
                $table->date('transaction_date');
                $table->text('description');
                $table->string('payment_mode', 40)->nullable();
                $table->enum('status', ['POSTED', 'VOID'])->default('POSTED');
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->constrained('users');
                $table->timestamp('voided_at')->nullable();
                $table->unsignedBigInteger('voided_by')->nullable();
                $table->timestamps();
            });
        }

        // 9. daily_entries
        if (!Schema::hasTable('daily_entries')) {
            Schema::create('daily_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients');
                $table->string('customer_name', 150);
                $table->text('description');
                $table->decimal('total_amount', 15, 2);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2)->default(0);
                $table->string('payment_mode', 40)->nullable();
                $table->date('entry_date');
                $table->enum('payment_status', ['UNPAID', 'PARTIAL', 'PAID', 'ADVANCE']);
                $table->enum('status', ['POSTED', 'VOID'])->default('POSTED');
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->constrained('users');
                $table->timestamps();
            });
        }

        // 10. audit_logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action', 60);
                $table->string('entity_type', 60);
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->timestamps();
            });
        }

        // 11. system_settings
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->unique()->constrained('clients');
                $table->string('currency', 10)->default('INR');
                $table->string('timezone', 50)->default('Asia/Kolkata');
                $table->timestamps();
            });
        }

        // 12. slow_query_settings
        if (!Schema::hasTable('slow_query_settings')) {
            Schema::create('slow_query_settings', function (Blueprint $table) {
                $table->tinyIncrements('id');
                $table->unsignedInteger('threshold_ms')->default(500);
                $table->unsignedBigInteger('updated_by')->nullable();
            });
        }

        // 13. slow_query_logs
        if (!Schema::hasTable('slow_query_logs')) {
            Schema::create('slow_query_logs', function (Blueprint $table) {
                $table->id();
                $table->mediumText('query_text');
                $table->string('query_type', 20)->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->unsignedInteger('threshold_ms')->nullable();
                $table->string('endpoint', 500)->nullable();
                $table->string('http_method', 10)->nullable();
                $table->string('source_file', 700)->nullable();
                $table->string('database_name', 128)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->timestamp('executed_at')->nullable();
            });
        }

        // 14. google_drive_backup_settings
        if (!Schema::hasTable('google_drive_backup_settings')) {
            Schema::create('google_drive_backup_settings', function (Blueprint $table) {
                $table->tinyIncrements('id');
                $table->text('refresh_token_ciphertext')->nullable();
                $table->string('refresh_token_iv', 64)->nullable();
                $table->string('refresh_token_auth_tag', 64)->nullable();
                $table->string('drive_email', 255)->nullable();
                $table->string('folder_id', 255)->nullable();
                $table->string('folder_name', 255)->default('Hisab Ledger Backups');
                $table->timestamp('connected_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->boolean('automatic_backup_enabled')->default(true);
                $table->string('backup_time', 5)->default('02:00');
                $table->string('backup_timezone', 64)->default('Asia/Kolkata');
                $table->timestamps();
            });
        }

        // 15. database_backup_logs
        if (!Schema::hasTable('database_backup_logs')) {
            Schema::create('database_backup_logs', function (Blueprint $table) {
                $table->id();
                $table->enum('status', ['RUNNING', 'SUCCESS', 'FAILED']);
                $table->string('file_name', 255);
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('drive_file_id', 255)->nullable();
                $table->text('drive_web_view_link')->nullable();
                $table->text('local_file_path')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->unsignedBigInteger('triggered_by')->nullable();
                $table->enum('trigger_type', ['MANUAL', 'SCHEDULED'])->default('MANUAL');
                $table->timestamp('scheduled_for')->nullable()->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backup_logs');
        Schema::dropIfExists('google_drive_backup_settings');
        Schema::dropIfExists('slow_query_logs');
        Schema::dropIfExists('slow_query_settings');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('daily_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('contact_phone_numbers');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('users');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('clients');
    }
};
