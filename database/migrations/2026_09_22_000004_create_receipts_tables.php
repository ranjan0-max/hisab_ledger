<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('customer_name', 150);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['client_id', 'created_at']);
        });

        Schema::create('receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('receipts')->cascadeOnDelete();
            $table->foreignId('warehouse_item_id')->constrained('items_in_warehouses');
            $table->decimal('quantity', 15, 3);
            $table->timestamps();

            $table->unique(['receipt_id', 'warehouse_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
        Schema::dropIfExists('receipts');
    }
};
