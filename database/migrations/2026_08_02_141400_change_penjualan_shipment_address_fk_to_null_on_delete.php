<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['penjualan_shipment', 'penjualan_address'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['penjualan_id']);
                $t->dropForeign(['penjualan_draft_id']);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('penjualan_id')->references('id')->on('penjualan')->nullOnDelete();
                $t->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['penjualan_shipment', 'penjualan_address'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['penjualan_id']);
                $t->dropForeign(['penjualan_draft_id']);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('penjualan_id')->references('id')->on('penjualan')->cascadeOnDelete();
                $t->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->cascadeOnDelete();
            });
        }
    }
};
