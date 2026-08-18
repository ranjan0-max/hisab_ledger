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
        if (!Schema::hasTable('daily_entry_payments')) {
            Schema::create('daily_entry_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daily_entry_id')->constrained('daily_entries')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('payment_mode', 40)->default('CASH');
                $table->string('notes', 255)->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_entry_payments');
    }
};
