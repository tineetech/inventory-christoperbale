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
        Schema::create('hpp_riwayat_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hpp_riwayat_id')->constrained('hpp_riwayat')->cascadeOnDelete();
            $table->string('nama_biaya');
            $table->decimal('harga', 12, 2);
            $table->dateTime('tanggal');
            $table->foreignId('created_by')->constrained('pengguna');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hpp_riwayat_detail');
    }
};
