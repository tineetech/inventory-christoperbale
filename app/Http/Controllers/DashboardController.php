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
use Carbon\Carbon;
use Carbon\CarbonPeriod;


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

        // ── FILTER TANGGAL ─────────────────────────────
        $fromDate = $request->filled('from_date')
            ? $request->from_date
            : now()->startOfYear()->format('Y-m-d');

        $toDate = $request->filled('to_date')
            ? $request->to_date
            : now()->endOfMonth()->format('Y-m-d');

        // Jika from > to → tukar
        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        // ── QUERY DATABASE ─────────────────────────────
        $rawChart = Penjualan::selectRaw('
        YEAR(tanggal) as tahun,
        MONTH(tanggal) as bulan,
        COUNT(*) as total_transaksi,
        SUM(total_harga) as total_omzet
    ')
            ->whereDate('tanggal', '>=', $fromDate)
            ->whereDate('tanggal', '<=', $toDate)
            ->where('is_draft', '!=', 'yes')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        // Mapping data DB supaya gampang dicari
        $mapped = [];

        foreach ($rawChart as $row) {
            $key = $row->tahun . '-' . str_pad($row->bulan, 2, '0', STR_PAD_LEFT);

            $mapped[$key] = [
                'transaksi' => (int) $row->total_transaksi,
                'omzet' => (float) $row->total_omzet,
            ];
        }

        // ── GENERATE SEMUA BULAN DALAM RANGE ───────────
        $bulanLabels = [];
        $dataTransaksi = [];
        $dataOmzet = [];

        $period = CarbonPeriod::create(
            Carbon::parse($fromDate)->startOfMonth(),
            '1 month',
            Carbon::parse($toDate)->startOfMonth()
        );

        foreach ($period as $date) {

            $key = $date->format('Y-m');

            $bulanLabels[] = $date
                ->locale('id')
                ->translatedFormat('M Y');

            $dataTransaksi[] = $mapped[$key]['transaksi'] ?? 0;
            $dataOmzet[] = $mapped[$key]['omzet'] ?? 0;
        }

        // ── SUMMARY ────────────────────────────────────
        $totalTransaksiH1 = array_sum($dataTransaksi);
        $totalOmzetH1 = array_sum($dataOmzet);

        $tahun = now()->year;

        return view('pages.dashboard', compact(
            'totalBarang',
            'totalSupplier',
            'totalDropshipper',
            'fromDate',
            'toDate',
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
