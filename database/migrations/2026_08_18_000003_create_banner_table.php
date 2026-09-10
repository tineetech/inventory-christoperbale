<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('gambar');
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pengguna')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner');
    }
};