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
        ])->whereDate('tanggal', today())->get();

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
            'scan_out'        => 'required',
            'total_harga'     => 'required|numeric|min:0',
            'is_draft'     => 'required',
            'items'           => 'required'
        ]);


        DB::beginTransaction();

        try {

            $items = json_decode($request->items, true);
            $isDraft = $request->is_draft ?? 'no';

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
                'scan_out'        => $request->scan_out,
                'is_draft'        => $request->is_draft ?? 'no',
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

                if ($isDraft === 'no' && $stokSebelum < $qty) {
                    throw new \Exception("Stok tidak cukup untuk barang ID ".$barangId);
                }

                $stokSesudah = $stokSebelum - $qty;

                // 3️⃣ update stok_barang
                if ($isDraft === 'no') {
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
            'kode_penjualan' => 'required|string|unique:penjualan,kode_penjualan,'.$id,
            'dropshipper_id' => 'nullable|exists:dropshipper,id',
            'tanggal'        => 'required|date',
            'total_harga'    => 'required|numeric|min:0',
            'scan_out'       => 'required',
            'is_draft'       => 'required',
            'items'          => 'required'
        ]);

        DB::beginTransaction();

        try {
            $penjualan = Penjualan::with('detail')->findOrFail($id);
            $wasDraft  = $penjualan->is_draft; // status LAMA sebelum diupdate
            $isDraft   = $request->is_draft ?? 'no'; // status BARU dari request

            $items = json_decode($request->items, true);

            $nomorResi      = $items[0]['nomor_resi'] ?? null;
            $nomorPesanan   = $items[0]['nomor_pesanan'] ?? null;
            $nomorTransaksi = $items[0]['nomor_transaksi'] ?? null;

            if ($nomorResi && Penjualan::where('nomor_resi', $nomorResi)
                    ->where('id', '!=', $penjualan->id)
                    ->exists()) {
                throw new \Exception("Nomor resi sudah digunakan, tidak boleh duplikat");
            }

            $penjualan->update([
                'kode_penjualan'  => $request->kode_penjualan,
                'nomor_resi'      => $nomorResi,
                'nomor_pesanan'   => $nomorPesanan,
                'nomor_transaksi' => $nomorTransaksi,
                'dropshipper_id'  => $request->dropshipper_id,
                'tanggal'         => $request->tanggal,
                'total_harga'     => $request->total_harga,
                'scan_out'        => $request->scan_out,
                'is_draft'        => $isDraft,
                'keterangan'      => $request->keterangan,
            ]);

            $oldDetails      = $penjualan->detail->keyBy('barang_id');
            $processedBarang = [];

            foreach ($items as $item) {
                $barangId = $item['id'];
                $newQty   = $item['qty'];
                $harga    = $item['harga_2'];
                $oldQty   = $oldDetails[$barangId]->qty ?? 0;

                $stok        = StokBarang::where('barang_id', $barangId)->lockForUpdate()->first();
                $stokSebelum = $stok->jumlah_stok ?? 0;

                // ============================================================
                // CASE: no → yes
                // Penjualan aktif diubah jadi draft → kembalikan stok lama
                // ============================================================
                if ($wasDraft === 'no' && $isDraft === 'yes') {
                    $stokSesudah = $stokSebelum + $oldQty;
                    StokBarang::updateOrCreate(
                        ['barang_id' => $barangId],
                        ['jumlah_stok' => $stokSesudah]
                    );
                    StokMovement::create([
                        'barang_id'      => $barangId,
                        'jenis'          => 'masuk',
                        'qty'            => $oldQty,
                        'stok_sebelum'   => $stokSebelum,
                        'stok_sesudah'   => $stokSesudah,
                        'referensi_tipe' => 'penjualan_draft_revert',
                        'referensi_id'   => $penjualan->id,
                        'keterangan'     => 'Revert ke draft ' . $penjualan->kode_penjualan,
                        'created_by'     => Auth::guard('pengguna')->user()->id
                    ]);
                }

                // ============================================================
                // CASE: yes → no  ← KASUS B KAMU ADA DI SINI
                // Draft diproses jadi aktif → kurangi stok dengan newQty
                // Cek stok dulu, tidak boleh minus
                // ============================================================
                elseif ($wasDraft === 'yes' && $isDraft === 'no') {
                    if ($stokSebelum < $newQty) {
                        throw new \Exception(
                            "Stok tidak cukup untuk barang ID {$barangId}. " .
                            "Stok tersedia: {$stokSebelum}, dibutuhkan: {$newQty}"
                        );
                    }
                    $stokSesudah = $stokSebelum - $newQty;
                    StokBarang::updateOrCreate(
                        ['barang_id' => $barangId],
                        ['jumlah_stok' => $stokSesudah]
                    );
                    StokMovement::create([
                        'barang_id'      => $barangId,
                        'jenis'          => 'keluar',
                        'qty'            => $newQty,
                        'stok_sebelum'   => $stokSebelum,
                        'stok_sesudah'   => $stokSesudah,
                        'referensi_tipe' => 'penjualan_draft_processed',
                        'referensi_id'   => $penjualan->id,
                        'keterangan'     => 'Draft diproses ' . $penjualan->kode_penjualan,
                        'created_by'     => Auth::guard('pengguna')->user()->id
                    ]);
                }

                // ============================================================
                // CASE: no → no
                // Edit penjualan aktif → hitung delta, cek stok kalau nambah
                // ============================================================
                elseif ($wasDraft === 'no' && $isDraft === 'no') {
                    $delta = $newQty - $oldQty;

                    if ($delta > 0 && $stokSebelum < $delta) {
                        throw new \Exception(
                            "Stok tidak cukup untuk barang ID {$barangId}. " .
                            "Stok tersedia: {$stokSebelum}, tambahan dibutuhkan: {$delta}"
                        );
                    }

                    $stokSesudah = $stokSebelum - $delta;
                    StokBarang::updateOrCreate(
                        ['barang_id' => $barangId],
                        ['jumlah_stok' => $stokSesudah]
                    );

                    if ($delta !== 0) {
                        StokMovement::create([
                            'barang_id'      => $barangId,
                            'jenis'          => $delta > 0 ? 'keluar' : 'masuk',
                            'qty'            => abs($delta),
                            'stok_sebelum'   => $stokSebelum,
                            'stok_sesudah'   => $stokSesudah,
                            'referensi_tipe' => 'penjualan_update',
                            'referensi_id'   => $penjualan->id,
                            'keterangan'     => 'Edit penjualan ' . $penjualan->kode_penjualan,
                            'created_by'     => Auth::guard('pengguna')->user()->id
                        ]);
                    }
                }

                // CASE: yes → yes → tidak ada efek stok, skip

                PenjualanDetail::updateOrCreate(
                    ['penjualan_id' => $penjualan->id, 'barang_id' => $barangId],
                    [
                        'qty'             => $newQty,
                        'harga'           => $harga,
                        'subtotal'        => $newQty * $harga,
                        'nomor_resi'      => $item['nomor_resi'],
                        'nomor_pesanan'   => $item['nomor_pesanan'],
                        'nomor_transaksi' => $item['nomor_transaksi']
                    ]
                );

                $processedBarang[] = $barangId;
            }

            // ============================================================
            // HANDLE BARANG DIHAPUS DARI EDIT
            // Kembalikan stok hanya kalau sebelumnya penjualan aktif (no)
            // Kalau wasDraft=yes, stok tidak pernah dikurangi → skip
            // ============================================================
            foreach ($oldDetails as $barangId => $detail) {
                if (!in_array($barangId, $processedBarang)) {

                    PenjualanDetail::where([
                        'penjualan_id' => $penjualan->id,
                        'barang_id'    => $barangId
                    ])->delete();

                    // Hanya rollback stok kalau penjualan sebelumnya aktif
                    // dan status akhirnya juga aktif (no → no, item dihapus)
                    if ($wasDraft === 'no' && $isDraft === 'no') {
                        $qty  = $detail->qty;
                        $stok = StokBarang::where('barang_id', $barangId)
                            ->lockForUpdate()->first();

                        $stokSebelum = $stok->jumlah_stok;
                        $stokSesudah = $stokSebelum + $qty;

                        StokBarang::updateOrCreate(
                            ['barang_id' => $barangId],
                            ['jumlah_stok' => $stokSesudah]
                        );
                        StokMovement::create([
                            'barang_id'      => $barangId,
                            'jenis'          => 'masuk',
                            'qty'            => $qty,
                            'stok_sebelum'   => $stokSebelum,
                            'stok_sesudah'   => $stokSesudah,
                            'referensi_tipe' => 'penjualan_update_delete_item',
                            'referensi_id'   => $penjualan->id,
                            'keterangan'     => 'Hapus item saat edit ' . $penjualan->kode_penjualan,
                            'created_by'     => Auth::guard('pengguna')->user()->id
                        ]);
                    }
                    // no → yes: stok sudah dikembalikan di loop atas, skip
                    // yes → *: stok tidak pernah dikurangi, skip
                }
            }

            DB::commit();

            return redirect()->route('penjualan.index')
                ->with('success', 'Penjualan berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan : ' . $e->getMessage());
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
            if ($penjualan->is_draft === 'no') {

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
