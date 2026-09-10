<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rate_caches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('pengguna')->cascadeOnDelete();
            $table->unsignedBigInteger('origin_area_id')->nullable();
            $table->unsignedBigInteger('destination_area_id')->nullable();
            $table->string('origin_postal_code', 20)->nullable();
            $table->string('destination_postal_code', 20)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rate_caches');
    }
};
