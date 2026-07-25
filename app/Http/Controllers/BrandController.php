<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $brand = Brand::with('createdBy');

        if ($search) {
            $brand->where(function ($q) use ($search) {
                $q->where('nama_brand', 'like', "%{$search}%")
                  ->orWhere('deskripsi_brand', 'like', "%{$search}%");
            });
        }

        $brand = $brand->latest()->paginate($perPage);

        return view('pages.master.brand.index', compact('brand'));
    }

    public function create()
    {
        return view('pages.master.brand.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_brand'     => 'required|string|max:255',
            'deskripsi_brand' => 'nullable|string',
            'status_brand'   => 'required|in:aktif,nonaktif',
        ]);

        Brand::create([
            'nama_brand'      => $request->nama_brand,
            'deskripsi_brand' => $request->deskripsi_brand,
            'status_brand'    => $request->status_brand,
            'created_by'      => Auth::guard('pengguna')->id(),
        ]);

        return redirect()->route('brand.index')->with('success', 'Brand "' . $request->nama_brand . '" berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('pages.master.brand.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'nama_brand'     => 'required|string|max:255',
            'deskripsi_brand' => 'nullable|string',
            'status_brand'   => 'required|in:aktif,nonaktif',
        ]);

        $brand->update([
            'nama_brand'      => $request->nama_brand,
            'deskripsi_brand' => $request->deskripsi_brand,
            'status_brand'    => $request->status_brand,
        ]);

        return redirect()->route('brand.index')->with('success', 'Brand "' . $brand->nama_brand . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $nama = $brand->nama_brand;
        $brand->delete();

        return redirect()->route('brand.index')->with('success', 'Brand "' . $nama . '" berhasil dihapus.');
    }
}
