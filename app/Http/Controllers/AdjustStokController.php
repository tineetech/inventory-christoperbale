<?php

namespace App\Http\Controllers;

use App\Models\AdjustStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdjustStokController extends Controller
{
    public function index()
    {
        $adjustments = AdjustStok::with('user', 'detail.barang.stok')->latest()->get();
        return view('pages.transaksi.adjust_stok.index', compact('adjustments'));
    }

    public function create()
    {
        return view('pages.transaksi.adjust_stok.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_adjust' => 'required|string|max:50|unique:adjust_stok,kode_adjust',
            'tanggal'     => 'required|date',
            'keterangan'  => 'nullable|string',
        ]);

        $data = $request->all();

        AdjustStok::create([
            'kode_adjust' => $data['kode_adjust'],
            'tanggal'     => $data['tanggal'],
            'keterangan'  => $data['keterangan'] ?? null,
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('adjust-stok.index')->with('success', 'Adjust stok berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $adjustStok = AdjustStok::with('user', 'detail.barang')->findOrFail($id);
        return view('pages.transaksi.adjust_stok.show', compact('adjustStok'));
    }

    public function edit(string $id)
    {
        $adjustStok = AdjustStok::findOrFail($id);
        return view('pages.transaksi.adjust_stok.edit', compact('adjustStok'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode_adjust' => 'required|string|max:50|unique:adjust_stok,kode_adjust,' . $id,
            'tanggal'     => 'required|date',
            'keterangan'  => 'nullable|string',
        ]);

        $data = $request->all();

        $adjustStok = AdjustStok::findOrFail($id);
        $adjustStok->update([
            'kode_adjust' => $data['kode_adjust'],
            'tanggal'     => $data['tanggal'],
            'keterangan'  => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('adjust-stok.index')->with('success', 'Adjust stok berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $adjustStok = AdjustStok::findOrFail($id);
        $adjustStok->delete();

        return redirect()->route('adjust-stok.index')->with('success', 'Adjust stok berhasil dihapus.');
    }
}
