<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['penjualan_id']);
            $table->dropForeign(['penjualan_draft_id']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->foreign('penjualan_id')->references('id')->on('penjualan')->nullOnDelete();
            $table->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['penjualan_id']);
            $table->dropForeign(['penjualan_draft_id']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->foreign('penjualan_id')->references('id')->on('penjualan')->cascadeOnDelete();
            $table->foreign('penjualan_draft_id')->references('id')->on('penjualan_draft')->cascadeOnDelete();
        });
    }
};
