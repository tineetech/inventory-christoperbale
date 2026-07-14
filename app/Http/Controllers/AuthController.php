<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

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

    // ─── API Auth ───────────────────────────────────────────────

    public function apiRegister(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'email'    => 'required|email|unique:pengguna,email',
            'password' => ['required', 'confirmed', Password::min(6)],
            'full_name' => 'nullable|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'gender'   => 'nullable|in:L,P',
        ]);

        $pengguna = Pengguna::create([
            'nama'      => $request->nama,
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'gender'    => $request->gender,
            'password'  => Hash::make($request->password),
            'role_id'   => 1,
        ]);

        $token = $pengguna->createToken('api-token')->plainTextToken;

        return response()->json([
            'message'  => 'Register berhasil',
            'pengguna' => $pengguna,
            'token'    => $token,
        ], 201);
    }

    public function apiLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $pengguna = Pengguna::where('email', $request->email)->first();

        if (!$pengguna || !Hash::check($request->password, $pengguna->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $pengguna->createToken('api-token')->plainTextToken;

        return response()->json([
            'message'  => 'Login berhasil',
            'pengguna' => $pengguna,
            'token'    => $token,
        ]);
    }

    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $pengguna = Pengguna::where('email', $request->email)->first();

        if (!$pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan.',
            ], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $pengguna->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $pengguna->notify(new ResetPasswordNotification($token, $pengguna->email));

        return response()->json([
            'success' => true,
            'message' => 'Tautan reset password telah dikirim ke email Anda. Silakan cek inbox utama/kotak spam.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$row || !Hash::check($request->token, $row->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token reset password tidak valid atau sudah kadaluarsa.',
            ], 400);
        }

        if (now()->diffInMinutes($row->created_at) >= 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Token reset password sudah kadaluarsa.',
            ], 400);
        }

        $pengguna = Pengguna::where('email', $request->email)->first();

        if (!$pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan.',
            ], 404);
        }

        $pengguna->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login.',
        ]);
    }

    public function apiLoginGoogle(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'nama'  => 'required|string',
        ]);

        $pengguna = Pengguna::where('email', $request->email)->first();

        if (!$pengguna) {
            // Register baru
            $pengguna = Pengguna::create([
                'nama'     => $request->nama,
                'email'    => $request->email,
                'password' => Hash::make(Str::random(16)),
                'role_id'  => 1,
            ]);
        }

        $token = $pengguna->createToken('api-token')->plainTextToken;

        return response()->json([
            'message'  => 'Login Google berhasil',
            'pengguna' => $pengguna,
            'token'    => $token,
        ]);
    }
}
