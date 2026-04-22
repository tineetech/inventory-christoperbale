<?php

namespace App\Http\Controllers;

use App\Models\AdjustStok;
use App\Models\AdjustStokDetail;
use App\Models\Barang;
use App\Models\StokMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $kode = 'AS-' . date('Ymd') . '-' . rand(100,999);
        return view('pages.transaksi.adjust_stok.create', compact('kode'));
    }

    public function store(Request $request)
    {

    DB::beginTransaction();

    try{

    $items = json_decode($request->items,true);

    $adjust = AdjustStok::create([

    'kode_adjust'=>$request->kode_adjust,
    'tanggal'=>$request->tanggal,
    'keterangan'=>$request->keterangan,
    'created_by'=>Auth::guard('pengguna')->user()->id

    ]);

    foreach($items as $item){

    $barang = Barang::find($item['id']);

    $stok = $barang->stok;

    $stok_sebelum = $stok->jumlah_stok;

    $selisih = $item['selisih'];

    $stok_sesudah = $stok_sebelum + $selisih;

    AdjustStokDetail::create([

    'adjust_stok_id'=>$adjust->id,
    'barang_id'=>$item['id'],
    'qty_sistem'=>$stok_sebelum,
    'qty_fisik'=>$item['qty_fisik'],
    'selisih'=>$selisih

    ]);

    $stok->update([

    'jumlah_stok'=>$stok_sesudah

    ]);

    StokMovement::create([

    'barang_id'=>$item['id'],
    'jenis'=>'adjustment',
    'qty'=>$selisih,
    'stok_sebelum'=>$stok_sebelum,
    'stok_sesudah'=>$stok_sesudah,
    'referensi_tipe'=>'adjust_stok',
    'referensi_id'=>$adjust->id,
    'keterangan'=>'Adjustment stok',
    'created_by'=>Auth::guard('pengguna')->user()->id

    ]);

    }

    DB::commit();

    return redirect()->route('manage-stok.index')
    ->with('success','Adjustment stok berhasil');

    }catch(\Exception $e){

    DB::rollback();

    return back()->with('error',$e->getMessage());

    }

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

        return redirect()->route('adjust-stok.index')->with('success', 'ok berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $adjustStok = AdjustStok::findOrFail($id);
        $adjustStok->delete();

        return redirect()->route('adjust-stok.index')->with('success', 'ok berhasil dihapus.');
    }
}
