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
        Schema::create('adjust_stok_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjust_stok_id')->constrained('adjust_stok');
            $table->foreignId('barang_id')->constrained('barang');
            $table->integer('qty_sistem');
            $table->integer('qty_fisik');
            $table->integer('selisih');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjust_stok_detail');
    }
};
