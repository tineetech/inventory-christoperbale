<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pembayaran');

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualan')->cascadeOnDelete();
            $table->unsignedBigInteger('penjualan_draft_id')->nullable();
            $table->string('order_id_midtrans', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 50)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('penjualan')->cascadeOnDelete();
            $table->string('payment_provider', 50)->default('midtrans');
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->string('payment_status', 50)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }
};
