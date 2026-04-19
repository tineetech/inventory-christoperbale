<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Penjualan;

class ReportController extends Controller
{
    public function pembelian()
    {
        $pembelian = Pembelian::with('supplier', 'user')->latest()->get();

        return view('pages.laporan.pembelian', compact('pembelian'));
    }

    public function penjualan()
    {
        $penjualan = Penjualan::with('dropshipper', 'user')->latest()->get();

        return view('pages.laporan.penjualan', compact('penjualan'));
    }

    public function stok()
    {
        $barang = Barang::with('satuan', 'stok')->orderBy('nama_barang')->get();

        return view('pages.laporan.stok', compact('barang'));
    }

    public function barang()
    {
        $barang = Barang::with('satuan', 'stok')->latest()->get();

        return view('pages.laporan.barang', compact('barang'));
    }
}
