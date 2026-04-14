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
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('nama_barang');
            $table->foreignId('satuan_id')->constrained('satuan');
            $table->decimal('harga_1',12,2)->nullable();
            $table->decimal('harga_2',12,2)->nullable();
            $table->integer('stok_minimum')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
