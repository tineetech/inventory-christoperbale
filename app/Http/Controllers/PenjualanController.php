<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Dropshipper;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PenjualanDetail;
use App\Models\StokBarang;
use App\Models\StokMovement;
class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with([
            'dropshipper',
            'user',
            'detail.barang.stok'
        ])->latest()->get();

        return view('pages.transaksi.penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        $dropshippers = Dropshipper::all();
        $supplier = Supplier::all();
        $kode = 'PJ-' . date('Ymd') . '-' . rand(100,999);
        return view('pages.transaksi.penjualan.create', compact('dropshippers', 'supplier', 'kode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_penjualan'  => 'required|string|unique:penjualan,kode_penjualan',
            'dropshipper_id'  => 'nullable|exists:dropshipper,id',
            'tanggal'         => 'required|date',
            'total_harga'     => 'required|numeric|min:0',
            'items'           => 'required'
        ]);

        DB::beginTransaction();

        try {

            $items = json_decode($request->items, true);


            $nomorResi = $items[0]['nomor_resi'] ?? null;
            $nomorPesanan = $items[0]['nomor_pesanan'];
            $nomorTransaksi = $items[0]['nomor_transaksi'];

            if ($nomorResi) {
                $duplicate = Penjualan::where('nomor_resi', $nomorResi)->exists();
                if ($duplicate) {
                    // dd('woi');
                    // return back()->with('error','Nomor resi sudah digunakan, tidak boleh duplikat.');
                    throw new \Exception("Nomor resi sudah digunakan, tidak boleh duplikat ");
                }
            }

            // 1️⃣ create penjualan
            $penjualan = Penjualan::create([
                'kode_penjualan'  => $request->kode_penjualan,
                'nomor_resi'      => $nomorResi ?? null,
                'nomor_pesanan'   => $nomorPesanan ?? null,
                'nomor_transaksi' => $nomorTransaksi ?? null,
                'dropshipper_id'  => $request->dropshipper_id,
                'tanggal'         => $request->tanggal,
                'total_harga'     => $request->total_harga,
                'keterangan'      => $request->keterangan,
                'created_by'      => Auth::guard('pengguna')->user()->id
            ]);

            foreach ($items as $item) {

                $barangId = $item['id'];
                $qty      = $item['qty'];
                $harga    = $item['harga_2'];

                $subtotal = $qty * $harga;

                // 2️⃣ insert penjualan_detail
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id'    => $barangId,
                    'qty'          => $qty,
                    'harga'        => $harga,
                    'subtotal'     => $subtotal,
                    'nomor_resi'=>$item['nomor_resi'],
                    'nomor_pesanan'=>$item['nomor_pesanan'],
                    'nomor_transaksi'=>$item['nomor_transaksi']
                ]);

                // ambil stok lama
                $stok = StokBarang::where('barang_id', $barangId)
                    ->lockForUpdate()
                    ->first();

                $stokSebelum = $stok->jumlah_stok ?? 0;

                if ($stokSebelum < $qty) {
                    throw new \Exception("Stok tidak cukup untuk barang ID ".$barangId);
                }

                $stokSesudah = $stokSebelum - $qty;

                // 3️⃣ update stok_barang
                StokBarang::updateOrCreate(
                    ['barang_id' => $barangId],
                    ['jumlah_stok' => $stokSesudah]
                );

                // 4️⃣ create stok movement
                StokMovement::create([
                    'barang_id'      => $barangId,
                    'jenis'          => 'keluar',
                    'qty'            => $qty,
                    'stok_sebelum'   => $stokSebelum,
                    'stok_sesudah'   => $stokSesudah,
                    'referensi_tipe' => 'penjualan',
                    'referensi_id'   => $penjualan->id,
                    'keterangan'     => 'Penjualan '.$penjualan->kode_penjualan,
                    'created_by'     => Auth::guard('pengguna')->user()->id
                ]);
            }

            DB::commit();

            return redirect()
                ->route('penjualan.index')
                ->with('success','Penjualan berhasil disimpan');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error','Terjadi kesalahan : '.$e->getMessage());
        }
    }

    public function show(string $id)
    {
        $penjualan = Penjualan::with('dropshipper', 'user', 'detail.barang')->findOrFail($id);
        return view('pages.transaksi.penjualan.show', compact('penjualan'));
    }

    public function edit(string $id)
    {
        $penjualan = Penjualan::with('detail.barang.stok')->findOrFail($id);
        $dropshippers = Dropshipper::all();

        return view('pages.transaksi.penjualan.edit', compact('penjualan','dropshippers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_penjualan'  => 'required|string|unique:penjualan,kode_penjualan,'.$id,
            'dropshipper_id'  => 'nullable|exists:dropshipper,id',
            'tanggal'         => 'required|date',
            'total_harga'     => 'required|numeric|min:0',
            'items'           => 'required'
        ]);

        DB::beginTransaction();

        try {

            $penjualan = Penjualan::with('detail')->findOrFail($id);

            $items = json_decode($request->items, true);

            $nomorResi      = $items[0]['nomor_resi'] ?? null;
            $nomorPesanan   = $items[0]['nomor_pesanan'] ?? null;
            $nomorTransaksi = $items[0]['nomor_transaksi'] ?? null;

            if ($nomorResi && Penjualan::where('nomor_resi', $nomorResi)
                    ->where('id', '!=', $penjualan->id)  // <-- kecualikan diri sendiri
                    ->exists()) {
                    throw new \Exception("Nomor resi sudah digunakan, tidak boleh duplikat ");
            }

            /*
            =========================================
            UPDATE HEADER
            =========================================
            */

            $penjualan->update([
                'kode_penjualan'  => $request->kode_penjualan,
                'nomor_resi'      => $nomorResi,
                'nomor_pesanan'   => $nomorPesanan,
                'nomor_transaksi' => $nomorTransaksi,
                'dropshipper_id'  => $request->dropshipper_id,
                'tanggal'         => $request->tanggal,
                'total_harga'     => $request->total_harga,
                'keterangan'      => $request->keterangan,
            ]);

            /*
            =========================================
            MAP DETAIL LAMA
            =========================================
            */

            $oldDetails = $penjualan->detail->keyBy('barang_id');

            $processedBarang = [];

            foreach ($items as $item) {

                $barangId = $item['id'];
                $newQty   = $item['qty'];
                $harga    = $item['harga_2'];

                $subtotal = $newQty * $harga;

                $oldQty = $oldDetails[$barangId]->qty ?? 0;

                $delta = $newQty - $oldQty;

                $stok = StokBarang::where('barang_id',$barangId)
                    ->lockForUpdate()
                    ->first();

                $stokSebelum = $stok->jumlah_stok ?? 0;

                if ($delta > 0 && $stokSebelum < $delta) {
                    throw new \Exception("Stok tidak cukup untuk barang ID ".$barangId);
                }

                $stokSesudah = $stokSebelum - $delta;

                StokBarang::updateOrCreate(
                    ['barang_id'=>$barangId],
                    ['jumlah_stok'=>$stokSesudah]
                );

                if ($delta != 0) {

                    StokMovement::create([
                        'barang_id'      => $barangId,
                        'jenis'          => $delta > 0 ? 'keluar' : 'masuk',
                        'qty'            => abs($delta),
                        'stok_sebelum'   => $stokSebelum,
                        'stok_sesudah'   => $stokSesudah,
                        'referensi_tipe' => 'penjualan_update',
                        'referensi_id'   => $penjualan->id,
                        'keterangan'     => 'Edit penjualan '.$penjualan->kode_penjualan,
                        'created_by'     => Auth::guard('pengguna')->user()->id
                    ]);
                }

                PenjualanDetail::updateOrCreate(
                    [
                        'penjualan_id' => $penjualan->id,
                        'barang_id'    => $barangId
                    ],
                    [
                        'qty'             => $newQty,
                        'harga'           => $harga,
                        'subtotal'        => $subtotal,
                        'nomor_resi'      => $item['nomor_resi'],
                        'nomor_pesanan'   => $item['nomor_pesanan'],
                        'nomor_transaksi' => $item['nomor_transaksi']
                    ]
                );

                $processedBarang[] = $barangId;
            }

            /*
            =========================================
            HANDLE BARANG YANG DIHAPUS DARI EDIT
            =========================================
            */

            foreach ($oldDetails as $barangId => $detail) {

                if (!in_array($barangId, $processedBarang)) {

                    $qty = $detail->qty;

                    $stok = StokBarang::where('barang_id',$barangId)
                        ->lockForUpdate()
                        ->first();

                    $stokSebelum = $stok->jumlah_stok;

                    $stokSesudah = $stokSebelum + $qty;

                    StokBarang::updateOrCreate(
                        ['barang_id'=>$barangId],
                        ['jumlah_stok'=>$stokSesudah]
                    );

                    StokMovement::create([
                        'barang_id'      => $barangId,
                        'jenis'          => 'masuk',
                        'qty'            => $qty,
                        'stok_sebelum'   => $stokSebelum,
                        'stok_sesudah'   => $stokSesudah,
                        'referensi_tipe' => 'penjualan_update_delete_item',
                        'referensi_id'   => $penjualan->id,
                        'keterangan'     => 'Hapus item saat edit '.$penjualan->kode_penjualan,
                        'created_by'     => Auth::guard('pengguna')->user()->id
                    ]);

                    PenjualanDetail::where([
                        'penjualan_id'=>$penjualan->id,
                        'barang_id'=>$barangId
                    ])->delete();
                }
            }

            DB::commit();

            return redirect()
                ->route('penjualan.index')
                ->with('success','Penjualan berhasil diupdate');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error','Terjadi kesalahan : '.$e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {

            $penjualan = Penjualan::with('detail')->findOrFail($id);

            /*
            =============================================
            1️⃣ ROLLBACK STOK DARI PENJUALAN
            =============================================
            */

            foreach ($penjualan->detail as $detail) {

                $barangId = $detail->barang_id;
                $qty      = $detail->qty;

                $stok = StokBarang::where('barang_id', $barangId)
                    ->lockForUpdate()
                    ->first();

                $stokSebelum = $stok->jumlah_stok ?? 0;
                $stokSesudah = $stokSebelum + $qty;

                StokBarang::updateOrCreate(
                    ['barang_id' => $barangId],
                    ['jumlah_stok' => $stokSesudah]
                );

                /*
                =============================================
                2️⃣ CATAT STOK MOVEMENT
                =============================================
                */

                StokMovement::create([
                    'barang_id'      => $barangId,
                    'jenis'          => 'masuk',
                    'qty'            => $qty,
                    'stok_sebelum'   => $stokSebelum,
                    'stok_sesudah'   => $stokSesudah,
                    'referensi_tipe' => 'penjualan_delete',
                    'referensi_id'   => $penjualan->id,
                    'keterangan'     => 'Hapus penjualan '.$penjualan->kode_penjualan,
                    'created_by'     => Auth::guard('pengguna')->user()->id
                ]);
            }

            /*
            =============================================
            3️⃣ HAPUS DETAIL
            =============================================
            */

            PenjualanDetail::where('penjualan_id', $penjualan->id)->delete();

            /*
            =============================================
            4️⃣ HAPUS HEADER
            =============================================
            */

            $penjualan->delete();

            DB::commit();

            return redirect()
                ->route('penjualan.index')
                ->with('success', 'Penjualan berhasil dihapus');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Terjadi kesalahan : '.$e->getMessage()
            );
        }
    }
}
