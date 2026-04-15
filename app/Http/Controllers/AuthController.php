<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function loginView() {
        if (Auth::guard('pengguna')->check()) {
            return redirect('/dashboard');
        }
        return view('pages.auth.login');
    }
    
    public function loginAction(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        // cari user berdasarkan email atau nama
        $user = Pengguna::where('email', $request->email)
            ->orWhere('nama', $request->email)
            ->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        // cek password
        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah');
        }

        // LOGIN PAKAI GUARD PENGGUNA
        Auth::guard('pengguna')->login($user);

        return redirect('/dashboard')->with('success', 'Login berhasil');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login');
    }
}
