<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with('supplier', 'user')->latest()->get();
        return view('pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view('pembelian.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_pembelian' => 'required|string|max:50|unique:pembelian,kode_pembelian',
            'supplier_id'    => 'required|exists:supplier,id',
            'tanggal'        => 'required|date',
            'total_harga'    => 'required|numeric|min:0',
            'keterangan'     => 'nullable|string',
        ]);

        $data = $request->all();

        Pembelian::create([
            'kode_pembelian' => $data['kode_pembelian'],
            'supplier_id'    => $data['supplier_id'],
            'tanggal'        => $data['tanggal'],
            'total_harga'    => $data['total_harga'],
            'keterangan'     => $data['keterangan'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $pembelian = Pembelian::with('supplier', 'user', 'detail.barang')->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    public function edit(string $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $suppliers = Supplier::all();
        return view('pembelian.edit', compact('pembelian', 'suppliers'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode_pembelian' => 'required|string|max:50|unique:pembelian,kode_pembelian,' . $id,
            'supplier_id'    => 'required|exists:supplier,id',
            'tanggal'        => 'required|date',
            'total_harga'    => 'required|numeric|min:0',
            'keterangan'     => 'nullable|string',
        ]);

        $data = $request->all();

        $pembelian = Pembelian::findOrFail($id);
        $pembelian->update([
            'kode_pembelian' => $data['kode_pembelian'],
            'supplier_id'    => $data['supplier_id'],
            'tanggal'        => $data['tanggal'],
            'total_harga'    => $data['total_harga'],
            'keterangan'     => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $pembelian->delete();

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus.');
    }
}
