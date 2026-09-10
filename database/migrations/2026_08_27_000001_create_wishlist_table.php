<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};