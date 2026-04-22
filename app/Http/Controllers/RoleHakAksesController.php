<?php

namespace App\Http\Controllers;

use App\Models\RoleHakAkses;
use App\Models\Role;
use App\Models\HakAkses;
use Illuminate\Http\Request;

class RoleHakAksesController extends Controller
{
    
    public function index(Request $request)
    {
        $roles = Role::all();

        $roleId = $request->role ?? $roles->first()->id;

        $role = Role::findOrFail($roleId);

        $permissions = HakAkses::all();

        $rolePermissions = RoleHakAkses::where('role_id',$roleId)
                        ->pluck('hak_akses_id')
                        ->toArray();

        return view('pages.role_hak_akses.index',compact(
            'roles',
            'role',
            'permissions',
            'rolePermissions'
        ));
    }

    public function create()
    {
        $roles     = Role::all();
        $hakAkses  = HakAkses::all();
        return view('pages.role_hak_akses.create', compact('roles', 'hakAkses'));
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
        return view('pages.role_hak_akses.show', compact('roleHakAkses'));
    }

    public function edit(string $id)
    {
        $roleHakAkses = RoleHakAkses::findOrFail($id);
        $roles        = Role::all();
        $hakAkses     = HakAkses::all();
        return view('pages.role_hak_akses.edit', compact('roleHakAkses', 'roles', 'hakAkses'));
    }

    
    public function update(Request $request,$roleId)
    {
        RoleHakAkses::where('role_id',$roleId)->delete();

        if($request->permissions){

            foreach($request->permissions as $permission){

                RoleHakAkses::create([
                    'role_id'=>$roleId,
                    'hak_akses_id'=>$permission
                ]);

            }

        }

        return redirect()
            ->back()
            ->with('success','Hak akses berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $roleHakAkses = RoleHakAkses::findOrFail($id);
        $roleHakAkses->delete();

        return redirect()->route('role-hak-akses.index')->with('success', 'Role hak akses berhasil dihapus.');
    }
}
