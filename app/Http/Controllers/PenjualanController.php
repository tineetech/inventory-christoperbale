<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDraft;
use App\Models\Notifikasi;
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
    public function index(Request $request)
    {
        $query = Penjualan::with(['dropshipper', 'user', 'detail.barang.stok']);

        // ── Search ──────────────────────────────────────────
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_penjualan', 'like', "%{$search}%")
                    ->orWhere('nomor_resi', 'like', "%{$search}%")
                    ->orWhere('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhere('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('dropshipper', fn($q2) => $q2->where('nama', 'like', "%{$search}%"));
            });
        }

        // ── Filter Tanggal (default: hari ini) ──────────────
        $dateFrom = $request->date_from ?? today()->format('Y-m-d');
        $dateTo   = $request->date_to   ?? today()->format('Y-m-d');

        $from = $dateFrom . ' ' . ($request->time_from ?: '06:00');
        $to   = $dateTo   . ' ' . ($request->time_to   ?: '23:59');
        $query->where('tanggal', '>=', $from)->where('tanggal', '<=', $to);
        $query->orderBy('id', 'asc');

        // ── Filter Dropshipper ──────────────────────────────
        if ($request->filled('dropshipper')) {
            $query->whereHas('dropshipper', fn($q) => $q->where('nama', $request->dropshipper));
        }

        // ── Filter Print Status ─────────────────────────────
        if ($request->filled('print_status')) {
            if ($request->print_status === 'belum') {
                $query->where(fn($q) => $q->where('strukprint_status', '!=', 'sudah')->orWhereNull('strukprint_status'));
            } elseif ($request->print_status === 'sudah') {
                $query->where('strukprint_status', 'sudah');
            }
        }

        // ── Filter Scan Out ─────────────────────────────────
        if ($request->filled('scan_out')) {
            $query->where('scan_out', $request->scan_out);
        }

        // ── Filter Order Web ────────────────────────────────
        if ($request->order_web !== null && $request->order_web !== '') {
            $query->where('order_web', (int) $request->order_web);
        }

        // ── Sort ────────────────────────────────────────────
        $sortCol = $request->sort_col;
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['kode_penjualan', 'nomor_resi', 'nomor_pesanan', 'tanggal', 'total_harga', 'scan_out', 'is_draft', 'is_retur', 'strukprint_status'];
        if ($sortCol && in_array($sortCol, $allowedSort)) {
            $query->orderBy($sortCol, $sortDir);
        } else {
            $query->orderByDesc('id');
        }

        // ── Pagination ──────────────────────────────────────
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;
        $penjualan = $query->paginate($perPage)->withQueryString();

        $dropshippers = Dropshipper::orderBy('nama')->get();

        return view('pages.transaksi.penjualan.index', compact('penjualan', 'dropshippers'));
    }

    public function draft(Request $request)
    {
        $query = Penjualan::with(['dropshipper', 'user', 'detail.barang.stok'])
            ->where('is_draft', 'yes');

        // ── Search ──────────────────────────────────────────
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_penjualan', 'like', "%{$search}%")
                    ->orWhere('nomor_resi', 'like', "%{$search}%")
                    ->orWhere('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhere('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('dropshipper', fn($q2) => $q2->where('nama', 'like', "%{$search}%"));
            });
        }

        // ── Filter Dropshipper ──────────────────────────────
        if ($request->filled('dropshipper')) {
            $query->whereHas('dropshipper', fn($q) => $q->where('nama', $request->dropshipper));
        }

        // ── Sort ────────────────────────────────────────────
        $sortCol = $request->sort_col;
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['kode_penjualan', 'nomor_resi', 'nomor_pesanan', 'tanggal', 'total_harga', 'scan_out', 'created_at', 'updated_at'];
        if ($sortCol && in_array($sortCol, $allowedSort)) {
            $query->orderBy($sortCol, $sortDir);
        } else {
            $query->orderByDesc('created_at');
        }

        // ── Pagination ──────────────────────────────────────
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;
        $penjualanDraft = $query->paginate($perPage)->withQueryString();

        $dropshippers = Dropshipper::orderBy('nama')->get();

        return view('pages.transaksi.penjualan.draft', compact('penjualanDraft', 'dropshippers'));
    }

    public function webIndex(Request $request)
    {
        $query = Penjualan::with(['dropshipper', 'user', 'detail.barang.stok', 'address', 'shipment', 'pembayaran'])
            ->where('order_web', 1);

        // ── Search ──────────────────────────────────────────
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_penjualan', 'like', "%{$search}%")
                    ->orWhere('nomor_resi', 'like', "%{$search}%")
                    ->orWhere('nomor_pesanan', 'like', "%{$search}%")
                    ->orWhere('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('address', fn($q2) => $q2->where('recipient_name', 'like', "%{$search}%"))
                    ->orWhereHas('dropshipper', fn($q2) => $q2->where('nama', 'like', "%{$search}%"));
            });
        }

        // ── Filter Tanggal (default: hari ini) ──────────────
        $dateFrom = $request->date_from ?? today()->format('Y-m-d');
        $dateTo   = $request->date_to   ?? today()->format('Y-m-d');

        $from = $dateFrom . ' ' . ($request->time_from ?: '06:00');
        $to   = $dateTo   . ' ' . ($request->time_to   ?: '23:59');
        $query->where('tanggal', '>=', $from)->where('tanggal', '<=', $to);

        // ── Filter Status Web ───────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Filter Scan Out ─────────────────────────────────
        if ($request->filled('scan_out')) {
            $query->where('scan_out', $request->scan_out);
        }

        // ── Sort ────────────────────────────────────────────
        $sortCol = $request->sort_col;
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['kode_penjualan', 'nomor_resi', 'nomor_pesanan', 'tanggal', 'total_harga', 'status', 'scan_out', 'is_retur'];
        if ($sortCol && in_array($sortCol, $allowedSort)) {
            $query->orderBy($sortCol, $sortDir);
        } else {
            $query->orderByDesc('id');
        }

        // ── Pagination ──────────────────────────────────────
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;
        $penjualan = $query->paginate($perPage)->withQueryString();

        // Draft pesanan web yang menunggu konfirmasi pembayaran
        $drafts = PenjualanDraft::with(['items.barang.stok', 'address', 'shipment', 'pembayaran', 'creator'])
            ->where('order_web', 1)
            ->orderByDesc('id')
            ->get();

        $dropshippers = Dropshipper::orderBy('nama')->get();

        return view('pages.transaksi.penjualan.web', compact('penjualan', 'drafts', 'dropshippers'));
    }

    public function draftReleasePreview(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $penjualans = Penjualan::with(['dropshipper', 'detail.barang.stok'])
            ->whereIn('id', $request->ids)
            ->where('is_draft', 'yes')
            ->get();

        $normal = [];
        $konflik = [];

        // ── Klasifikasi normal / konflik ────────────────────────────────
        foreach ($penjualans as $pj) {
            $items = [];
            foreach ($pj->detail as $d) {
                $stok = (int) ($d->barang->stok->jumlah_stok ?? 0);
                $items[] = [
                    'barang_id' => $d->barang_id,
                    'sku'       => $d->barang->sku ?? '-',
                    'nama'      => $d->barang->nama_barang ?? '-',
                    'qty'       => (int) $d->qty,
                    'stok'      => $stok,
                    'conflict'  => $d->qty > $stok,
                ];
            }

            $entry = [
                'id'            => $pj->id,
                'kode'          => $pj->kode_penjualan,
                'tanggal'       => $pj->tanggal ? \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y') : '-',
                'dropshipper'   => $pj->dropshipper->nama ?? '-',
                'total_harga'   => $pj->total_harga ?? 0,
                'items'         => $items,
                'ada_konflik'   => collect($items)->contains('conflict', true),
            ];

            if ($entry['ada_konflik']) {
                $konflik[] = $entry;
            } else {
                $normal[] = $entry;
            }
        }

        // ── Ambil barang UNIK yang perlu update stok (hanya qty > stok) ──
        // jika barang sama muncul di >1 penjualan konflik,
        // ambil yang qty-nya PALING BESAR saja.
        $barangMap = [];
        foreach ($konflik as $entry) {
            foreach ($entry['items'] as $item) {
                if (!$item['conflict']) continue;
                $bid = $item['barang_id'];
                if (!isset($barangMap[$bid]) || $item['qty'] > $barangMap[$bid]['qty']) {
                    $barangMap[$bid] = [
                        'barang_id'  => $bid,
                        'sku'        => $item['sku'],
                        'nama'       => $item['nama'],
                        'qty'        => $item['qty'],
                        'stok'       => $item['stok'],
                        'penjualan_id' => $entry['id'],
                        'kode'       => $entry['kode'],
                    ];
                }
            }
        }

        // Keperluan input form: tandai item di penjualan mana yang jadi "pemilik" input
        foreach ($konflik as &$entry) {
            foreach ($entry['items'] as &$item) {
                $item['input_utama'] = false;
                if ($item['conflict'] && ($barangMap[$item['barang_id']]['penjualan_id'] ?? null) === $entry['id']) {
                    $item['input_utama'] = true;
                }
            }
            unset($item);
        }
        unset($entry);

        return response()->json([
            'success' => true,
            'counts'  => [
                'normal'  => count($normal),
                'konflik' => count($konflik),
            ],
            'normal'        => $normal,
            'konflik'       => $konflik,
            'barang_update' => array_values($barangMap),
        ]);
    }

    public function draftReleaseProcess(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
            'items' => 'nullable|array',
        ]);

        $penyans = Penjualan::with(['detail.barang.stok'])
            ->whereIn('id', $request->ids)
            ->where('is_draft', 'yes')
            ->get();

        // input form: [ { penjualan_id, barang_id, qty, jumlah_stok } ]
        $inputs = collect($request->items ?? [])->keyBy(fn($i) => $i['penjualan_id'] . '-' . $i['barang_id']);

        // Barang unik yang conflict (qty > stok); jika muncul di beberapa penjualan,
        // hanya penjualan dengan qty PALING BESAR yang menjadi "pemilik" input stok.
        $barangMap = [];
        foreach ($penyans as $pj) {
            foreach ($pj->detail as $d) {
                $stok = (int) ($d->barang->stok->jumlah_stok ?? 0);
                if ($d->qty <= $stok) continue;
                $bid = $d->barang_id;
                if (!isset($barangMap[$bid]) || $d->qty > $barangMap[$bid]['qty']) {
                    $barangMap[$bid] = [
                        'barang_id'    => $bid,
                        'penjualan_id' => $pj->id,
                        'qty'          => (int) $d->qty,
                    ];
                }
            }
        }

        $normalClear   = 0;
        $konflikClear  = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($penyans as $pj) {
                $adaConflict = false;
                foreach ($pj->detail as $d) {
                    $stok = (int) ($d->barang->stok->jumlah_stok ?? 0);
                    if ($d->qty > $stok) {
                        $adaConflict = true;
                        break;
                    }
                }

                if (!$adaConflict) {
                    // ── PENJUALAN NORMAL → langsung update dam is_draft=no
                    $pj->update(['is_draft' => 'no']);
                    $normalClear++;
                    continue;
                }

                $konflikClear++;

                // ── PENJUALAN KONFLIK → proses talahan stok ─────────────
                foreach ($pj->detail as $d) {
                    $bid = $d->barang_id;

                    // Hanya proses barang yang pemiliknya penjualan ini (qty terbesar).
                    // Duplikat di penjualan lain otomatis diabaikan.
                    if (($barangMap[$bid]['penjualan_id'] ?? null) !== $pj->id) continue;

                    $stokSaatIni = (int) ($d->barang->stok->jumlah_stok ?? 0);

                    // Sisa barang yang conflict hanya karena (qty > stok)
                    if ($d->qty <= $stokSaatIni) continue;

                    $key = $pj->id . '-' . $bid;
                    $input = $inputs->get($key);

                    $qtyDipakai = (int) ($input['qty'] ?? $d->qty);
                    $stokBaru   = isset($input['jumlah_stok']) ? (int) $input['jumlah_stok'] : 0;

                    // Skip kalau input stok terbaru kosong / tidak valid
                    if ($qtyDipakai <= 0 || $stokBaru <= 0 || $stokBaru < $qtyDipakai) {
                        continue;
                    }

                    $stokSebelum = $stokSaatIni;
                    $stokSesudah = $stokBaru; // stok terbaru dari input form
                    $selisih     = $stokSesudah - $stokSebelum;

                    StokBarang::updateOrCreate(
                        ['barang_id' => $bid],
                        ['jumlah_stok' => $stokSesudah]
                    );

                    StokMovement::create([
                        'barang_id'      => $bid,
                        'jenis'          => 'adjustment',
                        'qty'            => $selisih,
                        'stok_sebelum'   => $stokSebelum,
                        'stok_sesudah'   => $stokSesudah,
                        'referensi_tipe' => 'penjualan_draft_release',
                        'referensi_id'   => $pj->id,
                        'keterangan'     => 'Penambahan stok saat keluarkan draft ' . $pj->kode_penjualan,
                        'created_by'     => Auth::guard('pengguna')->user()->id,
                    ]);
                }

                $pj->update(['is_draft' => 'no']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success'       => true,
            'message'       => "{$normalClear} penjualan normal dan {$konflikClear} penjualan konflik berhasil dikeluarkan dari draft.",
            'normal_clear'  => $normalClear,
            'konflik_clear' => $konflikClear,
            'errors'        => $errors,
        ]);
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
            'status'         => 'nullable|in:proses,packing,dikirim,selesai',
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
                'status'          => in_array($request->status, ['proses', 'packing', 'dikirim', 'selesai'])
                    ? $request->status
                    : ($penjualan->status ?? 'proses'),
                'keterangan'      => $request->keterangan,
            ]);

            // ★ Simpan/update file resi dari base64 (jika user upload file baru)
            if (!empty($request->file_resi_base64)) {
                try {
                    $base64 = $request->file_resi_base64;
                    if (str_contains($base64, ',')) {
                        $base64 = explode(',', $base64)[1];
                    }
                    $imgBin   = base64_decode($base64);
                    $filename = 'resi_' . ($nomorResi ?? uniqid()) . '_' . time() . '.jpg';
                    $dir      = storage_path('app/public/resi');
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    file_put_contents($dir . '/' . $filename, $imgBin);

                    $penjualan->update(['file_resi' => 'resi/' . $filename]);
                } catch (\Throwable $ex) {
                    Log::warning("[update] Gagal simpan file resi: " . $ex->getMessage());
                }
            }

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

            $penjualanDate = \Carbon\Carbon::parse($penjualan->tanggal)->format('Y-m-d');

            // Jika edit dibuka dari halaman Penjualan Web → kembali ke Penjualan Web
            if ($request->input('redirect_to') === 'web') {
                return redirect()->route('penjualan.web')->with('success', 'Penjualan berhasil diupdate');
            }

            return redirect()->route('penjualan.index', [
                'date_from' => $penjualanDate,
                'date_to'   => $penjualanDate,
            ])->with('success', 'Penjualan berhasil diupdate');
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

        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $penjualanList = Penjualan::with('dropshipper')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn($p) => array_search($p->id, $ids))
            ->values();

        $groups = $penjualanList
            ->map(fn($p) => [
                'tanggal'        => \Carbon\Carbon::parse($p->tanggal)->format('Y-m-d'),
                'dropshipper_id' => $p->dropshipper_id,
            ])
            ->unique(fn($g) => $g['tanggal'] . '_' . $g['dropshipper_id'])
            ->values();

        $nomorUrutMap = [];
        foreach ($groups as $group) {
            $groupKey = $group['tanggal'] . '_' . $group['dropshipper_id'];
            $allIds   = Penjualan::whereDate('tanggal', $group['tanggal'])
                ->where('dropshipper_id', $group['dropshipper_id'])
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            foreach ($allIds as $idx => $pid) {
                $nomorUrutMap[$groupKey][$pid] = $idx + 1;
            }
        }

        $struks = $penjualanList->map(function ($penjualan) use ($nomorUrutMap) {
            $tanggalKey = \Carbon\Carbon::parse($penjualan->tanggal)->format('Y-m-d');
            $groupKey   = $tanggalKey . '_' . $penjualan->dropshipper_id;
            $nomorUrut  = $nomorUrutMap[$groupKey][$penjualan->id] ?? 1;
            $resiChunks = [];

            $dropshipper = strtoupper($penjualan->dropshipper->nama ?? '');
            $nomorStruk  = sprintf(
                '%s-%04d-%s',
                $dropshipper,
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
                        $resiBase64 = $this->compressImageToBase64($resiPath, $ext);
                        $resiChunks = $this->splitImageToPages($resiBase64);
                        $resiMime   = 'image/jpeg';
                    } elseif ($ext === 'pdf') {
                        $resiIsPdf = true;
                        $resiChunks = [];
                    }
                }
            }

            return compact('penjualan', 'nomorStruk', 'resiChunks', 'resiMime', 'resiIsPdf');
        })->toArray();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pages.transaksi.penjualan.struk_pdf_bulk',
            compact('struks')
        )
            ->setPaper([0, 0, 419.53, 595.28])
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'Arial',
                'dpi'                  => 72,
            ]);

        Penjualan::whereIn('id', $ids)->update(['strukprint_status' => 'sudah']);

        $filename = 'struk-bulk-' . now()->format('dmY-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Split gambar resi tinggi menjadi beberapa halaman A5,
     * TANPA memotong tepat di tengah (menghindari QR/barcode kepotong).
     *
     * Strategi:
     * 1. Hitung target tinggi per halaman berdasarkan rasio kertas A5 (lebar tetap 100%).
     * 2. Kalau gambar muat dalam 1 halaman -> kembalikan 1 chunk saja (tidak displit).
     * 3. Kalau harus displit, cari "safe zone" di sekitar titik potong ideal
     *    dengan menganalisis baris piksel yang paling "kosong" (dominan putih/polos),
     *    supaya potongan jatuh di celah antar elemen, bukan di tengah QR/barcode.
     */
    private function splitImageToPages(string $base64Image, int $safeZoneSearchPx = 150): array
{
    $imgData = base64_decode($base64Image);
    $srcImg  = @imagecreatefromstring($imgData);

    if (!$srcImg) {
        return [$base64Image];
    }

    // ★ Trim whitespace kosong di bagian bawah gambar dulu,
    //   supaya tidak salah split gara-gara margin putih dari foto/scan.
    $srcImg = $this->trimTrailingWhitespace($srcImg);

    $origWidth  = imagesx($srcImg);
    $origHeight = imagesy($srcImg);

    $paperWidthPt  = 419.53;
    $paperHeightPt = 595.28;
    $paddingPt     = 20 * 2;
    $footerReserve = 60;

    $usableWidthPt  = $paperWidthPt - $paddingPt;
    $usableHeightPt = $paperHeightPt - $paddingPt - $footerReserve;

    $scale = $origWidth / $usableWidthPt;
    $maxHeightPerPagePx = (int) round($usableHeightPt * $scale);

    // ★ Tambah toleransi 10% — overflow sedikit di bawah threshold
    //   tidak perlu dipaksa split, cukup discale lebih kecil oleh browser/DomPDF.
    $tolerance = $maxHeightPerPagePx * 0.10;

    if ($origHeight <= $maxHeightPerPagePx + $tolerance) {
        ob_start();
        imagejpeg($srcImg, null, 90);
        $trimmedData = ob_get_clean();
        imagedestroy($srcImg);
        return [base64_encode($trimmedData)];
    }

    $chunks = [];
    $currentY = 0;

    while ($currentY < $origHeight) {
        $idealCutY = min($currentY + $maxHeightPerPagePx, $origHeight);

        if ($idealCutY < $origHeight) {
            $cutY = $this->findSafeCutLine($srcImg, $origWidth, $idealCutY, $safeZoneSearchPx, $currentY);
        } else {
            $cutY = $origHeight;
        }

        $chunkHeight = $cutY - $currentY;
        if ($chunkHeight <= 0) {
            $chunkHeight = $origHeight - $currentY;
            $cutY = $origHeight;
        }

        $chunkImg = imagecreatetruecolor($origWidth, $chunkHeight);
        $white = imagecolorallocate($chunkImg, 255, 255, 255);
        imagefill($chunkImg, 0, 0, $white);

        imagecopy($chunkImg, $srcImg, 0, 0, 0, $currentY, $origWidth, $chunkHeight);

        ob_start();
        imagejpeg($chunkImg, null, 90);
        $chunkData = ob_get_clean();
        imagedestroy($chunkImg);

        $chunks[] = base64_encode($chunkData);

        $currentY = $cutY;
    }

    imagedestroy($srcImg);

    return $chunks;
}

/**
 * Pangkas baris-baris kosong/putih di bagian PALING BAWAH gambar.
 * Berguna untuk foto/scan resi yang punya margin putih ekstra di bawah,
 * supaya tidak salah dianggap "perlu displit" padahal kontennya sudah habis.
 *
 * Menyisakan sedikit padding (defaultnya 15px) supaya tidak terlalu mepet.
 */
private function trimTrailingWhitespace($img, int $keepPaddingPx = 15)
{
    $width  = imagesx($img);
    $height = imagesy($img);

    $lastContentY = $height - 1;

    // Scan dari bawah ke atas, cari baris pertama yang BUKAN putih polos
    for ($y = $height - 1; $y >= 0; $y--) {
        if (!$this->isRowBlankWhite($img, $width, $y)) {
            $lastContentY = $y;
            break;
        }
    }

    $newHeight = min($height, $lastContentY + 1 + $keepPaddingPx);

    // Kalau tidak ada whitespace signifikan untuk di-trim, kembalikan apa adanya
    if ($newHeight >= $height - 5) {
        return $img;
    }

    $trimmed = imagecreatetruecolor($width, $newHeight);
    $white = imagecolorallocate($trimmed, 255, 255, 255);
    imagefill($trimmed, 0, 0, $white);
    imagecopy($trimmed, $img, 0, 0, 0, 0, $width, $newHeight);

    imagedestroy($img);

    return $trimmed;
}

/**
 * Cek apakah satu baris piksel adalah putih polos (blank),
 * bukan sekadar "variansi rendah" (yang bisa saja abu-abu/hitam polos).
 */
private function isRowBlankWhite($img, int $width, int $y): bool
{
    $step = max(1, (int) ($width / 40));
    $sumBrightness = 0;
    $count = 0;
    $maxDelta = 0;

    for ($x = 0; $x < $width; $x += $step) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $brightness = ($r + $g + $b) / 3;

        $sumBrightness += $brightness;
        $count++;
        $maxDelta = max($maxDelta, abs($r - $g), abs($g - $b), abs($r - $b));
    }

    if ($count === 0) return true;

    $avgBrightness = $sumBrightness / $count;

    // Dianggap "putih polos" kalau rata-rata terang (>245) dan hampir tidak ada variasi warna
    return $avgBrightness > 245 && $maxDelta < 10;
}

    /**
     * Cari baris (y) paling "aman" untuk dipotong di sekitar idealCutY,
     * dengan mendeteksi baris yang paling homogen/polos (variasi warna rendah),
     * karena QR code/barcode/teks biasanya punya variasi piksel tinggi,
     * sementara area kosong/putih di antar section punya variasi rendah.
     */
    private function findSafeCutLine($img, int $width, int $idealCutY, int $searchRangePx, int $minY): int
    {
        $height = imagesy($img);
        $searchStart = max($minY + 10, $idealCutY - $searchRangePx);
        $searchEnd   = min($height - 10, $idealCutY + $searchRangePx);

        if ($searchStart >= $searchEnd) {
            return $idealCutY;
        }

        $bestY = $idealCutY;
        $lowestVariance = PHP_INT_MAX;

        // Sampling tiap baris (step 2px biar cepat) di rentang pencarian
        for ($y = $searchStart; $y <= $searchEnd; $y += 2) {
            $variance = $this->rowVariance($img, $width, $y);

            if ($variance < $lowestVariance) {
                $lowestVariance = $variance;
                $bestY = $y;
            }

            // Kalau ketemu baris yang benar-benar polos (hampir putih rata),
            // langsung pakai itu, tidak perlu cari lebih jauh.
            if ($variance < 50) {
                return $y;
            }
        }

        return $bestY;
    }

    /**
     * Hitung variansi warna piksel dalam satu baris horizontal.
     * Baris dengan variansi rendah = polos/kosong (aman untuk dipotong).
     * Baris dengan variansi tinggi = ada elemen visual (teks, QR, garis) di situ.
     */
    private function rowVariance($img, int $width, int $y): float
    {
        $samples = [];
        $step = max(1, (int) ($width / 40)); // ambil ~40 sample titik per baris

        for ($x = 0; $x < $width; $x += $step) {
            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $samples[] = ($r + $g + $b) / 3;
        }

        if (empty($samples)) {
            return 0;
        }

        $mean = array_sum($samples) / count($samples);
        $variance = 0;
        foreach ($samples as $s) {
            $variance += ($s - $mean) ** 2;
        }

        return $variance / count($samples);
    }

    /**
     * Compress & resize gambar sebelum di-embed ke PDF.
     * Max width 800px, quality JPEG 75% — cukup untuk struk cetak.
     */
    private function compressImageToBase64(string $path, string $ext): string
    {
        // Kalau GD tidak tersedia, fallback ke raw
        if (!extension_loaded('gd')) {
            return base64_encode(file_get_contents($path));
        }

        $src = match ($ext) {
            'png'  => imagecreatefrompng($path),
            'webp' => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };

        if (!$src) {
            return base64_encode(file_get_contents($path));
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxW  = 800;

        // Resize kalau lebar > 800px
        if ($origW > $maxW) {
            $newH  = (int) round($origH * $maxW / $origW);
            $dst   = imagecreatetruecolor($maxW, $newH);

            // Preserve transparency untuk PNG
            if ($ext === 'png') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $dst;
        }

        ob_start();
        imagejpeg($src, null, 75); // selalu output JPEG, quality 75
        $data = ob_get_clean();
        imagedestroy($src);

        return base64_encode($data);
    }


    public function strukDownload($id)
    {
        $penjualan = Penjualan::findOrFail($id);

        // Wajib ada dropshipper agar nomor struk valid
        if (is_null($penjualan->dropshipper_id)) {
            return redirect()->back()->with('error', 'Penjualan "' . $penjualan->kode_penjualan . '" belum memiliki dropshipper. Silakan tambahkan dropshipper terlebih dahulu.');
        }

        $nomorUrut = Penjualan::where('id', '<=', $penjualan->id)->whereDate('tanggal', today())->where('dropshipper_id', $penjualan->dropshipper_id)->count();
        $dropshipper = strtoupper($penjualan->dropshipper->nama);
        $noUrutAwal = $dropshipper . "-%04d-%s";

        $nomorStruk = sprintf(
            $noUrutAwal,
            $nomorUrut,
            \Carbon\Carbon::parse($penjualan->tanggal)->format('dmY')
        );

        // Encode file resi ke base64 agar bisa ditampilkan di PDF
        $resiChunks = [];
        $resiMime   = null;
        $resiIsPdf  = false;

        if ($penjualan->file_resi) {
            $resiPath = storage_path('app/public/' . $penjualan->file_resi);
            $ext      = strtolower(pathinfo($resiPath, PATHINFO_EXTENSION));

            if (file_exists($resiPath)) {
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    // Compress + split sama seperti bulk
                    $resiBase64 = $this->compressImageToBase64($resiPath, $ext);
                    $resiChunks = $this->splitImageToPages($resiBase64);
                    $resiMime   = 'image/jpeg';
                } elseif ($ext === 'pdf') {
                    $resiIsPdf = true; // PDF tidak bisa di-embed langsung di DomPDF
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.transaksi.penjualan.struk_pdf', compact(
            'penjualan',
            'nomorStruk',
            'resiChunks',
            'resiMime',
            'resiIsPdf'
        ))->setPaper([0, 0, 419.53, 595.28])->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'Arial',
            'dpi'                  => 72,
        ]);
        $penjualan->update(['strukprint_status' => 'sudah']);

        $filename = 'struk-' . $nomorStruk . '.pdf';

        return $pdf->download($filename);
    }

    public function updateHargaCair(Request $request, $id)
    {
        $request->validate([
            'harga_cair' => 'required|numeric',
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

    public function updateTransit(Request $request, $id)
    {
        $penjualan = Penjualan::findOrFail($id);

        if ($penjualan->keterangan === 'sedang transit') {
            $penjualan->update(['keterangan' => null]);
            $transit = false;
        } else {
            $penjualan->update(['keterangan' => 'sedang transit']);
            $transit = true;
        }

        return response()->json([
            'success' => true,
            'transit' => $transit,
            'message' => $transit ? 'Status transit diaktifkan.' : 'Status transit dinonaktifkan.',
        ]);
    }

    /**
     * Perbarui dropshipper pada penjualan web (via popup dari halaman penjualan web).
     * Mendukung upload file resi (image / pdf yang sudah dikonversi jadi image di client):
     * file discan dulu via FastAPI (scan-resi-multiple-async) untuk mengambil nomor resi &
     * nomor pesanan, divalidasi duplikatnya, baru file & data penjualan disimpan.
     */
    public function updateDropshipper(Request $request, $id)
    {
        $request->validate([
            'dropshipper_id' => 'nullable|exists:dropshipper,id',
            'file_resi'      => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:20480',
            'mode'           => 'nullable|string|in:shopee,tiktok',
        ]);

        $penjualan = Penjualan::findOrFail($id);

        if (!$request->filled('dropshipper_id') && !$request->hasFile('file_resi')) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih dropshipper atau upload file resi terlebih dahulu.',
            ], 422);
        }

        $update = [];
        if ($request->filled('dropshipper_id')) {
            $update['dropshipper_id'] = $request->dropshipper_id;
        }

        // ── File resi: scan dulu (OCR) → validasi duplikat → baru simpan ──────
        if ($request->hasFile('file_resi') && $request->file('file_resi')->isValid()) {
            set_time_limit(300);

            $file = $request->file('file_resi');

            try {
                // Step 1 — submit job OCR async
                $response = Http::timeout(30)
                    ->attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName()
                    )
                    ->post($this->fastApiUrl() . '/scan-resi-multiple-async', array_filter([
                        'mode' => $request->input('mode', 'shopee'),
                    ]));

                if ($response->failed()) {
                    throw new \Exception('OCR service error: HTTP ' . $response->status());
                }

                $jobId = $response->json()['job_id'] ?? null;
                if (!$jobId) {
                    throw new \Exception('OCR service tidak mengembalikan job_id.');
                }

                // Step 2 — polling status job sampai done (maks ~120 detik)
                $ocrData = null;
                for ($i = 0; $i < 40; $i++) {
                    sleep(3);

                    $poll = Http::timeout(120)->get($this->fastApiUrl() . '/job-status/' . $jobId);
                    if ($poll->failed()) {
                        continue;
                    }

                    $payload = $poll->json();

                    if (isset($payload['error'])) {
                        throw new \Exception($payload['error']);
                    }

                    if (($payload['status'] ?? '') === 'done') {
                        $ocrData = $payload['data'] ?? [];
                        break;
                    }
                }

                if ($ocrData === null) {
                    throw new \Exception('Timeout menunggu hasil scan resi. Coba lagi.');
                }

                // Step 3 — ambil nomor resi & nomor pesanan (halaman pertama yang terbaca)
                $nomorResi    = null;
                $nomorPesanan = null;
                foreach ($ocrData as $page) {
                    if (!$nomorResi && !empty($page['resi'])) {
                        $nomorResi = $page['resi'];
                    }
                    if (!$nomorPesanan && !empty($page['order_id'])) {
                        $nomorPesanan = $page['order_id'];
                    }
                    if ($nomorResi && $nomorPesanan) {
                        break;
                    }
                }

                // Step 4 — VALIDASI DUPLIKAT: tolak & jangan proses apa pun
                if ($nomorResi) {
                    $duplicate = Penjualan::where('nomor_resi', $nomorResi)
                        ->where('id', '!=', $penjualan->id)
                        ->first();

                    if ($duplicate) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Nomor resi "' . $nomorResi . '" sudah digunakan oleh penjualan "'
                                . $duplicate->kode_penjualan . '". File tidak diproses.',
                            'duplicate' => true,
                        ], 422);
                    }
                }

                // Lolos validasi → simpan/replace file resi
                if ($penjualan->file_resi) {
                    Storage::disk('public')->delete($penjualan->file_resi);
                }
                $path = $file->store('resi', 'public');
                $update['file_resi'] = $path;

                if ($nomorResi) {
                    $update['nomor_resi'] = $nomorResi;
                }
                if ($nomorPesanan) {
                    $update['nomor_pesanan'] = $nomorPesanan;
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'OCR service tidak dapat dijangkau.',
                ], 503);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        $penjualan->update($update);

        // Update juga nomor resi & nomor pesanan di penjualan_detail
        $detailUpdate = array_intersect_key($update, array_flip(['nomor_resi', 'nomor_pesanan']));
        if (!empty($detailUpdate)) {
            PenjualanDetail::where('penjualan_id', $penjualan->id)->update($detailUpdate);
        }

        $pesan = [];
        if ($request->filled('dropshipper_id')) {
            $pesan[] = 'dropshipper';
        }
        if ($penjualan->file_resi) {
            $pesan[] = 'file resi';
        }
        if (!empty($update['nomor_resi'])) {
            $pesan[] = 'no. resi (' . $update['nomor_resi'] . ')';
        }
        if (!empty($update['nomor_pesanan'])) {
            $pesan[] = 'no. pesanan (' . $update['nomor_pesanan'] . ')';
        }

        return response()->json([
            'success' => true,
            'message' => 'Penjualan "' . $penjualan->kode_penjualan . '" berhasil diperbarui: ' . implode(', ', $pesan) . '.',
            'nomor_resi'    => $update['nomor_resi'] ?? null,
            'nomor_pesanan' => $update['nomor_pesanan'] ?? null,
        ]);
    }

    private function fastApiUrl(): string
    {
        return env('FASTAPI_URL');
    }

    /**
     * Konfirmasi pembayaran draft web (paid_confirmation -> paid),
     * lalu pindahkan penjualan_draft + items ke penjualan + penjualan_detail
     * dengan status proses "packing".
     */
    public function confirmDraftPayment(Request $request, $id)
    {
        $draft = PenjualanDraft::with(['items', 'address', 'shipment', 'pembayaran'])->findOrFail($id);

        if (!$draft->pembayaran || $draft->pembayaran->status !== 'paid_confirmation') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran untuk draft ini tidak sedang menunggu konfirmasi.',
            ], 422);
        }

        // Pastikan kode penjualan unik di tabel penjualan
        $kode = $draft->kode_penjualan;
        if (Penjualan::where('kode_penjualan', $kode)->exists()) {
            $kode = $kode . '-' . now()->format('His');
        }

        DB::beginTransaction();

        try {
            $penjualan = Penjualan::create([
                'kode_penjualan' => $kode,
                // Tanggal memakai waktu saat konfirmasi pembayaran (jam juga)
                'tanggal'        => now(),
                'total_harga'    => $draft->total_harga,
                'harga_discount' => $draft->harga_discount,
                'shipping_cost'  => $draft->shipping_cost,
                'subtotal_harga' => $draft->subtotal_harga,
                'keterangan'     => $draft->keterangan,
                'status'         => 'packing',
                'order_web'      => 1,
                'scan_out'       => 'pending',
                'is_draft'       => 'no',
                'created_by'     => $draft->created_by ?? Auth::guard('pengguna')->id(),
            ]);

            foreach ($draft->items as $item) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id'    => $item->barang_id,
                    'qty'          => $item->qty,
                    'harga'        => $item->harga,
                    'subtotal'     => $item->subtotal,
                ]);
            }

            // Pindahkan relasi address, shipment & pembayaran ke penjualan
            if ($draft->address) {
                $draft->address->update(['penjualan_id' => $penjualan->id, 'penjualan_draft_id' => null]);
            }
            if ($draft->shipment) {
                $draft->shipment->update(['penjualan_id' => $penjualan->id, 'penjualan_draft_id' => null]);
            }

            $draft->pembayaran->update([
                'status'             => 'paid',
                'paid_at'            => now(),
                'penjualan_id'       => $penjualan->id,
                'penjualan_draft_id' => null,
            ]);

            // Hapus draft beserta items-nya (data sudah dipindahkan)
            $draft->items()->delete();
            $draft->delete();

            // Notifikasi: pembayaran telah selesai dilakukan untuk penjualan ini
            Notifikasi::create([
                'judul'      => 'Pembayaran Dikonfirmasi',
                'isi'        => 'Pembayaran untuk penjualan "' . $kode . '" telah selesai dilakukan. Pesanan kini berstatus packing.',
                'tipe'       => 'pembayaran',
                'link'       => route('penjualan.web'),
                'payload'    => [
                    'penjualan_id'   => $penjualan->id,
                    'kode_penjualan' => $kode,
                    'total_harga'    => (float) $penjualan->total_harga,
                    'status'         => $penjualan->status,
                ],
                'user_id'    => null, // broadcast ke semua pengguna
                'created_by' => Auth::guard('pengguna')->id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran "' . $kode . '" dikonfirmasi. Data dipindahkan ke penjualan dengan status packing.',
        ]);
    }
}
