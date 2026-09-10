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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->string('tipe')->nullable();          // penjualan_web, pembayaran, dll
            $table->string('link')->nullable();          // url tujuan saat diklik
            $table->json('payload')->nullable();         // data tambahan (mis. id penjualan/pembayaran)
            $table->foreignId('user_id')->nullable()->constrained('pengguna')->nullOnDelete(); // null = untuk semua user
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};