<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->dropForeign(['penjualan_id']);
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->foreign('penjualan_id')->references('id')->on('penjualan')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->dropForeign(['penjualan_id']);
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->foreign('penjualan_id')->references('id')->on('penjualan')->restrictOnDelete();
        });
    }
};
