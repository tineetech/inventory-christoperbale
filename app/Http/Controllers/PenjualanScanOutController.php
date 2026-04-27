<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use Illuminate\Http\Request;

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
     * Ambil semua data penjualan terbaru (untuk auto-refresh tabel)
     *
     * GET /api/penjualan/list
     */
    public function list()
    {
        $penjualan = Penjualan::with(['dropshipper', 'detail.barang.stok'])
            // ->orderByDesc('tanggal')
            ->whereDate('tanggal', today())
            ->orderByDesc('id')
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
                    'tanggal_fmt'     => \Carbon\Carbon::parse($pj->tanggal)->format('d M Y'),
                    'total_harga'     => $pj->total_harga,
                    'total_harga_fmt' => 'Rp ' . number_format($pj->total_harga, 0, ',', '.'),
                    'scan_out'        => $pj->scan_out ?? 'nothing',
                    'is_draft'        => $pj->is_draft,
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