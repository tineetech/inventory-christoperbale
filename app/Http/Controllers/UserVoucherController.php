<?php

namespace App\Http\Controllers;

use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class UserVoucherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $userVouchers = UserVoucher::with(['voucher', 'user']);

        if ($search) {
            $userVouchers->whereHas('voucher', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })->orWhereHas('user', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $userVouchers = $userVouchers->latest()->paginate($perPage);

        return view('pages.master.user_voucher.index', compact('userVouchers'));
    }

    public function create()
    {
        $vouchers = Voucher::where('status', 'active')->latest()->get();
        $users = Pengguna::whereHas('role', function ($q) {
            $q->where('nama_role', 'user');
        })->latest()->get();

        return view('pages.master.user_voucher.create', compact('vouchers', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'user_id'    => 'required|exists:pengguna,id',
        ]);

        $voucher = Voucher::findOrFail($request->voucher_id);

        if ($voucher->quota && $voucher->used_count >= $voucher->quota) {
            return redirect()->back()->withInput()->with('error', 'Kuota voucher "' . $voucher->name . '" sudah penuh.');
        }

        UserVoucher::create([
            'voucher_id' => $request->voucher_id,
            'user_id'    => $request->user_id,
            'status'     => 'unused',
        ]);

        $voucher->increment('used_count');

        return redirect()->route('user_voucher.index')->with('success', 'Voucher berhasil diberikan ke user.');
    }

    public function destroy($id)
    {
        $uv = UserVoucher::findOrFail($id);
        $uv->voucher->decrement('used_count');
        $uv->delete();

        return redirect()->route('user_voucher.index')->with('success', 'Data user voucher berhasil dihapus.');
    }
}
