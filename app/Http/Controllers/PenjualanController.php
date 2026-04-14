<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Dropshipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with('dropshipper', 'user')->latest()->get();
        return view('penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        $dropshippers = Dropshipper::all();
        return view('penjualan.create', compact('dropshippers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_penjualan'    => 'required|string|max:50|unique:penjualan,kode_penjualan',
            'nomor_resi'        => 'nullable|string|max:100',
            'nomor_pesanan'     => 'nullable|string|max:100',
            'nomor_transaksi'   => 'nullable|string|max:100',
            'dropshipper_id'    => 'nullable|exists:dropshipper,id',
            'tanggal'           => 'required|date',
            'total_harga'       => 'required|numeric|min:0',
            'keterangan'        => 'nullable|string',
        ]);

        $data = $request->all();

        Penjualan::create([
            'kode_penjualan'  => $data['kode_penjualan'],
            'nomor_resi'      => $data['nomor_resi'] ?? null,
            'nomor_pesanan'   => $data['nomor_pesanan'] ?? null,
            'nomor_transaksi' => $data['nomor_transaksi'] ?? null,
            'dropshipper_id'  => $data['dropshipper_id'] ?? null,
            'tanggal'         => $data['tanggal'],
            'total_harga'     => $data['total_harga'],
            'keterangan'      => $data['keterangan'] ?? null,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $penjualan = Penjualan::with('dropshipper', 'user', 'detail.barang')->findOrFail($id);
        return view('penjualan.show', compact('penjualan'));
    }

    public function edit(string $id)
    {
        $penjualan    = Penjualan::findOrFail($id);
        $dropshippers = Dropshipper::all();
        return view('penjualan.edit', compact('penjualan', 'dropshippers'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode_penjualan'  => 'required|string|max:50|unique:penjualan,kode_penjualan,' . $id,
            'nomor_resi'      => 'nullable|string|max:100',
            'nomor_pesanan'   => 'nullable|string|max:100',
            'nomor_transaksi' => 'nullable|string|max:100',
            'dropshipper_id'  => 'nullable|exists:dropshipper,id',
            'tanggal'         => 'required|date',
            'total_harga'     => 'required|numeric|min:0',
            'keterangan'      => 'nullable|string',
        ]);

        $data = $request->all();

        $penjualan = Penjualan::findOrFail($id);
        $penjualan->update([
            'kode_penjualan'  => $data['kode_penjualan'],
            'nomor_resi'      => $data['nomor_resi'] ?? null,
            'nomor_pesanan'   => $data['nomor_pesanan'] ?? null,
            'nomor_transaksi' => $data['nomor_transaksi'] ?? null,
            'dropshipper_id'  => $data['dropshipper_id'] ?? null,
            'tanggal'         => $data['tanggal'],
            'total_harga'     => $data['total_harga'],
            'keterangan'      => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $penjualan = Penjualan::findOrFail($id);
        $penjualan->delete();

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus.');
    }
}
