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
        Schema::table('adjust_stok_detail', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });

        Schema::table('adjust_stok_detail', function (Blueprint $table) {
            $table->foreign('barang_id')
                ->references('id')
                ->on('barang')
                ->onDelete('cascade');
        });

        Schema::table('stok_barang', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });

        Schema::table('stok_barang', function (Blueprint $table) {
            $table->foreign('barang_id')
                ->references('id')
                ->on('barang')
                ->onDelete('cascade');
        });

        Schema::table('stok_movement', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });

        Schema::table('stok_movement', function (Blueprint $table) {
            $table->foreign('barang_id')
                ->references('id')
                ->on('barang')
                ->onDelete('cascade');
        });
        Schema::table('pembelian_detail', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });

        Schema::table('pembelian_detail', function (Blueprint $table) {
            $table->foreign('barang_id')
                ->references('id')
                ->on('barang')
                ->onDelete('cascade');
        });
        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });

        Schema::table('penjualan_detail', function (Blueprint $table) {
            $table->foreign('barang_id')
                ->references('id')
                ->on('barang')
                ->onDelete('cascade');
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
