<?php

namespace App\Http\Controllers;

use App\Models\AdjustStokDetail;
use App\Models\AdjustStok;
use App\Models\Barang;
use Illuminate\Http\Request;

class AdjustStokDetailController extends Controller
{
    public function index()
    {
        $details = AdjustStokDetail::with('adjust', 'barang')->get();
        return view('adjust_stok_detail.index', compact('details'));
    }

    public function create()
    {
        $adjustStok = AdjustStok::all();
        $barang     = Barang::all();
        return view('adjust_stok_detail.create', compact('adjustStok', 'barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'adjust_stok_id' => 'required|exists:adjust_stok,id',
            'barang_id'      => 'required|exists:barang,id',
            'qty_sistem'     => 'required|integer|min:0',
            'qty_fisik'      => 'required|integer|min:0',
            'selisih'        => 'required|integer',
        ]);

        $data = $request->all();

        AdjustStokDetail::create([
            'adjust_stok_id' => $data['adjust_stok_id'],
            'barang_id'      => $data['barang_id'],
            'qty_sistem'     => $data['qty_sistem'],
            'qty_fisik'      => $data['qty_fisik'],
            'selisih'        => $data['selisih'],
        ]);

        return redirect()->route('adjust-stok-detail.index')->with('success', 'Detail adjust stok berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $detail = AdjustStokDetail::with('adjust', 'barang')->findOrFail($id);
        return view('adjust_stok_detail.show', compact('detail'));
    }

    public function edit(string $id)
    {
        $detail     = AdjustStokDetail::findOrFail($id);
        $adjustStok = AdjustStok::all();
        $barang     = Barang::all();
        return view('adjust_stok_detail.edit', compact('detail', 'adjustStok', 'barang'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'adjust_stok_id' => 'required|exists:adjust_stok,id',
            'barang_id'      => 'required|exists:barang,id',
            'qty_sistem'     => 'required|integer|min:0',
            'qty_fisik'      => 'required|integer|min:0',
            'selisih'        => 'required|integer',
        ]);

        $data = $request->all();

        $detail = AdjustStokDetail::findOrFail($id);
        $detail->update([
            'adjust_stok_id' => $data['adjust_stok_id'],
            'barang_id'      => $data['barang_id'],
            'qty_sistem'     => $data['qty_sistem'],
            'qty_fisik'      => $data['qty_fisik'],
            'selisih'        => $data['selisih'],
        ]);

        return redirect()->route('adjust-stok-detail.index')->with('success', 'Detail adjust stok berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $detail = AdjustStokDetail::findOrFail($id);
        $detail->delete();

        return redirect()->route('adjust-stok-detail.index')->with('success', 'Detail adjust stok berhasil dihapus.');
    }
}
