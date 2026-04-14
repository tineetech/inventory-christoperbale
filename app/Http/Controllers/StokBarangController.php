<?php

namespace App\Http\Controllers;

use App\Models\StokBarang;
use App\Models\Barang;
use Illuminate\Http\Request;

class StokBarangController extends Controller
{
    public function index()
    {
        $stokBarang = StokBarang::with('barang')->get();
        return view('stok_barang.index', compact('stokBarang'));
    }

    public function create()
    {
        // Only barang that don't yet have a stok record
        $barang = Barang::doesntHave('stok')->get();
        return view('stok_barang.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_id'   => 'required|exists:barang,id|unique:stok_barang,barang_id',
            'jumlah_stok' => 'required|integer|min:0',
        ]);

        $data = $request->all();

        StokBarang::create([
            'barang_id'   => $data['barang_id'],
            'jumlah_stok' => $data['jumlah_stok'],
        ]);

        return redirect()->route('stok-barang.index')->with('success', 'Stok barang berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $stokBarang = StokBarang::with('barang')->findOrFail($id);
        return view('stok_barang.show', compact('stokBarang'));
    }

    public function edit(string $id)
    {
        $stokBarang = StokBarang::with('barang')->findOrFail($id);
        $barang     = Barang::all();
        return view('stok_barang.edit', compact('stokBarang', 'barang'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'barang_id'   => 'required|exists:barang,id|unique:stok_barang,barang_id,' . $id,
            'jumlah_stok' => 'required|integer|min:0',
        ]);

        $data = $request->all();

        $stokBarang = StokBarang::findOrFail($id);
        $stokBarang->update([
            'barang_id'   => $data['barang_id'],
            'jumlah_stok' => $data['jumlah_stok'],
        ]);

        return redirect()->route('stok-barang.index')->with('success', 'Stok barang berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $stokBarang = StokBarang::findOrFail($id);
        $stokBarang->delete();

        return redirect()->route('stok-barang.index')->with('success', 'Stok barang berhasil dihapus.');
    }
}
