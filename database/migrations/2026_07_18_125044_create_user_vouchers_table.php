<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('pengguna')->cascadeOnDelete();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->enum('status', ['unused', 'used', 'expired', 'cancelled'])->default('unused');
            $table->dateTime('claimed_at')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('penjualan')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
    }
};
