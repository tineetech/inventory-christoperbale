<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function loginView() {
        return view('pages.auth.login');
    }
    
    
    public function loginAction(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = Pengguna::where('email', $request->email)
                    ->orWhere('nama', $request->email)
                    ->first();

        if(!$user){
            return back()->with('error','User tidak ditemukan');
        }

        if(!Hash::check($request->password, $user->password)){
            return back()->with('error','Password salah');
        }

        session([
            'user_id' => $user->id,
            'user_nama' => $user->nama,
            'role_id' => $user->role_id
        ]);

        return redirect('/dashboard')->with('success','Login berhasil');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login');
    }
}
