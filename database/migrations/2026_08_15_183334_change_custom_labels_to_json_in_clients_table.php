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
            $table->dropColumn(['customer_label', 'supplier_label', 'daily_label']);
            $table->json('menu_labels')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('menu_labels');
            $table->string('customer_label')->nullable();
            $table->string('supplier_label')->nullable();
            $table->string('daily_label')->nullable();
        });
    }
};
