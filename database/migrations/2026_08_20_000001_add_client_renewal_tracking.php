<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->date('next_renewal_date')
                ->nullable()
                ->after('session_timeout_minutes')
                ->index();
        });

        Schema::create('client_renewal_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('renewal_due_date');
            $table->timestamp('paid_at');
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'renewal_due_date'], 'uq_client_renewal_due_date');
            $table->index(['client_id', 'paid_at'], 'idx_client_renewal_paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_renewal_payments');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['next_renewal_date']);
            $table->dropColumn('next_renewal_date');
        });
    }
};
