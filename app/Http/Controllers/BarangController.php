<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StokBarang;
use App\Models\StokMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class BarangController extends Controller
{
    // public function index()
    // {
    //     $barang = Barang::with('satuan', 'stok')->get();
    //     return view('pages.master.barang.index', compact('barang'));
    // }
    public function index(Request $request)
    {
        $query = Barang::with('satuan', 'stok');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortColumn = $request->get('sort', 'nama_barang');
        $sortDir    = $request->get('direction', 'asc');
        $allowed    = ['sku', 'nama_barang', 'harga_1', 'harga_2', 'keterangan'];

        if (in_array($sortColumn, $allowed)) {
            $query->orderBy($sortColumn, $sortDir);
        }

        $perPage = in_array($request->get('per_page'), [10, 25, 50, 100])
            ? $request->get('per_page')
            : 10;

        $barang = $query->paginate($perPage)->withQueryString();

        return view('pages.master.barang.index', compact('barang'));
    }

    public function create()
    {
        $satuan = Satuan::all();
        return view('pages.master.barang.create', compact('satuan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku'           => 'required|string|unique:barang,sku',
            'nama_barang'   => 'required|string',
            'kategori'   => 'nullable|string',
            'satuan_id'     => 'required|exists:satuan,id',
            'harga_1'       => 'required|numeric|min:0',
            'harga_2'       => 'nullable|numeric|min:0',
            'stok_minimum'  => 'nullable|integer|min:0',
            'stok_awal'     => 'required|integer|min:0',
            'keterangan'    => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            // 1. Simpan barang
            $barang = Barang::create([
                'sku'          => $request->sku,
                'nama_barang'  => $request->nama_barang,
                'kategori'  => $request->kategori,
                'satuan_id'    => $request->satuan_id,
                'harga_1'      => $request->harga_1,
                'harga_2'      => $request->harga_2,
                'stok_minimum' => $request->stok_minimum ?? 0,
                'keterangan'   => $request->keterangan,
            ]);

            // 2. Simpan stok awal
            StokBarang::create([
                'barang_id' => $barang->id,
                'jumlah_stok' => $request->stok_awal
            ]);

            // 3. Catat movement stok
            StokMovement::create([
                'barang_id'      => $barang->id,
                'jenis'          => 'masuk',
                'qty'            => $request->stok_awal,
                'stok_sebelum'   => 0,
                'stok_sesudah'   => $request->stok_awal,
                'referensi_tipe' => 'stok_awal',
                'referensi_id'   => $barang->id,
                'keterangan'     => 'Input stok awal saat membuat barang',
                'created_by'     => Auth::guard('pengguna')->user()->id
            ]);

            DB::commit();

            return redirect()->route('barang.index')
                ->with('success', 'Barang dan stok awal berhasil ditambahkan.');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function show(string $id)
    {
        $barang = Barang::with('satuan', 'stok', 'stokMovement')->findOrFail($id);
        return view('pages.master.barang.show', compact('barang'));
    }

    public function edit(string $id)
    {
        $barang = Barang::findOrFail($id);
        $stok = StokBarang::where('barang_id', $id)->first();
        $satuan = Satuan::all();
        return view('pages.master.barang.edit', compact('barang', 'satuan', 'stok'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'sku'          => 'required|string|max:50|unique:barang,sku,' . $id,
            'nama_barang'  => 'required|string|max:100',
            'satuan_id'    => 'required|exists:satuan,id',
            'harga_1'      => 'required|numeric|min:0',
            'harga_2'      => 'nullable|numeric|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'kategori'   => 'nullable|string',
            'keterangan'   => 'nullable|string',
        ]);

        $data = $request->all();

        $barang = Barang::findOrFail($id);
        $barang->update([
            'sku'          => $data['sku'],
            'nama_barang'  => $data['nama_barang'],
            'kategori'  => $data['kategori'],
            'satuan_id'    => $data['satuan_id'],
            'harga_1'      => $data['harga_1'],
            'harga_2'      => $data['harga_2'] ?? null,
            'stok_minimum' => $data['stok_minimum'] ?? 0,
            'keterangan'   => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }



    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {

            $barang = Barang::findOrFail($id);

            if ($barang->pembelianDetail->count() > 0) {
                throw new \Exception("Barang tidak bisa dihapus karena masih digunakan di data pembelian !");
            }

            if ($barang->penjualanDetail->count() > 0) {
                throw new \Exception("Barang tidak bisa dihapus karena masih digunakan di data penjualan !");
            }

            // hapus histori stok
            StokMovement::where('barang_id', $barang->id)->delete();

            // hapus stok saat ini
            StokBarang::where('barang_id', $barang->id)->delete();

            // hapus barang
            $barang->delete();

            DB::commit();

            return redirect()
                ->route('barang.index')
                ->with('success', 'Barang berhasil dihapus.');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->route('barang.index')
                ->with('error', $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih.']);
        }

        DB::beginTransaction();

        try {
            $errors = [];

            foreach ($ids as $id) {
                $barang = Barang::find($id);
                if (!$barang) continue;

                if ($barang->pembelianDetail->count() > 0) {
                    $errors[] = "SKU {$barang->sku} digunakan di data pembelian.";
                    continue;
                }

                if ($barang->penjualanDetail->count() > 0) {
                    $errors[] = "SKU {$barang->sku} digunakan di data penjualan.";
                    continue;
                }

                StokMovement::where('barang_id', $barang->id)->delete();
                StokBarang::where('barang_id', $barang->id)->delete();
                $barang->delete();
            }

            DB::commit();

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sebagian barang tidak bisa dihapus: ' . implode(', ', $errors)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' barang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function search(Request $req)
    {

        $q = $req->q;

        return Barang::where('nama_barang', 'like', "%$q%")
            ->orWhere('sku', 'like', "%$q%")
            ->limit(50)
            ->with('stok')
            ->get();
    }

    public function barcode($sku)
    {

        return Barang::where('sku', $sku)->with('stok')->first();
    }

    public function downloadBarcode($id)
    {
        $barang = Barang::findOrFail($id);
        $sku    = $barang->sku;

        // 1. Generate QR code PNG (raw binary)
        $barcode    = new DNS2D();
        $barcodePng = base64_decode($barcode->getBarcodePNG($sku, 'QRCODE', 10, 10));

        // 2. Load ke GD
        $barcodeImg = imagecreatefromstring($barcodePng);
        $bcWidth    = imagesx($barcodeImg);
        $bcHeight   = imagesy($barcodeImg);

        // 3. Canvas dengan ruang teks di bawah
        $textHeight = 24;
        $padding    = 8;
        $canvas     = imagecreatetruecolor($bcWidth, $bcHeight + $textHeight + $padding);

        // 4. Background putih
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

        // 5. Copy QR ke canvas
        imagecopy($canvas, $barcodeImg, 0, 0, 0, 0, $bcWidth, $bcHeight);

        // 6. Tulis teks SKU di tengah bawah
        $black     = imagecolorallocate($canvas, 0, 0, 0);
        $fontSize  = 4;
        $textWidth = imagefontwidth($fontSize) * strlen($sku);
        $textX     = (int)(($bcWidth - $textWidth) / 2);
        $textY     = $bcHeight + $padding;
        imagestring($canvas, $fontSize, $textX, $textY, $sku, $black);

        // 7. Output PNG download
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="qrcode-' . $sku . '.png"');

        imagepng($canvas);

        imagedestroy($canvas);
        imagedestroy($barcodeImg);
        exit;
    }

    public function bulkUpdateHpp(Request $request)
    {
        $request->validate([
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'exists:barang,id',
            'harga_hpp' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            Barang::whereIn('id', $request->ids)
                ->update(['harga_1' => $request->harga_hpp]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' barang berhasil diupdate harga HPP-nya.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function bulkUpdateHargaReseller(Request $request)
    {
        $request->validate([
            'ids'            => 'required|array|min:1',
            'ids.*'          => 'exists:barang,id',
            'harga_reseller' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            Barang::whereIn('id', $request->ids)
                ->update(['harga_2' => $request->harga_reseller]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' barang berhasil diupdate harga reseller-nya.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
