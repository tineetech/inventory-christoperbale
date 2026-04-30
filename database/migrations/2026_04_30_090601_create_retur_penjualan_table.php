<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->onDelete('cascade');
            $table->date('tanggal_retur');
            $table->text('alasan_retur');
            $table->enum('status', ['pending', 'diproses', 'selesai', 'ditolak'])->default('pending');
            $table->string('file_path')->nullable();            // path file tersimpan
            $table->string('file_original_name')->nullable();  // nama asli file upload
            $table->string('file_mime')->nullable();            // mime type (image/video)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('pengguna')->onDelete('set null');
        });

        Schema::create('retur_penjualan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_penjualan_id')->constrained('retur_penjualan')->onDelete('cascade');
            $table->foreignId('penjualan_detail_id')->constrained('penjualan_detail')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->unsignedInteger('qty_retur');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_penjualan_detail');
        Schema::dropIfExists('retur_penjualan');
    }
};