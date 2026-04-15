<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index()
    {
        $satuan = Satuan::all();
        return view('pages.master.satuan.index', compact('satuan'));
    }

    public function create()
    {
        return view('pages.master.satuan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:50|unique:satuan,nama_satuan',
        ]);

        $data = $request->all();

        Satuan::create([
            'nama_satuan' => $data['nama_satuan'],
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $satuan = Satuan::with('barang')->findOrFail($id);
        return view('pages.master.satuan.show', compact('satuan'));
    }

    public function edit(string $id)
    {
        $satuan = Satuan::findOrFail($id);
        return view('pages.master.satuan.edit', compact('satuan'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:50|unique:satuan,nama_satuan,' . $id,
        ]);

        $data = $request->all();

        $satuan = Satuan::findOrFail($id);
        $satuan->update([
            'nama_satuan' => $data['nama_satuan'],
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $satuan = Satuan::findOrFail($id);
        $satuan->delete();

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
