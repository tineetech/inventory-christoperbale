<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Banner::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $banners = $query->orderByRaw('urutan IS NULL, urutan ASC')->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('pages.master.banner.index', compact('banners'));
    }

    public function create()
    {
        return view('pages.master.banner.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateBanner($request, true);

        if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
            $data['gambar'] = $request->file('gambar')->store('banner', 'public');
        }

        Banner::create(array_merge($data, [
            'created_by' => Auth::guard('pengguna')->id(),
        ]));

        return redirect()->route('banner.index')->with('success', 'Banner "' . $request->title . '" berhasil dibuat.');
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        return view('pages.master.banner.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $data = $this->validateBanner($request, false);

        if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
            if ($banner->gambar) {
                Storage::disk('public')->delete($banner->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('banner', 'public');
        }

        $banner->update($data);

        return redirect()->route('banner.index')->with('success', 'Banner "' . $banner->title . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $title = $banner->title;

        if ($banner->gambar) {
            Storage::disk('public')->delete($banner->gambar);
        }

        $banner->delete();

        return redirect()->route('banner.index')->with('success', 'Banner "' . $title . '" berhasil dihapus.');
    }

    private function validateBanner(Request $request, bool $requireImage): array
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'gambar'    => ($requireImage ? 'required|' : 'nullable|') . 'image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'catatan'   => 'nullable|string|max:2000',
            'urutan'    => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        return [
            'title'     => $request->title,
            'catatan'   => $request->catatan,
            'urutan'    => $request->filled('urutan') ? (int) $request->urutan : null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}