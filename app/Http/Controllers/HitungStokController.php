<?php

namespace App\Http\Controllers;

use App\Models\AdjustStok;
use App\Models\AdjustStokDetail;
use App\Models\Barang;
use App\Models\StokMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class HitungStokController extends Controller
{
    public function index()
    {
        if (Auth::guard('pengguna')->user()->role->nama_role === 'karyawan') {
            $adjustments = AdjustStok::where('created_by', Auth::guard('pengguna')->user()->id)->with('user', 'detail.barang.stok')->latest()->get();
        } else if (Auth::guard('pengguna')->user()->role->nama_role === 'admin' || Auth::guard('pengguna')->user()->role->nama_role === 'super_admin') {
            $adjustments = AdjustStok::with('user', 'detail.barang.stok')->latest()->get();
        }
        return view('pages.transaksi.hitung_stok.index', compact('adjustments'));
    }

    public function create()
    {
        $kode = 'AS-' . date('Ymd') . '-' . rand(100, 999);
        return view('pages.transaksi.hitung_stok.create', compact('kode'));
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {

            $items = json_decode($request->items, true);

            $adjust = AdjustStok::create([

                'kode_adjust' => $request->kode_adjust,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'created_by' => Auth::guard('pengguna')->user()->id,
                'status' => 'pending',

            ]);

            foreach ($items as $item) {

                $barang = Barang::find($item['id']);

                $stok = $barang->stok;

                $stok_sebelum = $stok->jumlah_stok;

                $selisih = $item['selisih'];

                $stok_sesudah = $stok_sebelum + $selisih;

                AdjustStokDetail::create([

                    'adjust_stok_id' => $adjust->id,
                    'barang_id' => $item['id'],
                    'qty_sistem' => $stok_sebelum,
                    'qty_fisik' => $item['qty_fisik'],
                    'selisih' => $selisih

                ]);

                // $stok->update([

                //     'jumlah_stok' => $stok_sesudah

                // ]);

                // StokMovement::create([

                //     'barang_id' => $item['id'],
                //     'jenis' => 'adjustment',
                //     'qty' => $selisih,
                //     'stok_sebelum' => $stok_sebelum,
                //     'stok_sesudah' => $stok_sesudah,
                //     'referensi_tipe' => 'adjust_stok',
                //     'referensi_id' => $adjust->id,
                //     'keterangan' => 'Adjustment stok',
                //     'created_by' => Auth::guard('pengguna')->user()->id

                // ]);
            }

            DB::commit();

            return redirect()->route('hitung-stok.index')
                ->with('success', 'Adjustment stok berhasil');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $adjustStok = AdjustStok::with('user', 'detail.barang')->findOrFail($id);
        return view('pages.transaksi.hitung_stok.show', compact('adjustStok'));
    }

    public function edit(string $id)
    {
        $adjustStok = AdjustStok::with('user', 'detail.barang')->findOrFail($id);
        // dd(optional($adjustStok->detail->first())->barang->nama_barang);
        return view('pages.transaksi.hitung_stok.edit', compact('adjustStok'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode_adjust' => 'required|string|max:50|unique:adjust_stok,kode_adjust,' . $id,
            'tanggal'     => 'required|date',
            'keterangan'  => 'nullable|string',
            'status'      => 'required|in:pending,approve,reject',
        ]);

        DB::beginTransaction();

        try {

            $adjust = AdjustStok::with('detail')->findOrFail($id);

            $items = json_decode($request->items, true);

            // =========================
            // 1. UPDATE HEADER
            // =========================
            $adjust->update([
                'kode_adjust' => $request->kode_adjust,
                'tanggal'     => $request->tanggal,
                'keterangan'  => $request->keterangan ?? null,
                'status'      => $request->status,
            ]);

            foreach ($items as $item) {

                $barang = Barang::find($item['id']);
                $stok = $barang->stok;

                $stok_sekarang = $stok->jumlah_stok;

                // =========================
                // 2. UPDATE DETAIL (TIDAK HAPUS)
                // =========================
                AdjustStokDetail::where('adjust_stok_id', $adjust->id)->update([
                        'barang_id'      => $item['id'],
                        'qty_sistem' => $item['stok_sistem'],
                        'qty_fisik'  => $item['qty_fisik'],
                        'selisih'    => $item['selisih'],
                ]);

                // =========================
                // 3. JIKA APPROVE → UPDATE STOK
                // =========================
                if ($request->status === 'approve') {

                    $stok_sesudah = $stok_sekarang + $item['selisih'];

                    // update stok real
                    $stok->update([
                        'jumlah_stok' => $stok_sesudah
                    ]);

                    // catat movement
                    StokMovement::create([
                        'barang_id' => $item['id'],
                        'jenis' => 'adjustment',
                        'qty' => $item['selisih'],
                        'stok_sebelum' => $stok_sekarang,
                        'stok_sesudah' => $stok_sesudah,
                        'referensi_tipe' => 'adjust_stok',
                        'referensi_id' => $adjust->id,
                        'keterangan' => 'Approve adjustment stok',
                        'created_by' => Auth::guard('pengguna')->user()->id
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('hitung-stok.index')
                ->with('success', 'Adjustment berhasil diverifikasi');

        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $adjustStok = AdjustStok::findOrFail($id);
        $adjustStok->delete();

        return redirect()->route('hitung-stok.index')->with('success', 'ok berhasil dihapus.');
    }
}
