<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rate_cache_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_rate_cache_id');
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rate_cache_items');
    }
};
