<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function index()
    {
        if (!Auth::guard('pengguna')->check()) {
            return redirect('/login');
        }

        $totalBarang = Barang::count();

        $totalSupplier = Supplier::count();

        $totalDropshipper = Dropshipper::count();

        $totalStok = StokBarang::sum('jumlah_stok');

        $penjualanHariIni = Penjualan::whereDate('created_at', today())->count();

        $pembelianHariIni = Pembelian::whereDate('created_at', today())->count();

        $adjustHariIni = AdjustStok::whereDate('created_at', today())->count();

        $stokMovementHariIni = StokMovement::whereDate('created_at', today())->count();

        return view('pages.dashboard', compact(
            'totalBarang',
            'totalSupplier',
            'totalDropshipper',
            'totalStok',
            'penjualanHariIni',
            'pembelianHariIni',
            'adjustHariIni',
            'stokMovementHariIni'
        ));
    }
}