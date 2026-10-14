<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan kolom 'foto' pada tabel 'produk_foto' nullable agar bisa diisi kosong/null
        if (Schema::hasTable('produk_foto') && Schema::hasColumn('produk_foto', 'foto')) {
            Schema::table('produk_foto', function (Blueprint $table) {
                $table->string('foto', 255)->nullable()->change();
            });
        }

        // 2. Ambil seluruh data barang
        $barangList = DB::table('barang')->get();

        // 3. Kelompokkan data barang berdasarkan keyword nama depan (case-insensitive)
        $groups = [];
        foreach ($barangList as $item) {
            $rawName = trim($item->nama_barang ?? '');
            if ($rawName === '') {
                continue;
            }

            $words = preg_split('/\s+/', $rawName);
            $keyword = strtolower($words[0] ?? '');
            if ($keyword === '') {
                continue;
            }

            $groups[$keyword][] = [
                'barang' => $item,
                'words'  => $words,
            ];
        }

        // 4. Proses setiap kelompok data barang
        foreach ($groups as $keyword => $items) {
            $namaProduk = ucfirst($keyword) . ' Product';
            $slug = Str::slug($namaProduk);

            // Tentukan harga normal perwakilan (dari harga_2 / harga reseller)
            $hargaNormal = 0;
            foreach ($items as $entry) {
                $b = $entry['barang'];
                if (!empty($b->harga_2) && floatval($b->harga_2) > 0) {
                    $hargaNormal = $b->harga_2;
                    break;
                }
            }
            // Fallback ke harga_1 jika harga_2 belum terisi
            if ($hargaNormal == 0) {
                foreach ($items as $entry) {
                    $b = $entry['barang'];
                    if (!empty($b->harga_1) && floatval($b->harga_1) > 0) {
                        $hargaNormal = $b->harga_1;
                        break;
                    }
                }
            }

            // Cek apakah produk sudah ada (berdasarkan slug atau produk_id yang terikat sebelumnya)
            $existingProdukId = null;
            foreach ($items as $entry) {
                if (!empty($entry['barang']->produk_id)) {
                    $existingProdukId = $entry['barang']->produk_id;
                    break;
                }
            }

            $produk = null;
            if ($existingProdukId) {
                $produk = DB::table('produk')->where('id', $existingProdukId)->first();
            }
            if (!$produk) {
                $produk = DB::table('produk')->where('slug', $slug)->first();
            }

            if ($produk) {
                $produkId = $produk->id;
                DB::table('produk')->where('id', $produkId)->update([
                    'nama_produk'   => $namaProduk,
                    'slug'          => $slug,
                    'brand_id'      => $produk->brand_id ?: 1,
                    'harga_normal'  => $produk->harga_normal ?: $hargaNormal,
                    'status'        => 'nonaktif',
                    'updated_at'    => now(),
                ]);
            } else {
                $produkId = DB::table('produk')->insertGetId([
                    'kategori_id'   => null,
                    'brand_id'      => 1,
                    'berat_gram'    => null,
                    'panjang_cm'    => null,
                    'lebar_cm'      => null,
                    'tinggi_cm'     => null,
                    'nama_produk'   => $namaProduk,
                    'slug'          => $slug,
                    'deskripsi'     => null,
                    'harga_normal'  => $hargaNormal,
                    'harga_diskon'  => null,
                    'status'        => 'nonaktif',
                    'is_popular'    => 0,
                    'is_newproduct' => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 5. Buat atau pastikan 1 data produk_foto mewakili produk ini
            $fotoExists = DB::table('produk_foto')->where('produk_id', $produkId)->exists();
            if (!$fotoExists) {
                DB::table('produk_foto')->insert([
                    'produk_id'  => $produkId,
                    'foto'       => null,
                    'urutan'     => 1,
                    'is_utama'   => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 6. Buat data ke table produk_varian dan update produk_id di table barang
            foreach ($items as $entry) {
                $barang = $entry['barang'];
                $words = $entry['words'];
                $count = count($words);

                $warna = null;
                $size = null;

                if ($count === 1) {
                    $warna = null;
                    $size = null;
                } elseif ($count === 2) {
                    if (is_numeric($words[1])) {
                        $size = $words[1];
                        $warna = null;
                    } else {
                        $warna = $words[1];
                        $size = null;
                    }
                } else {
                    // >= 3 kata
                    $lastWord = end($words);
                    if (is_numeric($lastWord)) {
                        $size = $lastWord;
                        // Ambil kata ke-2 sampai sebelum kata terakhir sebagai warna
                        $warna = implode(' ', array_slice($words, 1, -1));
                    } else {
                        $warna = implode(' ', array_slice($words, 1));
                        $size = null;
                    }
                }

                // Cek varian apakah sudah ada
                $existingVarian = DB::table('produk_varian')
                    ->where('produk_id', $produkId)
                    ->where('barang_id', $barang->id)
                    ->first();

                if (!$existingVarian) {
                    DB::table('produk_varian')->insert([
                        'produk_id'  => $produkId,
                        'barang_id'  => $barang->id,
                        'warna'      => $warna,
                        'size'       => $size,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('produk_varian')
                        ->where('id', $existingVarian->id)
                        ->update([
                            'warna'      => $warna,
                            'size'       => $size,
                            'updated_at' => now(),
                        ]);
                }

                // Update produk_id di table barang
                DB::table('barang')
                    ->where('id', $barang->id)
                    ->update([
                        'produk_id'  => $produkId,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('barang')->update(['produk_id' => null]);
        DB::table('produk_varian')->delete();
        DB::table('produk_foto')->delete();
        DB::table('produk')->delete();
    }
};
