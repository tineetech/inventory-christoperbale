<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PembelianDetail;
use App\Models\StokBarang;
use App\Models\StokMovement;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with('supplier', 'detail.barang.stok', 'user')->latest()->get();
        return view('pages.transaksi.pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        $supplier = Supplier::all();
        $user = Auth::guard('pengguna')->user()->role->nama_role;
        $kode = 'PB-' . date('Ymd') . '-' . rand(100,999);
        return view('pages.transaksi.pembelian.create', compact('supplier','kode', 'user'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'kode_pembelian' => 'required|string|unique:pembelian,kode_pembelian',
            'supplier_id'    => 'required|exists:supplier,id',
            'tanggal'        => 'required|date',
            'total_harga'    => 'required|numeric|min:0',
            'items'          => 'required'
        ]);

        DB::beginTransaction();

        try {

            $items = json_decode($request->items, true);

            // 1️⃣ create pembelian
            $pembelian = Pembelian::create([
                'kode_pembelian' => $request->kode_pembelian,
                'supplier_id'    => $request->supplier_id,
                'tanggal'        => $request->tanggal,
                'total_harga'    => $request->total_harga,
                'keterangan'     => $request->keterangan,
                'created_by'     => Auth::guard('pengguna')->user()->id
            ]);

            foreach($items as $item){

                $barangId = $item['id'];
                $qty      = $item['qty'];
                $harga    = $item['harga_1'];

                $subtotal = $qty * $harga;

                // 2️⃣ insert pembelian_detail
                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id'    => $barangId,
                    'qty'          => $qty,
                    'harga'        => $harga,
                    'subtotal'     => $subtotal
                ]);

                // ambil stok lama
                $stok = StokBarang::where('barang_id',$barangId)->first();

                $stokSebelum = $stok->jumlah_stok ?? 0;
                $stokSesudah = $stokSebelum + $qty;

                // 3️⃣ update stok_barang
                StokBarang::updateOrCreate(
                    ['barang_id'=>$barangId],
                    ['jumlah_stok'=>$stokSesudah]
                );

                // 4️⃣ create stok movement
                StokMovement::create([
                    'barang_id'       => $barangId,
                    'jenis'           => 'masuk',
                    'qty'             => $qty,
                    'stok_sebelum'    => $stokSebelum,
                    'stok_sesudah'    => $stokSesudah,
                    'referensi_tipe'  => 'pembelian',
                    'referensi_id'    => $pembelian->id,
                    'keterangan'      => 'Pembelian '.$pembelian->kode_pembelian,
                    'created_by'     => Auth::guard('pengguna')->user()->id
                ]);
            }

            DB::commit();

            return redirect()
                ->route('pembelian.index')
                ->with('success','Pembelian berhasil disimpan');

        } catch (\Exception $e){

            DB::rollBack();

            return back()->with('error','Terjadi kesalahan : '.$e->getMessage());
        }
    }

    public function show(string $id)
    {
        $pembelian = Pembelian::with('supplier', 'user', 'detail.barang')->findOrFail($id);
        return view('pages.transaksi.pembelian.show', compact('pembelian'));
    }

    public function edit(string $id)
    {
        $pembelian = Pembelian::with('detail', 'supplier')->findOrFail($id);
        $suppliers = Supplier::all();
        $user = Auth::guard('pengguna')->user()->role->nama_role;
        return view('pages.transaksi.pembelian.edit', compact('pembelian', 'suppliers', 'user'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $pembelian = Pembelian::findOrFail($id);

            $oldDetails = PembelianDetail::where('pembelian_id', $id)->get()
                ->keyBy('barang_id');

            $items = json_decode($request->items, true);

            /*
            =========================
            UPDATE HEADER
            =========================
            */

            $pembelian->update([
                'supplier_id' => $request->supplier_id,
                'kode_pembelian' => $request->kode_pembelian,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'total_harga' => $request->total_harga
            ]);

            $processedBarang = [];

            foreach ($items as $item) {

                $barangId = $item['id'];
                $qtyBaru = $item['qty'];
                $harga = $item['harga_1'];

                $qtyLama = $oldDetails[$barangId]->qty ?? 0;

                $delta = $qtyBaru - $qtyLama;

                $subtotal = $qtyBaru * $harga;

                /*
                =========================
                UPDATE / INSERT DETAIL
                =========================
                */

                PembelianDetail::updateOrCreate(
                    [
                        'pembelian_id' => $id,
                        'barang_id' => $barangId
                    ],
                    [
                        'qty' => $qtyBaru,
                        'harga' => $harga,
                        'subtotal' => $subtotal
                    ]
                );

                /*
                =========================
                UPDATE STOK DENGAN DELTA
                =========================
                */

                if ($delta != 0) {

                    $stok = StokBarang::firstOrCreate(
                        ['barang_id' => $barangId],
                        ['jumlah_stok' => 0]
                    );

                    $stokSebelum = $stok->jumlah_stok;
                    $stokSesudah = $stokSebelum + $delta;

                    $stok->update([
                        'jumlah_stok' => $stokSesudah
                    ]);

                    /*
                    =========================
                    STOK MOVEMENT
                    =========================
                    */

                    StokMovement::create([
                        'barang_id' => $barangId,
                        'jenis' => $delta > 0 ? 'masuk' : 'keluar',
                        'qty' => abs($delta),
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'referensi_tipe' => 'pembelian_update',
                        'referensi_id' => $pembelian->id,
                        'keterangan' => 'Edit pembelian ' . $pembelian->kode_pembelian,
                        'created_by' => Auth::guard('pengguna')->user()->id
                    ]);
                }

                $processedBarang[] = $barangId;
            }

            /*
            =========================
            BARANG YANG DIHAPUS
            =========================
            */

            foreach ($oldDetails as $barangId => $detail) {

                if (!in_array($barangId, $processedBarang)) {

                    $stok = StokBarang::where('barang_id', $barangId)->first();

                    if ($stok) {

                        $stokSebelum = $stok->jumlah_stok;
                        $stokSesudah = $stokSebelum - $detail->qty;

                        $stok->update([
                            'jumlah_stok' => $stokSesudah
                        ]);

                        StokMovement::create([
                            'barang_id' => $barangId,
                            'jenis' => 'keluar',
                            'qty' => $detail->qty,
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'referensi_tipe' => 'pembelian_update',
                            'referensi_id' => $pembelian->id,
                            'keterangan' => 'Hapus item pembelian',
                            'created_by' => Auth::guard('pengguna')->user()->id
                        ]);
                    }

                    $detail->delete();
                }
            }

            DB::commit();

            return redirect()
                ->route('pembelian.index')
                ->with('success', 'Pembelian berhasil diupdate');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {

            $pembelian = Pembelian::with('detail')->findOrFail($id);

            foreach ($pembelian->detail as $detail) {

                $stok = StokBarang::where('barang_id', $detail->barang_id)->first();

                if ($stok) {

                    $stokSebelum = $stok->jumlah_stok;

                    // rollback stok
                    $stokSesudah = $stokSebelum - $detail->qty;

                    // update stok barang
                    $stok->update([
                        'jumlah_stok' => $stokSesudah
                    ]);

                    // catat stok movement
                    StokMovement::create([
                        'barang_id' => $detail->barang_id,
                        'jenis' => 'keluar',
                        'qty' => $detail->qty,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'referensi_tipe' => 'pembelian_delete',
                        'referensi_id' => $pembelian->id,
                        'keterangan' => 'Rollback hapus pembelian '.$pembelian->kode_pembelian,
                        'created_by' => Auth::guard('pengguna')->user()->id
                    ]);

                }

            }

            // hapus detail
            PembelianDetail::where('pembelian_id',$id)->delete();

            // hapus pembelian
            $pembelian->delete();

            DB::commit();

            return redirect()
                ->route('pembelian.index')
                ->with('success','Pembelian berhasil dihapus dan stok disesuaikan');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error',$e->getMessage());

        }
    }
}
