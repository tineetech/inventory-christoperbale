<?php

namespace App\Http\Controllers;

use App\Models\Dropshipper;
use Illuminate\Http\Request;

class DropshipperController extends Controller
{
    public function index()
    {
        $dropshippers = Dropshipper::all();
        return view('dropshipper.index', compact('dropshippers'));
    }

    public function create()
    {
        return view('dropshipper.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:100',
            'no_telp'    => 'nullable|string|max:20',
            'alamat'     => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $data = $request->all();

        Dropshipper::create([
            'nama'       => $data['nama'],
            'no_telp'    => $data['no_telp'] ?? null,
            'alamat'     => $data['alamat'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('dropshipper.index')->with('success', 'Dropshipper berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $dropshipper = Dropshipper::with('penjualan')->findOrFail($id);
        return view('dropshipper.show', compact('dropshipper'));
    }

    public function edit(string $id)
    {
        $dropshipper = Dropshipper::findOrFail($id);
        return view('dropshipper.edit', compact('dropshipper'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama'       => 'required|string|max:100',
            'no_telp'    => 'nullable|string|max:20',
            'alamat'     => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $data = $request->all();

        $dropshipper = Dropshipper::findOrFail($id);
        $dropshipper->update([
            'nama'       => $data['nama'],
            'no_telp'    => $data['no_telp'] ?? null,
            'alamat'     => $data['alamat'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        return redirect()->route('dropshipper.index')->with('success', 'Dropshipper berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $dropshipper = Dropshipper::findOrFail($id);
        $dropshipper->delete();

        return redirect()->route('dropshipper.index')->with('success', 'Dropshipper berhasil dihapus.');
    }
}
