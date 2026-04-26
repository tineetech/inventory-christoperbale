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
        Schema::create('stok_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->date('dari_tanggal');
            $table->date('sampai_tanggal');
            $table->integer('stok_saat_ini')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->string('status')->default('pending');
            $table->foreignId('input_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['barang_id', 'dari_tanggal', 'sampai_tanggal'], 'stok_report_unique_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_report');
    }
};
