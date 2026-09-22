<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('name', 150);
            $table->timestamps();

            $table->unique(['client_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
