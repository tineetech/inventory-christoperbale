<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:100',
            'no_telp'       => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'keterangan'    => 'nullable|string',
        ]);

        $data = $request->all();

        Supplier::create([
            'nama_supplier' => $data['nama_supplier'],
            'no_telp'       => $data['no_telp'] ?? null,
            'alamat'        => $data['alamat'] ?? null,
            'keterangan'    => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $supplier = Supplier::with('pembelian')->findOrFail($id);
        return view('supplier.show', compact('supplier'));
    }

    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:100',
            'no_telp'       => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'keterangan'    => 'nullable|string',
        ]);

        $data = $request->all();

        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'nama_supplier' => $data['nama_supplier'],
            'no_telp'       => $data['no_telp'] ?? null,
            'alamat'        => $data['alamat'] ?? null,
            'keterangan'    => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
