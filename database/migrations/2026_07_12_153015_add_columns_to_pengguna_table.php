<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('nama');
            $table->string('phone')->nullable()->after('email');
            $table->string('photo_profile')->nullable()->after('phone');
            $table->string('gender')->nullable()->after('photo_profile');
        });
    }

    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'phone', 'photo_profile', 'gender']);
        });
    }
};
