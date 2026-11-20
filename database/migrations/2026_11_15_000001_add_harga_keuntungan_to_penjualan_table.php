<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->decimal('harga_keuntungan', 15, 2)->nullable()->default(0);
            $table->boolean('is_harga_keuntungan_manual')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['harga_keuntungan', 'is_harga_keuntungan_manual']);
        });
    }
};
