<?php

namespace App\Http\Controllers;

use App\Models\HakAkses;
use Illuminate\Http\Request;

class HakAksesController extends Controller
{
    public function index()
    {
        $hakAkses = HakAkses::with('roles')->get();
        return view('hak_akses.index', compact('hakAkses'));
    }

    public function create()
    {
        return view('hak_akses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_permission' => 'required|string|max:100|unique:hak_akses,nama_permission',
        ]);

        $data = $request->all();

        HakAkses::create([
            'nama_permission' => $data['nama_permission'],
        ]);

        return redirect()->route('hak-akses.index')->with('success', 'Hak akses berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $hakAkses = HakAkses::with('roles')->findOrFail($id);
        return view('hak_akses.show', compact('hakAkses'));
    }

    public function edit(string $id)
    {
        $hakAkses = HakAkses::findOrFail($id);
        return view('hak_akses.edit', compact('hakAkses'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_permission' => 'required|string|max:100|unique:hak_akses,nama_permission,' . $id,
        ]);

        $data = $request->all();

        $hakAkses = HakAkses::findOrFail($id);
        $hakAkses->update([
            'nama_permission' => $data['nama_permission'],
        ]);

        return redirect()->route('hak-akses.index')->with('success', 'Hak akses berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $hakAkses = HakAkses::findOrFail($id);
        $hakAkses->delete();

        return redirect()->route('hak-akses.index')->with('success', 'Hak akses berhasil dihapus.');
    }
}
