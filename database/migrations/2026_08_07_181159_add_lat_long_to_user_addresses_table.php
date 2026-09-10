<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('latitude', 20)->nullable()->after('address');
            $table->string('longitude', 20)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};