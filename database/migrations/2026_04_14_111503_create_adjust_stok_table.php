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
        Schema::create('adjust_stok', function (Blueprint $table) {
            $table->id();
            $table->string('kode_adjust')->unique();
            $table->dateTime('tanggal');
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
        Schema::dropIfExists('adjust_stok');
    }
};
