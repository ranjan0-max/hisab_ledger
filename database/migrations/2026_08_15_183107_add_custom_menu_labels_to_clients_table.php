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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('customer_label')->nullable()->default('Customers')->after('name');
            $table->string('supplier_label')->nullable()->default('Suppliers')->after('customer_label');
            $table->string('daily_label')->nullable()->default('Daily Entries')->after('supplier_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['customer_label', 'supplier_label', 'daily_label']);
        });
    }
};
