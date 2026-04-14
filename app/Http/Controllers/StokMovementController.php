<?php

namespace App\Http\Controllers;

use App\Models\StokMovement;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StokMovementController extends Controller
{
    public function index()
    {
        $movements = StokMovement::with('barang', 'user')->latest()->get();
        return view('stok_movement.index', compact('movements'));
    }

    public function create()
    {
        $barang = Barang::all();
        return view('stok_movement.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_id'       => 'required|exists:barang,id',
            'jenis'           => 'required|in:masuk,keluar,penyesuaian',
            'qty'             => 'required|integer|min:1',
            'stok_sebelum'    => 'required|integer|min:0',
            'stok_sesudah'    => 'required|integer|min:0',
            'referensi_tipe'  => 'nullable|string|max:50',
            'referensi_id'    => 'nullable|integer',
            'keterangan'      => 'nullable|string',
        ]);

        $data = $request->all();

        StokMovement::create([
            'barang_id'      => $data['barang_id'],
            'jenis'          => $data['jenis'],
            'qty'            => $data['qty'],
            'stok_sebelum'   => $data['stok_sebelum'],
            'stok_sesudah'   => $data['stok_sesudah'],
            'referensi_tipe' => $data['referensi_tipe'] ?? null,
            'referensi_id'   => $data['referensi_id'] ?? null,
            'keterangan'     => $data['keterangan'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('stok-movement.index')->with('success', 'Stok movement berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $movement = StokMovement::with('barang', 'user')->findOrFail($id);
        return view('stok_movement.show', compact('movement'));
    }

    public function edit(string $id)
    {
        $movement = StokMovement::findOrFail($id);
        $barang   = Barang::all();
        return view('stok_movement.edit', compact('movement', 'barang'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'barang_id'      => 'required|exists:barang,id',
            'jenis'          => 'required|in:masuk,keluar,penyesuaian',
            'qty'            => 'required|integer|min:1',
            'stok_sebelum'   => 'required|integer|min:0',
            'stok_sesudah'   => 'required|integer|min:0',
            'referensi_tipe' => 'nullable|string|max:50',
            'referensi_id'   => 'nullable|integer',
            'keterangan'     => 'nullable|string',
        ]);

        $data = $request->all();

        $movement = StokMovement::findOrFail($id);
        $movement->update([
            'barang_id'      => $data['barang_id'],
            'jenis'          => $data['jenis'],
            'qty'            => $data['qty'],
            'stok_sebelum'   => $data['stok_sebelum'],
            'stok_sesudah'   => $data['stok_sesudah'],
            'referensi_tipe' => $data['referensi_tipe'] ?? null,
            'referensi_id'   => $data['referensi_id'] ?? null,
            'keterangan'     => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('stok-movement.index')->with('success', 'Stok movement berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $movement = StokMovement::findOrFail($id);
        $movement->delete();

        return redirect()->route('stok-movement.index')->with('success', 'Stok movement berhasil dihapus.');
    }
}
