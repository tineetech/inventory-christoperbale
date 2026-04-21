<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = Pengguna::with('role')->get();
        return view('pages.pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('pages.pengguna.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'email'    => 'required|email|unique:pengguna,email',
            'password' => 'required|string',
            'role_id'  => 'required|exists:role,id',
        ]);

        $data = $request->all();

        Pengguna::create([
            'nama'     => $data['nama'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $pengguna = Pengguna::with('role')->findOrFail($id);
        return view('pages.pengguna.show', compact('pengguna'));
    }

    public function edit(string $id)
    {
        $pengguna = Pengguna::findOrFail($id);
        $roles = Role::all();
        return view('pages.pengguna.edit', compact('pengguna', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'email'    => 'required|email|unique:pengguna,email,' . $id,
            'password' => 'nullable|string',
            'role_id'  => 'required|exists:role,id',
        ]);

        $data = $request->all();

        $pengguna = Pengguna::findOrFail($id);

        $updateData = [
            'nama'    => $data['nama'],
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $pengguna->update($updateData);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $pengguna = Pengguna::findOrFail($id);
        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
