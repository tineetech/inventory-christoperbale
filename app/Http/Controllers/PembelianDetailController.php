<?php

namespace App\Http\Controllers;

use App\Models\PembelianDetail;
use App\Models\Pembelian;
use App\Models\Barang;
use Illuminate\Http\Request;

class PembelianDetailController extends Controller
{
    public function index()
    {
        $details = PembelianDetail::with('pembelian', 'barang')->get();
        return view('pembelian_detail.index', compact('details'));
    }

    public function create()
    {
        $pembelian = Pembelian::all();
        $barang    = Barang::all();
        return view('pembelian_detail.create', compact('pembelian', 'barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pembelian_id' => 'required|exists:pembelian,id',
            'barang_id'    => 'required|exists:barang,id',
            'qty'          => 'required|integer|min:1',
            'harga'        => 'required|numeric|min:0',
            'subtotal'     => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        PembelianDetail::create([
            'pembelian_id' => $data['pembelian_id'],
            'barang_id'    => $data['barang_id'],
            'qty'          => $data['qty'],
            'harga'        => $data['harga'],
            'subtotal'     => $data['subtotal'],
        ]);

        return redirect()->route('pembelian-detail.index')->with('success', 'Detail pembelian berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $detail = PembelianDetail::with('pembelian', 'barang')->findOrFail($id);
        return view('pembelian_detail.show', compact('detail'));
    }

    public function edit(string $id)
    {
        $detail    = PembelianDetail::findOrFail($id);
        $pembelian = Pembelian::all();
        $barang    = Barang::all();
        return view('pembelian_detail.edit', compact('detail', 'pembelian', 'barang'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'pembelian_id' => 'required|exists:pembelian,id',
            'barang_id'    => 'required|exists:barang,id',
            'qty'          => 'required|integer|min:1',
            'harga'        => 'required|numeric|min:0',
            'subtotal'     => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        $detail = PembelianDetail::findOrFail($id);
        $detail->update([
            'pembelian_id' => $data['pembelian_id'],
            'barang_id'    => $data['barang_id'],
            'qty'          => $data['qty'],
            'harga'        => $data['harga'],
            'subtotal'     => $data['subtotal'],
        ]);

        return redirect()->route('pembelian-detail.index')->with('success', 'Detail pembelian berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $detail = PembelianDetail::findOrFail($id);
        $detail->delete();

        return redirect()->route('pembelian-detail.index')->with('success', 'Detail pembelian berhasil dihapus.');
    }
}
