<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->enum('status', ['proses', 'packing', 'dikirim', 'selesai'])->default('selesai')->after('order_web');
            $table->decimal('harga_discount', 15, 2)->nullable()->after('total_harga');
            $table->decimal('shipping_cost', 15, 2)->nullable()->after('harga_discount');
            $table->decimal('service_fee', 15, 2)->default(0)->after('shipping_cost');
            $table->decimal('subtotal_harga', 15, 2)->default(0)->after('service_fee');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropColumn(['status', 'harga_discount', 'shipping_cost', 'service_fee', 'subtotal_harga']);
        });
    }
};
