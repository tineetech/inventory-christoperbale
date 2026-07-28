<?php

namespace App\Http\Controllers;

use App\Models\PengaturanWeb;
use Illuminate\Http\Request;

class PengaturanWebController extends Controller
{
    public function index()
    {
        $settings = PengaturanWeb::orderBy('key')->get();
        return view('pages.pengaturan_web.index', compact('settings'));
    }

    public function create()
    {
        return view('pages.pengaturan_web.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key'   => 'required|string|max:100|unique:pengaturan_web,key',
            'value' => 'nullable|string',
        ]);

        PengaturanWeb::create([
            'key'   => $request->key,
            'value' => $request->value,
        ]);

        return redirect()->route('pengaturan_web.index')->with('success', 'Pengaturan "' . $request->key . '" berhasil ditambahkan.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'values' => 'required|array',
            'values.*' => 'nullable|string',
        ]);

        foreach ($request->values as $id => $value) {
            PengaturanWeb::where('id', $id)->update(['value' => $value]);
        }

        return redirect()->route('pengaturan_web.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
