<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
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
    public function index(Request $request)
    {
        $keteranganCepat = 'penambahan barang langsung dari kepala gudang';

        $dariTanggal = $request->input('dari_tanggal', today()->toDateString());
        $sampaiTanggal = $request->input('sampai_tanggal', today()->toDateString());

        if ($dariTanggal > $sampaiTanggal) {
            [$dariTanggal, $sampaiTanggal] = [$sampaiTanggal, $dariTanggal];
        }

        $tipe = $request->input('tipe', 'semua');
        if (!in_array($tipe, ['semua', 'cepat', 'normal'])) {
            $tipe = 'semua';
        }

        $query = Pembelian::with('supplier', 'detail.barang.stok', 'user')
            ->whereDate('tanggal', '>=', $dariTanggal)
            ->whereDate('tanggal', '<=', $sampaiTanggal);

        if ($tipe === 'cepat') {
            $query->where('keterangan', $keteranganCepat);
        } elseif ($tipe === 'normal') {
            $query->where(function ($q) use ($keteranganCepat) {
                $q->where('keterangan', '!=', $keteranganCepat)->orWhereNull('keterangan');
            });
        }

        $pembelian = $query->orderByDesc('created_at')->get();

        return view('pages.transaksi.pembelian.index', compact('pembelian', 'dariTanggal', 'sampaiTanggal', 'tipe', 'keteranganCepat'));
    }

    public function create(Request $request)
    {
        $supplier = Supplier::all();
        $user = Auth::guard('pengguna')->user()->role->nama_role;
        $kode = 'PB-' . date('Ymd') . '-' . rand(100,999);
        $isCepat = $request->routeIs('pembelian.create-cepat') || $request->boolean('cepat');
        $defaultKeterangan = $isCepat ? 'penambahan barang langsung dari kepala gudang' : '';
        return view('pages.transaksi.pembelian.create', compact('supplier','kode', 'user', 'defaultKeterangan', 'isCepat'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'kode_pembelian' => 'required|string|unique:pembelian,kode_pembelian',
            'supplier_id'    => 'required|exists:supplier,id',
            'tanggal_final' => 'required|date_format:Y-m-d H:i:s',
            'total_harga'    => 'required|numeric|min:0',
            'items'          => 'required'
        ]);

        DB::beginTransaction();

        try {

            $items = json_decode($request->items, true);
            if (empty($items) || count($items) === 0) {
                throw new \Exception('Item tidak boleh kosong');
            }

            $isCepat = $request->boolean('is_cepat');
            $actor = Auth::guard('pengguna')->user();
            $actor->loadMissing('role');

            // 1️⃣ create pembelian
            $pembelian = Pembelian::create([
                'kode_pembelian' => $request->kode_pembelian,
                'supplier_id'    => $request->supplier_id,
                'tanggal'        => $request->tanggal_final,
                'total_harga'    => $request->total_harga,
                'keterangan'     => $request->keterangan,
                'created_by'     => $actor->id
            ]);

            $totalQty = 0;

            foreach($items as $item){

                $barangId = $item['id'];
                $qty      = (int) ($item['qty'] ?? 0);
                $harga    = $item['harga_1'];

                if ($qty <= 0) {
                    throw new \Exception('Qty item tidak valid');
                }

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
                    'created_by'     => $actor->id
                ]);

                $totalQty += $qty;
            }

            // 5️⃣ notifikasi pembelian (normal maupun cepat)
            $namaAktor = $actor->nama ?? $actor->full_name ?? 'Pengguna';
            $waktuPembelian = \Carbon\Carbon::parse($pembelian->tanggal);
            $tglTampil = $waktuPembelian->format('d/m/Y');
            $jamTampil = $waktuPembelian->format('H:i');

            if ($isCepat) {
                $isi = 'Kepala gudang ' . $namaAktor . ' menambahkan ' . $totalQty . ' barang '
                    . 'via pembelian ' . $pembelian->kode_pembelian
                    . ' pada ' . $tglTampil . ' pukul ' . $jamTampil . '.';

                Notifikasi::create([
                    'judul'      => 'Penambahan Cepat Barang oleh Kepala Gudang',
                    'isi'        => $isi,
                    'tipe'       => 'pembelian_cepat',
                    'link'       => route('pembelian.index'),
                    'payload'    => [
                        'pembelian_id'   => $pembelian->id,
                        'kode_pembelian' => $pembelian->kode_pembelian,
                        'total_qty'      => $totalQty,
                        'tanggal'        => $pembelian->tanggal,
                        'dibuat_oleh'    => $namaAktor,
                    ],
                    'user_id'    => null, // broadcast ke semua pengguna
                    'created_by' => $actor->id,
                ]);
            } else {
                $isi = $namaAktor . ' membuat pembelian ' . $pembelian->kode_pembelian
                    . ' sejumlah ' . $totalQty . ' barang'
                    . ' pada ' . $tglTampil . ' pukul ' . $jamTampil . '.';

                Notifikasi::create([
                    'judul'      => 'Pembelian Baru',
                    'isi'        => $isi,
                    'tipe'       => 'pembelian',
                    'link'       => route('pembelian.index'),
                    'payload'    => [
                        'pembelian_id'   => $pembelian->id,
                        'kode_pembelian' => $pembelian->kode_pembelian,
                        'total_qty'      => $totalQty,
                        'tanggal'        => $pembelian->tanggal,
                        'dibuat_oleh'    => $namaAktor,
                    ],
                    'user_id'    => null, // broadcast ke semua pengguna
                    'created_by' => $actor->id,
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
                'tanggal' => $request->tanggal_final,
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

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada ID yang dipilih.'], 422);
        }

        $deleted = 0;
        $errors  = [];

        foreach ($ids as $id) {
            DB::beginTransaction();
            try {
                $pembelian = Pembelian::with('detail')->findOrFail($id);

                // Rollback stok untuk setiap item
                foreach ($pembelian->detail as $detail) {
                    $stok = StokBarang::where('barang_id', $detail->barang_id)->first();

                    if ($stok) {
                        $stokSebelum = $stok->jumlah_stok;
                        $stokSesudah = $stokSebelum - $detail->qty;

                        $stok->update(['jumlah_stok' => $stokSesudah]);

                        StokMovement::create([
                            'barang_id'      => $detail->barang_id,
                            'jenis'          => 'keluar',
                            'qty'            => $detail->qty,
                            'stok_sebelum'   => $stokSebelum,
                            'stok_sesudah'   => $stokSesudah,
                            'referensi_tipe' => 'pembelian_delete',
                            'referensi_id'   => $pembelian->id,
                            'keterangan'     => 'Bulk hapus pembelian ' . $pembelian->kode_pembelian,
                            'created_by'     => Auth::guard('pengguna')->user()->id,
                        ]);
                    }
                }

                PembelianDetail::where('pembelian_id', $pembelian->id)->delete();
                $pembelian->delete();

                DB::commit();
                $deleted++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "ID {$id}: " . $e->getMessage();
            }
        }

        $message = "{$deleted} pembelian berhasil dihapus dan stok di-rollback.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' gagal.';
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'errors'  => $errors,
        ]);
    }
}
