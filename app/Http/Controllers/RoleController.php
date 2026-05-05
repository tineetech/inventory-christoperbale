<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('role.index', compact('roles'));
    }

    public function create()
    {
        return view('role.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_role' => 'required|string|max:100|unique:role,nama_role',
        ]);

        $duplicateRole = Role::where('nama_role', $request->nama_role)->first();
        if ($duplicateRole) {
            return redirect()->route('role.index')->with('error', 'Role telah tersedia !');
        }

        $data = $request->all();

        Role::create([
            'nama_role' => $data['nama_role'],
        ]);

        return redirect()->route('role_hak_akses.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $role = Role::with('permissions', 'pengguna')->findOrFail($id);
        return view('role.show', compact('role'));
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        return view('role.edit', compact('role'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama_role' => 'required|string|max:100|unique:role,nama_role,' . $id,
        ]);

        $data = $request->all();

        $role = Role::findOrFail($id);
        $role->update([
            'nama_role' => $data['nama_role'],
        ]);

        return redirect()->route('role.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Cegah hapus role yang masih dipakai pengguna
        if ($role->pengguna()->count() > 0) {
            return redirect()->back()->with('error', "Role '{$role->nama_role}' masih digunakan oleh {$role->pengguna()->count()} pengguna.");
        }

        $nama = $role->nama_role;
        $role->delete();

        return redirect()->back()->with('success', "Role '{$nama}' berhasil dihapus.");
    }
}
