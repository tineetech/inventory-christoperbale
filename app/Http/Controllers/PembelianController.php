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
        $pembelian = Pembelian::with('supplier', 'detail', 'user')->latest()->get();
        return view('pages.transaksi.pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        $supplier = Supplier::all();
        $kode = 'PB-' . date('Ymd') . '-' . rand(100,999);
        return view('pages.transaksi.pembelian.create', compact('supplier','kode'));
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
        return view('pages.transaksi.pembelian.edit', compact('pembelian', 'suppliers'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode_pembelian' => 'required|string|max:50|unique:pembelian,kode_pembelian,' . $id,
            'supplier_id'    => 'required|exists:supplier,id',
            'tanggal'        => 'required|date',
            'total_harga'    => 'required|numeric|min:0',
            'keterangan'     => 'nullable|string',
        ]);

        $data = $request->all();

        $pembelian = Pembelian::findOrFail($id);
        $pembelian->update([
            'kode_pembelian' => $data['kode_pembelian'],
            'supplier_id'    => $data['supplier_id'],
            'tanggal'        => $data['tanggal'],
            'total_harga'    => $data['total_harga'],
            'keterangan'     => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $pembelian->delete();

        return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus.');
    }
}
