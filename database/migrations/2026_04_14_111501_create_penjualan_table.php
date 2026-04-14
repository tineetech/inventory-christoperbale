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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan')->unique();
            $table->string('nomor_resi')->nullable();
            $table->string('nomor_pesanan')->nullable();
            $table->string('nomor_transaksi')->nullable();
            $table->foreignId('dropshipper_id')->nullable()->constrained('dropshipper');
            $table->dateTime('tanggal');
            $table->decimal('total_harga',14,2);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('pengguna');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
