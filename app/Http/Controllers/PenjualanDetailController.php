<?php

namespace App\Http\Controllers;

use App\Models\PenjualanDetail;
use App\Models\Penjualan;
use App\Models\Barang;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        $details = PenjualanDetail::with('penjualan', 'barang')->get();
        return view('penjualan_detail.index', compact('details'));
    }

    public function create()
    {
        $penjualan = Penjualan::all();
        $barang    = Barang::all();
        return view('penjualan_detail.create', compact('penjualan', 'barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualan,id',
            'barang_id'    => 'required|exists:barang,id',
            'qty'          => 'required|integer|min:1',
            'harga'        => 'required|numeric|min:0',
            'subtotal'     => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        PenjualanDetail::create([
            'penjualan_id' => $data['penjualan_id'],
            'barang_id'    => $data['barang_id'],
            'qty'          => $data['qty'],
            'harga'        => $data['harga'],
            'subtotal'     => $data['subtotal'],
        ]);

        return redirect()->route('penjualan-detail.index')->with('success', 'Detail penjualan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $detail = PenjualanDetail::with('penjualan', 'barang')->findOrFail($id);
        return view('penjualan_detail.show', compact('detail'));
    }

    public function edit(string $id)
    {
        $detail    = PenjualanDetail::findOrFail($id);
        $penjualan = Penjualan::all();
        $barang    = Barang::all();
        return view('penjualan_detail.edit', compact('detail', 'penjualan', 'barang'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'penjualan_id' => 'required|exists:penjualan,id',
            'barang_id'    => 'required|exists:barang,id',
            'qty'          => 'required|integer|min:1',
            'harga'        => 'required|numeric|min:0',
            'subtotal'     => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        $detail = PenjualanDetail::findOrFail($id);
        $detail->update([
            'penjualan_id' => $data['penjualan_id'],
            'barang_id'    => $data['barang_id'],
            'qty'          => $data['qty'],
            'harga'        => $data['harga'],
            'subtotal'     => $data['subtotal'],
        ]);

        return redirect()->route('penjualan-detail.index')->with('success', 'Detail penjualan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $detail = PenjualanDetail::findOrFail($id);
        $detail->delete();

        return redirect()->route('penjualan-detail.index')->with('success', 'Detail penjualan berhasil dihapus.');
    }
}
