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
        $namaProduk = $request->get('nama_produk');
        $brandId = $request->get('brand_id');
        $hargaMin = $request->get('harga_min');
        $hargaMax = $request->get('harga_max');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 10);

        $produk = Produk::with('fotoUtama', 'brand')->withCount(['varian', 'foto', 'barang']);

        if ($namaProduk) {
            $produk->where(function ($q) use ($namaProduk) {
                $q->where('nama_produk', 'like', "%{$namaProduk}%")
                  ->orWhere('slug', 'like', "%{$namaProduk}%");
            });
        }

        if ($search) {
            $produk->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($brandId) {
            $produk->where('brand_id', $brandId);
        }

        if ($hargaMin !== null && $hargaMin !== '') {
            $produk->where('harga_normal', '>=', (float) $hargaMin);
        }

        if ($hargaMax !== null && $hargaMax !== '') {
            $produk->where('harga_normal', '<=', (float) $hargaMax);
        }

        if ($status !== null && $status !== '') {
            $produk->where('status', $status);
        }

        $produk = $produk->latest()->paginate($perPage)->withQueryString();
        $brands = \App\Models\Brand::orderBy('nama_brand')->get();

        return view('pages.master.produk.index', compact('produk', 'brands'));
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
            'berat_gram'  => 'nullable|integer|min:0',
            'panjang_cm'  => 'nullable|integer|min:0',
            'lebar_cm'    => 'nullable|integer|min:0',
            'tinggi_cm'   => 'nullable|integer|min:0',
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
                'berat_gram'   => $request->berat_gram,
                'panjang_cm'   => $request->panjang_cm,
                'lebar_cm'     => $request->lebar_cm,
                'tinggi_cm'    => $request->tinggi_cm,
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
        $produk = Produk::with(['varian.barang', 'foto' => function($q) {
            $q->orderBy('urutan');
        }])->findOrFail($id);
        $brands = \App\Models\Brand::where('status_brand', 'aktif')->latest()->get();
        return view('pages.master.produk.edit', compact('produk', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        // Decode JSON strings from hidden inputs into real PHP arrays before validation
        if ($request->has('hapus_foto_ids') && is_string($request->hapus_foto_ids)) {
            $decoded = json_decode($request->hapus_foto_ids, true);
            $request->merge(['hapus_foto_ids' => is_array($decoded) ? $decoded : []]);
        }
        if ($request->has('existing_foto_order') && is_string($request->existing_foto_order)) {
            $decoded = json_decode($request->existing_foto_order, true);
            $request->merge(['existing_foto_order' => is_array($decoded) ? $decoded : []]);
        }

        $request->validate([
            'barang_ids'          => 'required|array|min:1',
            'barang_ids.*'        => 'exists:barang,id',
            'nama_produk'         => 'required|string|max:255',
            'slug'                => 'required|string|max:255|unique:produk,slug,' . $id,
            'deskripsi'           => 'nullable|string',
            'harga_normal'        => 'required|numeric|min:0',
            'status'              => 'required|in:aktif,nonaktif',
            'brand_id'            => 'nullable|exists:brand,id',
            'berat_gram'          => 'nullable|integer|min:0',
            'panjang_cm'          => 'nullable|integer|min:0',
            'lebar_cm'            => 'nullable|integer|min:0',
            'tinggi_cm'           => 'nullable|integer|min:0',
            'foto.*'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hapus_foto_ids'      => 'nullable|array',
            'hapus_foto_ids.*'    => 'exists:produk_foto,id',
            'existing_foto_order' => 'nullable|array',
            'existing_foto_order.*'=> 'exists:produk_foto,id',
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
                'berat_gram'   => $request->berat_gram,
                'panjang_cm'   => $request->panjang_cm,
                'lebar_cm'     => $request->lebar_cm,
                'tinggi_cm'    => $request->tinggi_cm,
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

            // Hapus foto yang ditandai
            $hapusIds = $request->input('hapus_foto_ids', []);
            if (!empty($hapusIds)) {
                $fotosToDelete = ProdukFoto::where('produk_id', $produk->id)
                    ->whereIn('id', $hapusIds)
                    ->get();
                foreach ($fotosToDelete as $f) {
                    $f->delete();
                }
            }

            // Update urutan foto lama yang tersisa
            $orderIds = $request->input('existing_foto_order', []);
            if (!empty($orderIds)) {
                // Reset semua is_utama dulu
                ProdukFoto::where('produk_id', $produk->id)->update(['is_utama' => false]);
                foreach ($orderIds as $idx => $fotoId) {
                    ProdukFoto::where('produk_id', $produk->id)
                        ->where('id', $fotoId)
                        ->update([
                            'urutan'   => $idx + 1,
                            'is_utama' => ($idx === 0),
                        ]);
                }
            }

            // Simpan foto baru yang diunggah
            if ($request->hasFile('foto')) {
                $existingCount = $produk->foto()->count();
                foreach ($request->file('foto') as $i => $file) {
                    $path = $file->store('produk/foto', 'public');
                    ProdukFoto::create([
                        'produk_id' => $produk->id,
                        'foto'      => $path,
                        'urutan'    => $existingCount + $i + 1,
                        'is_utama'  => ($existingCount === 0 && $i === 0),
                    ]);
                }
            }

            // Pastikan tetap ada 1 foto utama jika produk masih memiliki foto
            $hasUtama = $produk->foto()->where('is_utama', true)->exists();
            if (!$hasUtama) {
                $firstFoto = $produk->foto()->orderBy('urutan')->first();
                if ($firstFoto) {
                    $firstFoto->update(['is_utama' => true]);
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
