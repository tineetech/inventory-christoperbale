<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StokBarang;
use App\Models\StokMovement;
use Illuminate\Support\Facades\Auth;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::with('satuan', 'stok')->get();
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
            'keterangan'   => 'nullable|string',
        ]);

        $data = $request->all();

        $barang = Barang::findOrFail($id);
        $barang->update([
            'sku'          => $data['sku'],
            'nama_barang'  => $data['nama_barang'],
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

    public function search(Request $req)
    {

        $q = $req->q;

        return Barang::where('nama_barang','like',"%$q%")
        ->orWhere('sku','like',"%$q%")
        ->limit(50)
        ->with('stok')
        ->get();

    }

    public function barcode($sku)
    {

    return Barang::where('sku',$sku)->with('stok')->first();

    }
}
