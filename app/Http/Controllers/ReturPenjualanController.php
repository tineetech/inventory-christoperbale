<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReturPenjualanController extends Controller
{
    public function index()
    {
        $returs = ReturPenjualan::with(['penjualan', 'createdBy'])
            ->latest()
            ->paginate(20);

        return view('pages.transaksi.retur_penjualan.index', compact('returs'));
    }

    public function create($id)
    {
        $penjualan = Penjualan::with(['detail.barang.stok', 'dropshipper'])
            ->findOrFail($id);

        return view('pages.transaksi.retur_penjualan.create', compact('penjualan'));
    }

    public function store(Request $request, $penjualanId)
    {
        $request->validate([
            'tanggal_retur'               => 'required|date',
            'alasan_retur'                => 'required|string|min:5',
            'status'                      => 'required|in:pending,diproses,selesai,ditolak',
            // File datang dari masterFileInput (upload, foto, maupun video kamera)
            'file'                        => 'nullable|file|max:102400', // 100 MB max
            'items'                       => 'required|array|min:1',
            'items.*.penjualan_detail_id' => 'required|exists:penjualan_detail,id',
            'items.*.barang_id'           => 'required|exists:barang,id',
            'items.*.qty_retur'           => 'required|integer|min:1',
            'items.*.keterangan'          => 'nullable|string',
        ]);

        $penjualan = Penjualan::findOrFail($penjualanId);

        DB::beginTransaction();
        try {
            [$filePath, $fileOriginalName, $fileMime] = $this->uploadFile($request);

            $retur = ReturPenjualan::create([
                'penjualan_id'       => $penjualan->id,
                'tanggal_retur'      => $request->tanggal_retur,
                'alasan_retur'       => $request->alasan_retur,
                'status'             => $request->status,
                'file_path'          => $filePath,
                'file_original_name' => $fileOriginalName,
                'file_mime'          => $fileMime,
                'created_by'         => Auth::guard('pengguna')->id(),
            ]);

            Penjualan::where('id', $penjualan->id)->update(['is_retur' => 'yes']);

            foreach ($request->items as $item) {
                ReturPenjualanDetail::create([
                    'retur_penjualan_id'  => $retur->id,
                    'penjualan_detail_id' => $item['penjualan_detail_id'],
                    'barang_id'           => $item['barang_id'],
                    'qty_retur'           => $item['qty_retur'],
                    'keterangan'          => $item['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('penjualan.index')
                ->with('success', 'Retur penjualan berhasil disimpan.');

        } catch (\Throwable $e) {
            DB::rollBack();

            if (!empty($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan retur: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $retur = ReturPenjualan::with(['penjualan.dropshipper', 'detail.barang', 'createdBy'])
            ->findOrFail($id);

        return view('pages.transaksi.retur_penjualan.show', compact('retur'));
    }

    public function edit($id)
    {
        $retur = ReturPenjualan::with([
            'penjualan.detail.barang',
            'penjualan.dropshipper',
            'detail',
            'createdBy',
        ])->findOrFail($id);

        return view('pages.transaksi.retur_penjualan.edit', compact('retur'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_retur'               => 'required|date',
            'alasan_retur'                => 'required|string|min:5',
            'status'                      => 'required|in:pending,diproses,selesai,ditolak',
            'file'                        => 'nullable|file|max:102400',
            'hapus_file'                  => 'nullable|in:1',
            'items'                       => 'required|array|min:1',
            'items.*.penjualan_detail_id' => 'required|exists:penjualan_detail,id',
            'items.*.barang_id'           => 'required|exists:barang,id',
            'items.*.qty_retur'           => 'required|integer|min:1',
            'items.*.keterangan'          => 'nullable|string',
        ]);

        $retur = ReturPenjualan::findOrFail($id);

        DB::beginTransaction();
        try {
            $filePath         = $retur->file_path;
            $fileOriginalName = $retur->file_original_name;
            $fileMime         = $retur->file_mime;
            $oldFilePath      = null;

            // User centang "Hapus file"
            if ($request->input('hapus_file') == '1' && $retur->file_path) {
                $oldFilePath      = $retur->file_path;
                $filePath         = null;
                $fileOriginalName = null;
                $fileMime         = null;
            }

            // Ada file baru (upload / foto / video kamera)
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $oldFilePath                         = $retur->file_path;
                [$filePath, $fileOriginalName, $fileMime] = $this->uploadFile($request);
            }

            $retur->update([
                'tanggal_retur'      => $request->tanggal_retur,
                'alasan_retur'       => $request->alasan_retur,
                'status'             => $request->status,
                'file_path'          => $filePath,
                'file_original_name' => $fileOriginalName,
                'file_mime'          => $fileMime,
            ]);

            // Sync detail: hapus lama, simpan baru
            $retur->detail()->delete();

            foreach ($request->items as $item) {
                ReturPenjualanDetail::create([
                    'retur_penjualan_id'  => $retur->id,
                    'penjualan_detail_id' => $item['penjualan_detail_id'],
                    'barang_id'           => $item['barang_id'],
                    'qty_retur'           => $item['qty_retur'],
                    'keterangan'          => $item['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            // Hapus file lama dari storage setelah commit
            if ($oldFilePath && $oldFilePath !== $filePath) {
                Storage::disk('public')->delete($oldFilePath);
            }

            return redirect()
                ->route('laporan.retur.show', $retur->id)
                ->with('success', 'Retur penjualan berhasil diperbarui.');

        } catch (\Throwable $e) {
            DB::rollBack();

            if (!empty($filePath) && $filePath !== $retur->getOriginal('file_path')) {
                Storage::disk('public')->delete($filePath);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui retur: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,ditolak',
        ]);

        $retur = ReturPenjualan::findOrFail($id);
        $retur->update(['status' => $request->status]);

        return back()->with('success', 'Status retur berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE HELPER
    // ─────────────────────────────────────────────────────────

    /**
     * Upload file dari request->file('file') ke storage.
     * Berlaku untuk semua sumber: upload manual, foto kamera, video kamera.
     * (JS sudah inject blob ke masterFileInput, jadi controller cukup handle 1 input)
     */
    private function uploadFile(Request $request): array
    {
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return [null, null, null];
        }

        $file = $request->file('file');

        return [
            $file->store('retur-penjualan', 'public'),
            $file->getClientOriginalName(),
            $file->getMimeType(),
        ];
    }
}