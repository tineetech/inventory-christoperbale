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
        Schema::table('shipping_rate_caches', function (Blueprint $table) {
            $table->string('origin_area_id', 100)->change();
            $table->string('destination_area_id', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_rate_caches', function (Blueprint $table) {
            $table->integer('origin_area_id')->change();
            $table->integer('destination_area_id')->change();
        });
    }
};
