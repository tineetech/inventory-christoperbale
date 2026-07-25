<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable()->after('id');
            $table->string('nama_produk', 255)->after('kategori_id');
            $table->string('slug', 255)->unique()->after('nama_produk');
            $table->text('deskripsi')->nullable()->after('slug');
            $table->decimal('harga_normal', 15, 2)->default(0)->after('deskripsi');
            $table->decimal('harga_diskon', 15, 2)->nullable()->after('harga_normal');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('harga_diskon');
        });

        Schema::table('produk_varian', function (Blueprint $table) {
            $table->foreignId('produk_id')->after('id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('barang_id')->after('produk_id')->constrained('barang')->cascadeOnDelete();
            $table->string('warna', 100)->nullable()->after('barang_id');
            $table->string('size', 50)->nullable()->after('warna');
        });

        Schema::table('produk_foto', function (Blueprint $table) {
            $table->foreignId('produk_id')->after('id')->constrained('produk')->cascadeOnDelete();
            $table->string('foto', 255)->after('produk_id');
            $table->unsignedInteger('urutan')->default(0)->after('foto');
            $table->boolean('is_utama')->default(false)->after('urutan');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['kategori_id', 'nama_produk', 'slug', 'deskripsi', 'harga_normal', 'harga_diskon', 'status']);
        });

        Schema::table('produk_varian', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->dropForeign(['barang_id']);
            $table->dropColumn(['produk_id', 'barang_id', 'warna', 'size']);
        });

        Schema::table('produk_foto', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->dropColumn(['produk_id', 'foto', 'urutan', 'is_utama']);
        });
    }
};
