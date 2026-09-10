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
        Schema::table('hpp_riwayat', function (Blueprint $table) {
            $table->foreignId('barang_id')->nullable()->after('id')->constrained('barang')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hpp_riwayat', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barang_id');
        });
    }
};
