<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus table lama
        Schema::dropIfExists('order_address');
        Schema::dropIfExists('order_shipment');

        // Penjualan Address
        Schema::create('penjualan_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->unsignedBigInteger('penjualan_draft_id')->nullable();
            $table->string('recipient_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('label', 50)->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->timestamps();
        });

        // Penjualan Shipment
        Schema::create('penjualan_shipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->constrained('penjualan')->cascadeOnDelete();
            $table->unsignedBigInteger('penjualan_draft_id')->nullable();
            $table->string('courier', 50)->nullable();
            $table->string('service', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        // Penjualan Draft
        Schema::create('penjualan_draft', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan', 50);
            $table->date('tanggal');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->decimal('harga_discount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('subtotal_harga', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('order_web')->default(1);
            $table->enum('status', ['waiting_payment', 'proses'])->default('waiting_payment');
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();
        });

        // Penjualan Draft Items
        Schema::create('penjualan_draft_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_draft_id')->constrained('penjualan_draft')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_draft_items');
        Schema::dropIfExists('penjualan_draft');
        Schema::dropIfExists('penjualan_shipment');
        Schema::dropIfExists('penjualan_address');

        Schema::create('order_shipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('penjualan')->cascadeOnDelete();
            $table->string('courier', 50)->nullable();
            $table->string('service', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('penjualan')->cascadeOnDelete();
            $table->string('recipient_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('label', 50)->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->timestamps();
        });
    }
};
