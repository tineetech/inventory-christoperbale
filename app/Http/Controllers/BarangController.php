<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::with('satuan', 'stok')->get();
        return view('barang.index', compact('barang'));
    }

    public function create()
    {
        $satuan = Satuan::all();
        return view('barang.create', compact('satuan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku'           => 'required|string|max:50|unique:barang,sku',
            'nama_barang'   => 'required|string|max:100',
            'satuan_id'     => 'required|exists:satuan,id',
            'harga_1'       => 'required|numeric|min:0',
            'harga_2'       => 'nullable|numeric|min:0',
            'stok_minimum'  => 'nullable|integer|min:0',
            'keterangan'    => 'nullable|string',
        ]);

        $data = $request->all();

        Barang::create([
            'sku'          => $data['sku'],
            'nama_barang'  => $data['nama_barang'],
            'satuan_id'    => $data['satuan_id'],
            'harga_1'      => $data['harga_1'],
            'harga_2'      => $data['harga_2'] ?? null,
            'stok_minimum' => $data['stok_minimum'] ?? 0,
            'keterangan'   => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $barang = Barang::with('satuan', 'stok', 'stokMovement')->findOrFail($id);
        return view('barang.show', compact('barang'));
    }

    public function edit(string $id)
    {
        $barang = Barang::findOrFail($id);
        $satuan = Satuan::all();
        return view('barang.edit', compact('barang', 'satuan'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'sku'          => 'required|string|max:50|unique:barang,sku,' . $id,
            'nama_barang'  => 'required|string|max:100',
            'satuan_id'    => 'required|exists:satuan,id',
            'harga_1'      => 'required|numeric|min:0',
            'harga_2'      => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'keterangan'   => 'nullable|string',
        ]);

        $data = $request->all();

        $barang = Barang::findOrFail($id);
        $barang->update([
            'sku'          => $data['sku'],
            'nama_barang'  => $data['nama_barang'],
            'satuan_id'    => $data['satuan_id'],
            'harga_1'      => $data['harga_1'],
            'harga_2'      => $data['harga_2'] ?? null,
            'stok_minimum' => $data['stok_minimum'] ?? 0,
            'keterangan'   => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }
}
