<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bersihkan orphan penjualan_draft_id yang tidak ada di penjualan_draft
        DB::table('penjualan_shipment')
            ->leftJoin('penjualan_draft', 'penjualan_shipment.penjualan_draft_id', '=', 'penjualan_draft.id')
            ->whereNull('penjualan_draft.id')
            ->whereNotNull('penjualan_shipment.penjualan_draft_id')
            ->update(['penjualan_shipment.penjualan_draft_id' => null]);

        DB::table('penjualan_address')
            ->leftJoin('penjualan_draft', 'penjualan_address.penjualan_draft_id', '=', 'penjualan_draft.id')
            ->whereNull('penjualan_draft.id')
            ->whereNotNull('penjualan_address.penjualan_draft_id')
            ->update(['penjualan_address.penjualan_draft_id' => null]);

        DB::table('pembayaran')
            ->leftJoin('penjualan_draft', 'pembayaran.penjualan_draft_id', '=', 'penjualan_draft.id')
            ->whereNull('penjualan_draft.id')
            ->whereNotNull('pembayaran.penjualan_draft_id')
            ->update(['pembayaran.penjualan_draft_id' => null]);

        // penjualan_shipment: penjualan_id -> cascade, penjualan_draft_id -> FK cascade
        Schema::table('penjualan_shipment', function (Blueprint $t) {
            $t->dropForeign(['penjualan_id']);
        });
        Schema::table('penjualan_shipment', function (Blueprint $t) {
            $t->foreign('penjualan_id')->references('id')->on('penjualan')->cascadeOnDelete();
            $t->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->cascadeOnDelete();
        });

        // penjualan_address: penjualan_id -> cascade, penjualan_draft_id -> FK cascade
        Schema::table('penjualan_address', function (Blueprint $t) {
            $t->dropForeign(['penjualan_id']);
        });
        Schema::table('penjualan_address', function (Blueprint $t) {
            $t->foreign('penjualan_id')->references('id')->on('penjualan')->cascadeOnDelete();
            $t->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->cascadeOnDelete();
        });

        // pembayaran: penjualan_draft_id -> FK cascade (penjualan_id sudah cascade)
        Schema::table('pembayaran', function (Blueprint $t) {
            $t->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_shipment', function (Blueprint $t) {
            $t->dropForeign(['penjualan_draft_id']);
            $t->dropForeign(['penjualan_id']);
        });
        Schema::table('penjualan_shipment', function (Blueprint $t) {
            $t->foreign('penjualan_id')->references('id')->on('penjualan')->nullOnDelete();
        });

        Schema::table('penjualan_address', function (Blueprint $t) {
            $t->dropForeign(['penjualan_draft_id']);
            $t->dropForeign(['penjualan_id']);
        });
        Schema::table('penjualan_address', function (Blueprint $t) {
            $t->foreign('penjualan_id')->references('id')->on('penjualan')->nullOnDelete();
        });

        Schema::table('pembayaran', function (Blueprint $t) {
            $t->dropForeign(['penjualan_draft_id']);
        });
    }
};
