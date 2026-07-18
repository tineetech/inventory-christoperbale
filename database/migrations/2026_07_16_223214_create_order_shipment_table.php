<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_shipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('penjualan')->cascadeOnDelete();
            $table->string('courier', 50)->nullable();
            $table->string('service', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipment');
    }
};
