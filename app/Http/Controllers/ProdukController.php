<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\ProdukFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $produk = Produk::with('fotoUtama', 'brand')->withCount(['varian', 'foto', 'barang']);

        if ($search) {
            $produk->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $produk = $produk->latest()->paginate($perPage);

        return view('pages.master.produk.index', compact('produk'));
    }

    public function create()
    {
        $brands = \App\Models\Brand::where('status_brand', 'aktif')->latest()->get();
        return view('pages.master.produk.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_ids'  => 'required|array|min:1',
            'barang_ids.*'=> 'exists:barang,id',
            'nama_produk' => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:produk,slug',
            'deskripsi'   => 'nullable|string',
            'harga_normal'=> 'required|numeric|min:0',
            'status'      => 'required|in:aktif,nonaktif',
            'brand_id'    => 'nullable|exists:brand,id',
            'foto.*'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $alreadyGrouped = Barang::whereIn('id', $request->barang_ids)->whereNotNull('produk_id')->get();
        if ($alreadyGrouped->isNotEmpty()) {
            $names = $alreadyGrouped->pluck('nama_barang')->take(5)->implode(', ');
            return redirect()->back()->withInput()->with('error', 'Barang berikut sudah memiliki produk: ' . $names . '. Hapus kelompok produk sebelumnya jika ingin menggrup ulang.');
        }

        DB::beginTransaction();
        try {
            $produk = Produk::create([
                'nama_produk'  => $request->nama_produk,
                'slug'         => $request->slug,
                'deskripsi'    => $request->deskripsi,
                'harga_normal' => $request->harga_normal,
                'status'       => $request->status,
                'brand_id'     => $request->brand_id,
            ]);

            $barangs = Barang::whereIn('id', $request->barang_ids)->get();

            foreach ($barangs as $barang) {
                $barang->update(['produk_id' => $produk->id]);

                $words = explode(' ', $barang->nama_barang);

                $size = null;
                for ($i = 2; $i <= 3; $i++) {
                    if (isset($words[$i]) && preg_match('/\d/', $words[$i])) {
                        $size = $words[$i];
                        break;
                    }
                }

                ProdukVarian::create([
                    'produk_id' => $produk->id,
                    'barang_id' => $barang->id,
                    'warna'     => $words[1] ?? null,
                    'size'      => $size,
                ]);
            }

            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $i => $file) {
                    $path = $file->store('produk/foto', 'public');
                    ProdukFoto::create([
                        'produk_id' => $produk->id,
                        'foto'      => $path,
                        'urutan'    => $i + 1,
                        'is_utama'  => $i === 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Produk "' . $produk->nama_produk . '" berhasil dibuat dengan ' . count($barangs) . ' varian.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $produk = Produk::with(['varian.barang', 'foto'])->findOrFail($id);
        $brands = \App\Models\Brand::where('status_brand', 'aktif')->latest()->get();
        return view('pages.master.produk.edit', compact('produk', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'barang_ids'  => 'required|array|min:1',
            'barang_ids.*'=> 'exists:barang,id',
            'nama_produk' => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:produk,slug,' . $id,
            'deskripsi'   => 'nullable|string',
            'harga_normal'=> 'required|numeric|min:0',
            'status'      => 'required|in:aktif,nonaktif',
            'brand_id'    => 'nullable|exists:brand,id',
            'foto.*'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $alreadyGrouped = Barang::whereIn('id', $request->barang_ids)
            ->whereNotNull('produk_id')
            ->where('produk_id', '!=', $id)
            ->get();
        if ($alreadyGrouped->isNotEmpty()) {
            $names = $alreadyGrouped->pluck('nama_barang')->take(5)->implode(', ');
            return redirect()->back()->withInput()->with('error', 'Barang berikut sudah memiliki produk lain: ' . $names);
        }

        DB::beginTransaction();
        try {
            $produk->update([
                'nama_produk'  => $request->nama_produk,
                'slug'         => $request->slug,
                'deskripsi'    => $request->deskripsi,
                'harga_normal' => $request->harga_normal,
                'status'       => $request->status,
                'brand_id'     => $request->brand_id,
            ]);

            $oldBarangIds = $produk->barang()->pluck('id')->toArray();
            $newBarangIds = $request->barang_ids;

            $toRemove = array_diff($oldBarangIds, $newBarangIds);
            $toAdd = array_diff($newBarangIds, $oldBarangIds);

            if ($toRemove) {
                Barang::whereIn('id', $toRemove)->update(['produk_id' => null]);
                ProdukVarian::whereIn('barang_id', $toRemove)->where('produk_id', $id)->delete();
            }

            if ($toAdd) {
                $barangs = Barang::whereIn('id', $toAdd)->get();
                foreach ($barangs as $barang) {
                    $barang->update(['produk_id' => $produk->id]);

                    $words = explode(' ', $barang->nama_barang);

                    $size = null;
                    for ($i = 2; $i <= 3; $i++) {
                        if (isset($words[$i]) && preg_match('/\d/', $words[$i])) {
                            $size = $words[$i];
                            break;
                        }
                    }

                    ProdukVarian::create([
                        'produk_id' => $produk->id,
                        'barang_id' => $barang->id,
                        'warna'     => $words[1] ?? null,
                        'size'      => $size,
                    ]);
                }
            }

            if ($request->hasFile('foto')) {
                foreach ($request->file('foto') as $i => $file) {
                    $path = $file->store('produk/foto', 'public');
                    ProdukFoto::create([
                        'produk_id' => $produk->id,
                        'foto'      => $path,
                        'urutan'    => $produk->foto()->count() + $i + 1,
                        'is_utama'  => $produk->foto()->count() === 0 && $i === 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Produk "' . $produk->nama_produk . '" berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $nama = $produk->nama_produk;

        DB::beginTransaction();
        try {
            Barang::where('produk_id', $id)->update(['produk_id' => null]);

            foreach ($produk->foto as $f) {
                Storage::disk('public')->delete($f->foto);
            }

            $produk->delete();

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Produk "' . $nama . '" berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function varian(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $varian = ProdukVarian::with(['produk', 'barang']);

        if ($search) {
            $varian->whereHas('produk', function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%");
            })->orWhereHas('barang', function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%");
            })->orWhere('warna', 'like', "%{$search}%")
              ->orWhere('size', 'like', "%{$search}%");
        }

        $varian = $varian->latest()->paginate($perPage);

        return view('pages.master.produk_varian.index', compact('varian'));
    }

    public function foto(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $foto = ProdukFoto::with('produk');

        if ($search) {
            $foto->whereHas('produk', function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%");
            })->orWhere('foto', 'like', "%{$search}%");
        }

        $foto = $foto->latest()->paginate($perPage);

        return view('pages.master.produk_foto.index', compact('foto'));
    }
}
