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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with([
            'dropshipper',
            'user',
            'detail.barang.stok'
        ])->get();
        // dd($penjualan);

        $dropshippers = Dropshipper::orderBy('nama')->get(); 

        return view('pages.transaksi.penjualan.index', compact('penjualan', 'dropshippers'));
    }

    public function create()
    {
        $dropshippers = Dropshipper::all();
        $supplier = Supplier::all();
        $kode = 'PJ-' . date('Ymd') . '-' . rand(100, 999);
        return view('pages.transaksi.penjualan.create', compact('dropshippers', 'supplier', 'kode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_penjualan'  => 'required|string|unique:penjualan,kode_penjualan',
            'file_resi'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // ← validasi file
            'dropshipper_id'  => 'nullable|exists:dropshipper,id',
            'tanggal'         => 'required|date',
            'scan_out'        => 'required',
            'total_harga'     => 'required|numeric|min:0',
            'is_draft'        => 'required',
            'items'           => 'required'
        ]);

        DB::beginTransaction();

        try {

            $items           = json_decode($request->items, true);
            $isDraft         = $request->is_draft ?? 'no';
            $nomorResi       = $items[0]['nomor_resi'] ?? null;
            $nomorPesanan    = $items[0]['nomor_pesanan'];
            $nomorTransaksi  = $items[0]['nomor_transaksi'];

            if ($nomorResi) {
                $duplicate = Penjualan::where('nomor_resi', $nomorResi)->exists();
                if ($duplicate) {
                    throw new \Exception("Nomor resi sudah digunakan, tidak boleh duplikat");
                }
            }

            $fileResiPath = null;
            $fastApiUrl = env('FASTAPI_URL');

            if ($request->hasFile('file_resi') && $request->file('file_resi')->isValid()) {
                // dd('adafile');

                $file = $request->file('file_resi');

                if ($file->getClientOriginalExtension() === 'pdf') {
                    // dd('ini file pdf');

                    // kirim ke FastAPI
                    $response = Http::attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName()
                    )->post($fastApiUrl . '/convert-pdf');

                    if (!$response->successful()) {
                        throw new \Exception("Gagal convert PDF ke gambar");
                    }

                    $images = $response->json(); // array base64 / url

                    // contoh: ambil halaman pertama saja
                    $imageBase64 = $images[0];

                    $imageData = base64_decode($imageBase64);

                    $fileName = 'resi/' . Str::uuid() . '.jpg';

                    Storage::disk('public')->put($fileName, $imageData);

                    $fileResiPath = $fileName;
                } else {
                    // kalau bukan PDF → langsung simpan
                    $fileResiPath = $file->store('resi', 'public');
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
                'file_resi'       => $fileResiPath, // ✅ simpan path, null jika tidak upload
                'created_by'      => Auth::guard('pengguna')->user()->id
            ]);

            foreach ($items as $item) {

                $barangId = $item['id'];
                $qty      = $item['qty'];
                $harga    = $item['harga_2'];
                $subtotal = $qty * $harga;

                // 2️⃣ insert penjualan_detail
                PenjualanDetail::create([
                    'penjualan_id'    => $penjualan->id,
                    'barang_id'       => $barangId,
                    'qty'             => $qty,
                    'harga'           => $harga,
                    'subtotal'        => $subtotal,
                    'nomor_resi'      => $item['nomor_resi'],
                    'nomor_pesanan'   => $item['nomor_pesanan'],
                    'nomor_transaksi' => $item['nomor_transaksi']
                ]);

                $stok        = StokBarang::where('barang_id', $barangId)->lockForUpdate()->first();
                $stokSebelum = $stok->jumlah_stok ?? 0;

                if ($isDraft === 'no' && $stokSebelum < $qty) {
                    throw new \Exception("Stok tidak cukup untuk barang ID " . $barangId);
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
                        'keterangan'     => 'Penjualan ' . $penjualan->kode_penjualan,
                        'created_by'     => Auth::guard('pengguna')->user()->id
                    ]);
                }
            }

            DB::commit();

            // return redirect()
            //     ->route('penjualan.index')
            //     ->with('success', 'Penjualan berhasil disimpan');

            return redirect()
                ->route('penjualan.struk', $penjualan->id)
                ->with('success', 'Penjualan berhasil disimpan');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan : ' . $e->getMessage());
        }
    }

    public function createMultiple()
    {
        $dropshippers = Dropshipper::all();
        $supplier = Supplier::all();
        $kode = 'PJ-' . date('Ymd') . '-' . rand(100, 999);
        return view('pages.transaksi.penjualan.create_multi', compact('dropshippers', 'supplier', 'kode'));
    }

    public function storeMultiple(Request $request)
    {
        $request->validate(['payload' => 'required|string']);

        $payload = json_decode($request->input('payload'), true);
        if (!is_array($payload) || count($payload) === 0) {
            return back()->with('error', 'Payload kosong atau tidak valid.');
        }

        $userId   = Auth::guard('pengguna')->user()->id;
        $savedIds = [];
        $skipped  = [];

        foreach ($payload as $index => $data) {

            $resiLabel = 'Resi #' . ($index + 1);
            $items     = $data['items'] ?? [];
            $nomorResi = $items[0]['nomor_resi'] ?? $data['resi'] ?? null;

            // Pre-flight checks (sama seperti sebelumnya)
            if (empty($items)) {
                $skipped[] = ['label' => $resiLabel, 'resi' => $nomorResi ?? '-', 'reason' => 'Tidak ada item.', 'type' => 'no_items'];
                continue;
            }
            if ($nomorResi && \App\Models\Penjualan::where('nomor_resi', $nomorResi)->exists()) {
                $skipped[] = [
                    'label' => $resiLabel,
                    'resi' => $nomorResi,
                    'reason' => "Nomor resi <strong>{$nomorResi}</strong> sudah ada.",
                    'type' => 'duplicate_resi'
                ];
                continue;
            }

            // ★ Simpan file resi dari base64 (jika ada)
            $fileResiPath = null;
            if (!empty($data['file_resi_base64'])) {
                try {
                    $base64   = $data['file_resi_base64'];
                    // Bersihkan prefix data:image/...;base64, jika masih ada
                    if (str_contains($base64, ',')) {
                        $base64 = explode(',', $base64)[1];
                    }
                    $imgBin   = base64_decode($base64);
                    $filename = 'resi_' . ($nomorResi ?? uniqid()) . '_' . time() . '.jpg';
                    $dir      = storage_path('app/public/resi');
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    file_put_contents($dir . '/' . $filename, $imgBin);
                    $fileResiPath = 'resi/' . $filename;   // path relatif dari storage/app/public
                } catch (\Throwable $ex) {
                    // Gagal simpan gambar tidak fatal — lanjut tanpa file
                    Log::warning("[storeMultiple] Gagal simpan file resi: " . $ex->getMessage());
                }
            }

            DB::beginTransaction();
            try {
                $isDraft       = $data['is_draft']      ?? 'no';
                $kodePenjualan = $data['kode_penjualan'] ?? ('PJL-' . now()->format('YmdHis') . '-' . ($index + 1));
                $tanggal = $data['tanggal'] ?? now()->format('Y-m-d H:i:s');
                $nomorPesanan  = $items[0]['nomor_pesanan']   ?? null;
                $nomorTransaksi = $items[0]['nomor_transaksi'] ?? null;

                if (\App\Models\Penjualan::where('kode_penjualan', $kodePenjualan)->exists()) {
                    $kodePenjualan .= '-' . ($index + 1) . '-' . now()->format('His');
                }

                $totalHargaCalc = 0;
                foreach ($items as $item) {
                    $totalHargaCalc += (int)($item['qty'] ?? 0) * (float)($item['harga_2'] ?? 0);
                }

                if ($isDraft === 'no') {
                    foreach ($items as $item) {
                        $stok    = \App\Models\StokBarang::where('barang_id', $item['id'])->lockForUpdate()->first();
                        $stokAda = $stok->jumlah_stok ?? 0;
                        if ($stokAda < (int)($item['qty'] ?? 0)) {
                            throw new \Exception("Stok tidak cukup untuk barang ID {$item['id']} (ada: {$stokAda}, butuh: {$item['qty']})");
                        }
                    }
                }

                $penjualan = \App\Models\Penjualan::create([
                    'kode_penjualan'  => $kodePenjualan,
                    'nomor_resi'      => $nomorResi,
                    'nomor_pesanan'   => $nomorPesanan,
                    'nomor_transaksi' => $nomorTransaksi,
                    'dropshipper_id'  => $data['dropshipper_id'] ?? null,
                    'tanggal'         => $tanggal,
                    'total_harga'     => $totalHargaCalc,
                    'keterangan'      => $data['keterangan']  ?? null,
                    'scan_out'        => $data['scan_out']    ?? 'pending',
                    'is_draft'        => $isDraft,
                    'file_resi'       => $fileResiPath,   // ★ simpan path
                    'created_by'      => $userId,
                ]);

                foreach ($items as $item) {
                    $barangId = $item['id'];
                    $qty      = (int)($item['qty'] ?? 0);
                    $harga    = (float)($item['harga_2'] ?? 0);

                    \App\Models\PenjualanDetail::create([
                        'penjualan_id'    => $penjualan->id,
                        'barang_id'       => $barangId,
                        'qty'             => $qty,
                        'harga'           => $harga,
                        'subtotal'        => $qty * $harga,
                        'nomor_resi'      => $item['nomor_resi']      ?? $nomorResi,
                        'nomor_pesanan'   => $item['nomor_pesanan']   ?? $nomorPesanan,
                        'nomor_transaksi' => $item['nomor_transaksi'] ?? null,
                    ]);

                    if ($isDraft === 'no') {
                        $stok        = \App\Models\StokBarang::where('barang_id', $barangId)->lockForUpdate()->first();
                        $stokSebelum = $stok->jumlah_stok ?? 0;
                        $stokSesudah = $stokSebelum - $qty;

                        \App\Models\StokBarang::updateOrCreate(
                            ['barang_id' => $barangId],
                            ['jumlah_stok' => $stokSesudah]
                        );

                        \App\Models\StokMovement::create([
                            'barang_id'      => $barangId,
                            'jenis'          => 'keluar',
                            'qty'            => $qty,
                            'stok_sebelum'   => $stokSebelum,
                            'stok_sesudah'   => $stokSesudah,
                            'referensi_tipe' => 'penjualan',
                            'referensi_id'   => $penjualan->id,
                            'keterangan'     => 'Penjualan ' . $penjualan->kode_penjualan,
                            'created_by'     => $userId,
                        ]);
                    }
                }

                DB::commit();
                $savedIds[] = $penjualan->id;
            } catch (\Exception $e) {
                DB::rollBack();
                // ★ Hapus file yang sudah terlanjur disimpan jika transaksi gagal
                if ($fileResiPath) {
                    $fullPath = storage_path('app/public/' . $fileResiPath);
                    if (file_exists($fullPath)) @unlink($fullPath);
                }
                $skipped[] = ['label' => $resiLabel, 'resi' => $nomorResi ?? '-', 'reason' => $e->getMessage(), 'type' => 'error'];
            }
        } // end foreach

        $totalSaved   = count($savedIds);
        $totalSkipped = count($skipped);

        if ($totalSaved === 0) {
            return back()->with('error', 'Semua penjualan gagal disimpan.')->with('store_errors', $skipped);
        }

        return redirect()->route('penjualan.index')
            ->with('success', "{$totalSaved} penjualan berhasil disimpan." . ($totalSkipped > 0 ? " {$totalSkipped} dilewati." : ''))
            ->with('store_warnings', $skipped);
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

        return view('pages.transaksi.penjualan.edit', compact('penjualan', 'dropshippers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_penjualan' => 'required|string|unique:penjualan,kode_penjualan,' . $id,
            'dropshipper_id' => 'nullable|exists:dropshipper,id',
            'tanggal_final' => 'required|date_format:Y-m-d H:i:s',
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

            if (
                $nomorResi && Penjualan::where('nomor_resi', $nomorResi)
                ->where('id', '!=', $penjualan->id)
                ->exists()
            ) {
                throw new \Exception("Nomor resi sudah digunakan, tidak boleh duplikat");
            }

            $penjualan->update([
                'kode_penjualan'  => $request->kode_penjualan,
                'nomor_resi'      => $nomorResi,
                'nomor_pesanan'   => $nomorPesanan,
                'nomor_transaksi' => $nomorTransaksi,
                'dropshipper_id'  => $request->dropshipper_id,
                'tanggal'         => $request->tanggal_final,
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
                        'keterangan'     => 'Hapus penjualan ' . $penjualan->kode_penjualan,
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
                'Terjadi kesalahan : ' . $e->getMessage()
            );
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
                $penjualan = Penjualan::with('detail')->findOrFail($id);

                // Rollback stok jika bukan draft
                if ($penjualan->is_draft === 'no') {
                    foreach ($penjualan->detail as $detail) {
                        $stok        = StokBarang::where('barang_id', $detail->barang_id)->lockForUpdate()->first();
                        $stokSebelum = $stok->jumlah_stok ?? 0;
                        $stokSesudah = $stokSebelum + $detail->qty;

                        StokBarang::updateOrCreate(
                            ['barang_id' => $detail->barang_id],
                            ['jumlah_stok' => $stokSesudah]
                        );

                        StokMovement::create([
                            'barang_id'      => $detail->barang_id,
                            'jenis'          => 'masuk',
                            'qty'            => $detail->qty,
                            'stok_sebelum'   => $stokSebelum,
                            'stok_sesudah'   => $stokSesudah,
                            'referensi_tipe' => 'penjualan_delete',
                            'referensi_id'   => $penjualan->id,
                            'keterangan'     => 'Bulk hapus penjualan ' . $penjualan->kode_penjualan,
                            'created_by'     => Auth::guard('pengguna')->user()->id,
                        ]);
                    }
                }

                PenjualanDetail::where('penjualan_id', $penjualan->id)->delete();
                $penjualan->delete();

                DB::commit();
                $deleted++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = "ID {$id}: " . $e->getMessage();
            }
        }

        $message = "{$deleted} penjualan berhasil dihapus.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' gagal.';
        }

        return response()->json([
            'success' => $deleted > 0,
            'message' => $message,
            'errors'  => $errors,
        ]);
    }

    public function struk($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        // Hitung nomor urut: posisi data ini di antara semua penjualan terurut by id
        $nomorUrut = Penjualan::where('id', '<=', $penjualan->id)->count();

        // Format: CHRISBALE-0001-20250429
        $nomorStruk = sprintf(
            'CHRISBALE-%04d-%s',
            $nomorUrut,
            \Carbon\Carbon::parse($penjualan->tanggal)->format('dmY')
        );

        return view('pages.transaksi.penjualan.struk', compact('penjualan', 'nomorStruk'));
    }

    public function bulkStrukDownload(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada ID yang dipilih.'], 422);
        }

        $penjualanList = Penjualan::whereIn('id', $ids)->orderBy('id')->get();

        // Siapkan data lengkap untuk setiap penjualan
        $struks = $penjualanList->map(function ($penjualan) {
            $nomorUrut   = Penjualan::where('id', '<=', $penjualan->id)->whereDate('tanggal', today())->where('dropshipper_id', $penjualan->dropshipper_id)->count();
            $dropshipper = strtoupper($penjualan->dropshipper->nama);
            $nomorStruk  = sprintf(
                $dropshipper . "-%04d-%s",
                $nomorUrut,
                \Carbon\Carbon::parse($penjualan->tanggal)->format('dmY')
            );

            $resiBase64 = null;
            $resiMime   = null;
            $resiIsPdf  = false;

            if ($penjualan->file_resi) {
                $resiPath = storage_path('app/public/' . $penjualan->file_resi);
                $ext      = strtolower(pathinfo($resiPath, PATHINFO_EXTENSION));

                if (file_exists($resiPath)) {
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $resiBase64 = base64_encode(file_get_contents($resiPath));
                        $resiMime   = match ($ext) {
                            'jpg', 'jpeg' => 'image/jpeg',
                            'png'         => 'image/png',
                            'webp'        => 'image/webp',
                            default       => 'image/jpeg',
                        };
                    } elseif ($ext === 'pdf') {
                        $resiIsPdf = true;
                    }
                }
            }

            return compact('penjualan', 'nomorStruk', 'resiBase64', 'resiMime', 'resiIsPdf');
        })->toArray();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pages.transaksi.penjualan.struk_pdf_bulk',
            compact('struks')
        )->setPaper([0, 0, 419.53, 595.28]); // A5
        Penjualan::whereIn('id', $ids)->update(['strukprint_status' => 'sudah']);

        $filename = 'struk-bulk-' . now()->format('dmY-His') . '.pdf';

        return $pdf->download($filename);
    }

    public function strukDownload($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        $nomorUrut = Penjualan::where('id', '<=', $penjualan->id)->whereDate('tanggal', today())->where('dropshipper_id', $penjualan->dropshipper_id)->count();
        $dropshipper = strtoupper($penjualan->dropshipper->nama);
        $noUrutAwal = $dropshipper . "-%04d-%s";
        // dd($nomorUrut);

        $nomorStruk = sprintf(
            $noUrutAwal,
            $nomorUrut,
            \Carbon\Carbon::parse($penjualan->tanggal)->format('dmY')
        );

        // Encode file resi ke base64 agar bisa ditampilkan di PDF
        $resiBase64 = null;
        $resiMime   = null;
        $resiIsPdf  = false;

        if ($penjualan->file_resi) {
            $resiPath = storage_path('app/public/' . $penjualan->file_resi);
            $ext      = strtolower(pathinfo($resiPath, PATHINFO_EXTENSION));

            if (file_exists($resiPath)) {
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $resiBase64 = base64_encode(file_get_contents($resiPath));
                    $resiMime   = match ($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png'         => 'image/png',
                        'webp'        => 'image/webp',
                        default       => 'image/jpeg',
                    };
                } elseif ($ext === 'pdf') {
                    $resiIsPdf = true; // PDF tidak bisa di-embed langsung di DomPDF
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.transaksi.penjualan.struk_pdf', compact(
            'penjualan',
            'nomorStruk',
            'resiBase64',
            'resiMime',
            'resiIsPdf'
        ))->setPaper([0, 0, 419.53, 595.28]); // A5
        $penjualan->update(['strukprint_status' => 'sudah']);

        $filename = 'struk-' . $nomorStruk . '.pdf';

        return $pdf->download($filename);
    }
    
    public function updateHargaCair(Request $request, $id)
    {
        $request->validate([
            'harga_cair' => 'required|numeric|min:0',
        ]);

        $penjualan = Penjualan::findOrFail($id);
        $penjualan->update([
            'harga_cair' => $request->harga_cair,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Harga cair berhasil disimpan.',
            'harga_cair' => $penjualan->harga_cair,
        ]);
    }
}
