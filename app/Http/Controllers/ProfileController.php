<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

public function edit()
{
    return view('pages.profile.edit');
}

public function update(Request $request)
{

    $user = Pengguna::findOrFail(Auth::guard('pengguna')->user()->id);

    $request->validate([
        'nama' => 'required',
        'email' => 'required|email'
    ]);

    $data = [
        'nama' => $request->nama,
        'email' => $request->email
    ];

    if($request->password){
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return redirect()->back()->with('success','Profile berhasil diperbarui');

}

}