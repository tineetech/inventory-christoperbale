<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\StokBarang;
use App\Models\StokMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Konfirmasi scan out: adjust stok (opsional) lalu kurangi stok
     * setiap barang sesuai qty pada resi. Semua perubahan stok
     * dicatat ke stok_movement.
     *
     * POST /api/penjualan/scan-out-confirm
     * Body: { "nomor_resi": "...", "actor_id": 1, "adjustments": [{ "barang_id": 1, "stok_baru": 10 }] }
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'nomor_resi'                   => 'required|string',
            'actor_id'                     => 'required|integer|exists:pengguna,id',
            'adjustments'                  => 'nullable|array',
            'adjustments.*.barang_id'      => 'required|integer|exists:barang,id',
            'adjustments.*.stok_baru'      => 'required|integer|min:0',
        ]);

        // Actor dikirim eksplisit dari halaman (session guard tidak tersedia di konteks API)
        $actorId = (int) $request->input('actor_id');

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

            $wasDraft  = $penjualan->is_draft;
            $adjustMap = collect($request->input('adjustments', []))->keyBy('barang_id');
            $totalAdjust = 0;

            // 1️⃣ Adjust stok terlebih dahulu (sebelum pengurangan)
            foreach ($penjualan->detail as $detail) {
                if (!$adjustMap->has($detail->barang_id)) {
                    continue;
                }

                $stokBaru = (int) $adjustMap[$detail->barang_id]['stok_baru'];
                $sku      = $detail->barang->sku ?? ('ID ' . $detail->barang_id);

                if ($stokBaru < (int) $detail->qty) {
                    throw new \Exception(
                        "Stok baru untuk SKU {$sku} minimal sama dengan qty dipesan ({$detail->qty})."
                    );
                }

                $stok     = StokBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();
                $sebelum  = $stok->jumlah_stok ?? 0;
                Log::info("AKTOR: " . $actorId);

                if ($sebelum !== $stokBaru) {
                    StokBarang::updateOrCreate(
                        ['barang_id' => $detail->barang_id],
                        ['jumlah_stok' => $stokBaru]
                    );

                    StokMovement::create([
                        'barang_id'      => $detail->barang_id,
                        'jenis'          => $stokBaru > $sebelum ? 'masuk' : 'keluar',
                        'qty'            => abs($stokBaru - $sebelum),
                        'stok_sebelum'   => $sebelum,
                        'stok_sesudah'   => $stokBaru,
                        'referensi_tipe' => 'penjualan_scanout_adjust',
                        'referensi_id'   => $penjualan->id,
                        'keterangan'     => 'Adjust stok sebelum scan out ' . $penjualan->nomor_resi,
                        'created_by'     => $actorId,
                    ]);
                }

                $totalAdjust++;
            }

            // 2️⃣ Pastikan semua stok cukup (defensif, setelah adjust)
            $masihKurang = [];

            foreach ($penjualan->detail as $detail) {
                $stok   = StokBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();
                $sebelum = $stok->jumlah_stok ?? 0;

                if ($sebelum < (int) $detail->qty) {
                    $masihKurang[] = ($detail->barang->sku ?? ('ID ' . $detail->barang_id))
                        . ' (stok: ' . $sebelum . ', butuh: ' . $detail->qty . ')';
                }
            }

            if (!empty($masihKurang)) {
                throw new \Exception('Stok masih kurang untuk: ' . implode(', ', $masihKurang) . '. Lakukan adjust stok terlebih dahulu.');
            }

            // 3️⃣ Kurangi stok setiap barang sesuai qty pada resi
            $totalQty = 0;

            foreach ($penjualan->detail as $detail) {
                $qty    = (int) $detail->qty;
                $stok   = StokBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();
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

            $totalJenis  = $penjualan->detail->count();
            $totalNormal = $totalJenis - $totalAdjust;

            return response()->json([
                'success' => true,
                'message' => "Scan out berhasil untuk resi {$nomorResi}.",
                'summary' => [
                    'nomor_resi'    => $penjualan->nomor_resi,
                    'kode_penjualan'=> $penjualan->kode_penjualan,
                    'total_qty'     => $totalQty,
                    'total_jenis'   => $totalJenis,
                    'total_normal'  => $totalNormal,
                    'total_adjust'  => $totalAdjust,
                    'was_draft'     => $wasDraft === 'yes',
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