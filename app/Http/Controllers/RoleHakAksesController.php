<?php

namespace App\Http\Controllers;

use App\Models\RoleHakAkses;
use App\Models\Role;
use App\Models\HakAkses;
use Illuminate\Http\Request;

class RoleHakAksesController extends Controller
{
    public function index()
    {
        $roleHakAkses = RoleHakAkses::with(['role', 'hakAkses'])->get();
        // Grouping by role for cleaner display
        $roles = Role::with('permissions')->get();
        return view('role_hak_akses.index', compact('roleHakAkses', 'roles'));
    }

    public function create()
    {
        $roles     = Role::all();
        $hakAkses  = HakAkses::all();
        return view('role_hak_akses.create', compact('roles', 'hakAkses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id'      => 'required|exists:role,id',
            'hak_akses_id' => 'required|exists:hak_akses,id|unique:role_hak_akses,hak_akses_id,NULL,id,role_id,' . $request->role_id,
        ]);

        $data = $request->all();

        RoleHakAkses::create([
            'role_id'      => $data['role_id'],
            'hak_akses_id' => $data['hak_akses_id'],
        ]);

        return redirect()->route('role-hak-akses.index')->with('success', 'Role hak akses berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $roleHakAkses = RoleHakAkses::with(['role', 'hakAkses'])->findOrFail($id);
        return view('role_hak_akses.show', compact('roleHakAkses'));
    }

    public function edit(string $id)
    {
        $roleHakAkses = RoleHakAkses::findOrFail($id);
        $roles        = Role::all();
        $hakAkses     = HakAkses::all();
        return view('role_hak_akses.edit', compact('roleHakAkses', 'roles', 'hakAkses'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'role_id'      => 'required|exists:role,id',
            'hak_akses_id' => 'required|exists:hak_akses,id',
        ]);

        $data = $request->all();

        $roleHakAkses = RoleHakAkses::findOrFail($id);
        $roleHakAkses->update([
            'role_id'      => $data['role_id'],
            'hak_akses_id' => $data['hak_akses_id'],
        ]);

        return redirect()->route('role-hak-akses.index')->with('success', 'Role hak akses berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $roleHakAkses = RoleHakAkses::findOrFail($id);
        $roleHakAkses->delete();

        return redirect()->route('role-hak-akses.index')->with('success', 'Role hak akses berhasil dihapus.');
    }
}
