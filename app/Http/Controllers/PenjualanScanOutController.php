<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Pengguna;
use App\Models\Penjualan;
use App\Models\StokBarang;
use App\Models\StokMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PenjualanScanOutController extends Controller
{
    /**
     * Scan nomor resi → cari penjualan → update scan_out = done
     *
     * POST /api/penjualan/scan-out
     * Body: { "nomor_resi": "JNE123456789" }
     */
    public function scanOut(Request $request)
    {
        $request->validate([
            'nomor_resi' => 'required|string',
        ]);

        $nomorResi = trim($request->nomor_resi);

        // Cari penjualan berdasarkan nomor_resi
        $penjualan = Penjualan::where('nomor_resi', $nomorResi)->first();

        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => "Penjualan dengan nomor resi '{$nomorResi}' tidak ditemukan.",
            ], 404);
        }

        // Cegah double scan
        if ($penjualan->scan_out === 'done') {
            return response()->json([
                'success'   => false,
                'message'   => "Resi '{$nomorResi}' sudah pernah di-scan out ({$penjualan->kode_penjualan}).",
                'penjualan' => [
                    'id'             => $penjualan->id,
                    'kode_penjualan' => $penjualan->kode_penjualan,
                    'scan_out'       => $penjualan->scan_out,
                ],
            ], 409);
        }

        // Update scan_out → done
        $penjualan->update(['scan_out' => 'done']);

        return response()->json([
            'success'   => true,
            'message'   => "Scan out berhasil untuk {$penjualan->kode_penjualan} (resi: {$nomorResi}).",
            'penjualan' => [
                'id'             => $penjualan->id,
                'kode_penjualan' => $penjualan->kode_penjualan,
                'nomor_resi'     => $penjualan->nomor_resi,
                'scan_out'       => $penjualan->scan_out,
            ],
        ]);
    }

    /**
     * Ambil detail penjualan berdasarkan nomor resi (untuk popup konfirmasi scan out).
     *
     * GET /api/penjualan/by-resi/{nomorResi}
     */
    public function byResi($nomorResi)
    {
        $nomorResi = trim($nomorResi);

        $penjualan = Penjualan::with(['detail.barang.stok'])
            ->where('nomor_resi', $nomorResi)
            ->first();

        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => "Penjualan dengan nomor resi '{$nomorResi}' tidak ditemukan.",
            ], 404);
        }

        $items = $penjualan->detail->map(function ($d) {
            $stokSaatIni = $d->barang->stok->jumlah_stok ?? 0;

            return [
                'barang_id'     => $d->barang_id,
                'sku'           => $d->barang->sku ?? '-',
                'nama_barang'   => $d->barang->nama_barang ?? '-',
                'qty'           => (int) $d->qty,
                'stok_saat_ini' => (int) $stokSaatIni,
                'kurang'        => $stokSaatIni < (int) $d->qty,
            ];
        })->values();

        return response()->json([
            'success'   => true,
            'penjualan' => [
                'id'             => $penjualan->id,
                'kode_penjualan' => $penjualan->kode_penjualan,
                'nomor_resi'     => $penjualan->nomor_resi,
                'is_draft'       => $penjualan->is_draft,
                'scan_out'       => $penjualan->scan_out,
                'total_qty'      => $items->sum('qty'),
                'total_jenis'    => $items->count(),
                'items'          => $items,
            ],
        ]);
    }

    /**
     * Scan out otomatis: pastikan stok cukup untuk setiap barang.
     * Bila stok kurang, langsung tambah stok sebesar qty dipesan,
     * buat pembelian otomatis (semua barang kurang pada 1 resi),
     * catat stok movement, dan kirim notifikasi.
     * Setelah stok cukup, kurangi stok sesuai qty lalu tandai
     * scan_out = done dan is_draft = no.
     *
     * POST /api/penjualan/scan-out-confirm
     * Body: { "nomor_resi": "...", "actor_id": 1 }
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'nomor_resi' => 'required|string',
            'actor_id'   => 'required|integer|exists:pengguna,id',
        ]);

        // Actor dikirim eksplisit dari halaman (session guard tidak tersedia di konteks API)
        $actorId = (int) $request->input('actor_id');
        $actor   = Pengguna::find($actorId);

        if (!$actor) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 422);
        }

        $namaAktor = $actor->nama ?? $actor->full_name ?? 'Pengguna';
        $nomorResi = trim($request->nomor_resi);

        DB::beginTransaction();

        try {
            $penjualan = Penjualan::with(['detail.barang'])
                ->where('nomor_resi', $nomorResi)
                ->lockForUpdate()
                ->first();

            if (!$penjualan) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "Penjualan dengan nomor resi '{$nomorResi}' tidak ditemukan.",
                ], 404);
            }

            if ($penjualan->scan_out === 'done') {
                DB::rollBack();

                return response()->json([
                    'success'   => false,
                    'already'   => true,
                    'message'   => "Resi '{$nomorResi}' sudah pernah di-scan out ({$penjualan->kode_penjualan}).",
                    'penjualan' => [
                        'id'             => $penjualan->id,
                        'kode_penjualan' => $penjualan->kode_penjualan,
                        'scan_out'       => $penjualan->scan_out,
                    ],
                ], 409);
            }

            $wasDraft = $penjualan->is_draft;

            // 1️⃣ Identifikasi barang yang stoknya kurang dari qty dipesan
            $kurangBarang = [];

            foreach ($penjualan->detail as $detail) {
                $stok         = StokBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();
                $stokSaatIni  = $stok->jumlah_stok ?? 0;

                if ($stokSaatIni < (int) $detail->qty) {
                    $kurangBarang[$detail->barang_id] = (int) $detail->qty;
                }
            }

            // 2️⃣ Bila ada yang kurang: tambah stok sebesar qty dipesan + buat pembelian otomatis + notifikasi
            $topUpBerhasil = 0;

            if (!empty($kurangBarang)) {
                $supplier = Supplier::first();

                if (!$supplier) {
                    throw new \Exception('Tidak ada supplier terdaftar untuk pembelian otomatis scan out. Hubungi admin untuk menambahkan supplier.');
                }

                $now          = now();
                $kodePembelian = 'PB-SCANOUT-' . $now->format('Ymd') . '-' . strtoupper(Str::random(6));

                // Buat header pembelian otomatis (supplier pertama)
                $totalHarga = 0;

                foreach ($kurangBarang as $barangId => $qty) {
                    $barang  = \App\Models\Barang::find($barangId);
                    $harga   = $barang->harga_1 ?? 0;
                    $totalHarga += $qty * $harga;
                }

                $pembelian = Pembelian::create([
                    'kode_pembelian' => $kodePembelian,
                    'supplier_id'    => $supplier->id,
                    'tanggal'        => $now,
                    'total_harga'    => $totalHarga,
                    'keterangan'     => 'Pembelian otomatis scan out resi ' . $penjualan->nomor_resi,
                    'created_by'     => $actorId,
                ]);

                foreach ($kurangBarang as $barangId => $qty) {
                    $barang  = \App\Models\Barang::find($barangId);
                    $harga   = $barang->harga_1 ?? 0;
                    $subtotal = $qty * $harga;

                    // Pembelian detail
                    PembelianDetail::create([
                        'pembelian_id' => $pembelian->id,
                        'barang_id'    => $barangId,
                        'qty'          => $qty,
                        'harga'        => $harga,
                        'subtotal'     => $subtotal,
                    ]);

                    // Top-up stok penuh sesuai qty dipesan
                    $stok        = StokBarang::where('barang_id', $barangId)->lockForUpdate()->first();
                    $stokSebelum = $stok->jumlah_stok ?? 0;
                    $stokSesudah = $stokSebelum + $qty;

                    StokBarang::updateOrCreate(
                        ['barang_id' => $barangId],
                        ['jumlah_stok' => $stokSesudah]
                    );

                    StokMovement::create([
                        'barang_id'       => $barangId,
                        'jenis'           => 'masuk',
                        'qty'             => $qty,
                        'stok_sebelum'    => $stokSebelum,
                        'stok_sesudah'    => $stokSesudah,
                        'referensi_tipe'  => 'pembelian',
                        'referensi_id'    => $pembelian->id,
                        'keterangan'      => 'Pembelian otomatis scan out ' . $penjualan->nomor_resi,
                        'created_by'      => $actorId,
                    ]);

                    $topUpBerhasil++;
                }

                $skuList = collect($kurangBarang)->keys()->map(function ($barangId) {
                    $b = \App\Models\Barang::find($barangId);
                    return $b->sku ?? ('ID ' . $barangId);
                })->implode(', ');

                Notifikasi::create([
                    'judul'      => 'Penambahan Stok Otomatis Scan Out',
                    'isi'        => $namaAktor . ' menambahkan stok otomatis untuk ' . $topUpBerhasil . ' barang (' . $skuList . ') karena stok kurang pada resi ' . $penjualan->nomor_resi . ' pada ' . $now->format('d/m/Y') . ' pukul ' . $now->format('H:i') . ' via pembelian ' . $kodePembelian . '.',
                    'tipe'       => 'pembelian_cepat',
                    'link'       => route('pembelian.index'),
                    'payload'    => [
                        'pembelian_id'   => $pembelian->id,
                        'kode_pembelian' => $kodePembelian,
                        'total_qty'      => array_sum($kurangBarang),
                        'scan_out_resi'  => $penjualan->nomor_resi,
                        'tanggal'        => $now,
                        'dibuat_oleh'    => $namaAktor,
                    ],
                    'user_id'    => null,
                    'created_by' => $actorId,
                ]);
            }

            // 3️⃣ Kurangi stok setiap barang sesuai qty pada resi
            $totalQty = 0;

            foreach ($penjualan->detail as $detail) {
                $qty     = (int) $detail->qty;
                $stok    = StokBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();
                $sebelum = $stok->jumlah_stok ?? 0;
                $sesudah = $sebelum - $qty;

                StokBarang::updateOrCreate(
                    ['barang_id' => $detail->barang_id],
                    ['jumlah_stok' => $sesudah]
                );

                StokMovement::create([
                    'barang_id'      => $detail->barang_id,
                    'jenis'          => 'keluar',
                    'qty'            => $qty,
                    'stok_sebelum'   => $sebelum,
                    'stok_sesudah'   => $sesudah,
                    'referensi_tipe' => 'penjualan_scanout',
                    'referensi_id'   => $penjualan->id,
                    'keterangan'     => 'Scan out ' . $penjualan->nomor_resi . ' (' . $penjualan->kode_penjualan . ')',
                    'created_by'     => $actorId,
                ]);

                $totalQty += $qty;
            }

            // 4️⃣ Tandai scan out selesai + lepas status draft bila masih draft
            $penjualan->update([
                'scan_out' => 'done',
                'is_draft' => 'no',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Scan out berhasil untuk resi {$nomorResi}.",
                'summary' => [
                    'nomor_resi'     => $penjualan->nomor_resi,
                    'kode_penjualan' => $penjualan->kode_penjualan,
                    'total_qty'      => $totalQty,
                    'total_jenis'    => $penjualan->detail->count(),
                    'top_up_items'   => $topUpBerhasil,
                    'was_draft'      => $wasDraft === 'yes',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ambil semua data penjualan terbaru (untuk auto-refresh tabel)
     *
     * GET /api/penjualan/list
     */
    public function list()
    {
        $penjualan = Penjualan::with(['dropshipper', 'detail.barang.stok'])
            // ->orderByDesc('created_at')
            // ->whereDate('tanggal', today())
            // ->orderByDesc('id')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($pj) {
                return [
                    'id'              => $pj->id,
                    'kode_penjualan'  => $pj->kode_penjualan,
                    'nomor_resi'      => $pj->nomor_resi,
                    'nomor_pesanan'   => $pj->nomor_pesanan,
                    'nomor_transaksi' => $pj->nomor_transaksi,
                    'dropshipper'     => $pj->dropshipper->nama ?? '-',
                    'tanggal'         => $pj->tanggal,
                    'tanggal_fmt'     => $pj->tanggal,
                    'total_harga'     => $pj->total_harga,
                    'total_harga_fmt' => 'Rp ' . number_format($pj->total_harga, 0, ',', '.'),
                    'scan_out'        => $pj->scan_out ?? 'nothing',
                    'is_draft'        => $pj->is_draft,
                    'is_retur'        => $pj->is_retur,
                    'strukprint_status'        => $pj->strukprint_status,
                    'keterangan'      => $pj->keterangan,
                    'detail'          => $pj->detail->map(fn($d) => [
                        'nomor_resi'   => $d->nomor_resi,
                        'sku'          => $d->barang->sku ?? '-',
                        'nama_barang'  => $d->barang->nama_barang ?? '-',
                        'stok'         => $d->barang->stok->jumlah_stok ?? 0,
                        'qty'          => $d->qty,
                        'harga'        => $d->harga,
                        'harga_fmt'    => 'Rp ' . number_format($d->harga, 0, ',', '.'),
                        'subtotal'     => $d->subtotal,
                        'subtotal_fmt' => 'Rp ' . number_format($d->subtotal, 0, ',', '.'),
                    ]),
                ];
            });

        return response()->json([
            'success'   => true,
            'penjualan' => $penjualan,
        ]);
    }
}