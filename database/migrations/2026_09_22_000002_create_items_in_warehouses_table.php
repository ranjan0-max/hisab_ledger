<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_in_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('item_name', 150);
            $table->decimal('quantity', 15, 3)->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'item_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_in_warehouses');
    }
};
