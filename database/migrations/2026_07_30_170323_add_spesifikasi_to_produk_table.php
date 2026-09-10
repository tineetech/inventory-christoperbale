<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->integer('berat_gram')->nullable()->after('brand_id');
            $table->integer('panjang_cm')->nullable()->after('berat_gram');
            $table->integer('lebar_cm')->nullable()->after('panjang_cm');
            $table->integer('tinggi_cm')->nullable()->after('lebar_cm');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['berat_gram', 'panjang_cm', 'lebar_cm', 'tinggi_cm']);
        });
    }
};
