<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['penjualan_address', 'penjualan_shipment'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['penjualan_id']);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('penjualan_id')->nullable()->change();
            });

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('penjualan_id')->references('id')->on('penjualan')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['penjualan_address', 'penjualan_shipment'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['penjualan_id']);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('penjualan_id')->nullable(false)->change();
            });

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('penjualan_id')->references('id')->on('penjualan')->cascadeOnDelete();
            });
        }
    }
};
