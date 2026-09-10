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
        Schema::create('hpp_riwayat', function (Blueprint $table) {
            $table->id();
            $table->decimal('hpp_lama', 12, 2);
            $table->decimal('hpp_baru', 12, 2);
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
        Schema::dropIfExists('hpp_riwayat');
    }
};
