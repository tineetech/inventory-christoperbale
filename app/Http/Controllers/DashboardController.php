<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Dropshipper;
use App\Models\StokBarang;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\StokMovement;
use App\Models\AdjustStok;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('pengguna')->check()) {
            return redirect('/login');
        }

        $totalBarang      = Barang::count();
        $totalSupplier    = Supplier::count();
        $totalDropshipper = Dropshipper::count();
        $totalStok        = StokBarang::sum('jumlah_stok');

        $penjualanHariIni    = Penjualan::whereDate('created_at', today())->count();
        $pembelianHariIni    = Pembelian::whereDate('created_at', today())->count();
        $adjustHariIni       = AdjustStok::whereDate('created_at', today())->count();
        $stokMovementHariIni = StokMovement::whereDate('created_at', today())->count();

        $perPageKritis = in_array($request->get('kritis_per_page'), [10, 25, 50, 100])
            ? $request->get('kritis_per_page')
            : 10;

        $stokKritis = Barang::with(['stok', 'satuan'])
            ->whereHas('stok', function ($q) {
                $q->where(function ($inner) {
                    $inner->whereColumn('jumlah_stok', '<=', 'barang.stok_minimum')
                        ->orWhere('jumlah_stok', '<', 10);
                });
            })
            ->paginate($perPageKritis, ['*'], 'kritis_page')
            ->withQueryString();

        $penjualanDraft = Penjualan::with(['dropshipper', 'user'])
        ->where('is_draft', 'yes')
        ->orderBy('updated_at', 'desc')
        ->paginate(10, ['*'], 'draft_page')
        ->withQueryString();

        // ── STATISTIK PENJUALAN: Januari s/d Juni tahun berjalan ──
        $tahun        = now()->year;
        $bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

        // Query jumlah transaksi per bulan (Jan–Jun)
        $rawTransaksi = Penjualan::selectRaw('MONTH(tanggal) as bulan, COUNT(*) as total')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', 6)
            ->where('is_draft', '!=', 'yes')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        // Query total omzet per bulan (Jan–Jun)
        $rawOmzet = Penjualan::selectRaw('MONTH(tanggal) as bulan, SUM(total_harga) as omzet')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', '<=', 6)
            ->where('is_draft', '!=', 'yes')
            ->groupBy('bulan')
            ->pluck('omzet', 'bulan');

        // Bangun array lengkap bulan 1–6 (isi 0 jika tidak ada data)
        $dataTransaksi = [];
        $dataOmzet     = [];

        for ($m = 1; $m <= 6; $m++) {
            $dataTransaksi[] = (int) ($rawTransaksi[$m] ?? 0);
            $dataOmzet[]     = (float) ($rawOmzet[$m] ?? 0);
        }

        // Summary card tambahan
        $totalTransaksiH1 = array_sum($dataTransaksi);
        $totalOmzetH1     = array_sum($dataOmzet);

        return view('pages.dashboard', compact(
            'totalBarang',
            'totalSupplier',
            'totalDropshipper',
            'totalStok',
            'penjualanHariIni',
            'pembelianHariIni',
            'adjustHariIni',
            'stokKritis',
            'stokMovementHariIni',
            // statistik chart
            'bulanLabels',
            'dataTransaksi',
            'penjualanDraft',
            'dataOmzet',
            'totalTransaksiH1',
            'totalOmzetH1',
            'tahun'
        ));
    }
}