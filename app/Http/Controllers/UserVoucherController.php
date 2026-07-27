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
        return view('pages.master.user_voucher.create', compact('vouchers'));
    }

    public function users(Request $request)
    {
        $q = $request->q;
        $users = Pengguna::whereHas('role', function ($query) {
            $query->where('nama_role', 'user');
        })->when($q, function ($query, $q) {
            $query->where('nama', 'like', "%{$q}%");
        })->limit(50)->get(['id', 'nama']);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:pengguna,id',
        ]);

        $voucher = Voucher::findOrFail($request->voucher_id);
        $created = 0;

        foreach ($request->user_ids as $userId) {
            if ($voucher->quota && $voucher->used_count >= $voucher->quota) {
                break;
            }

            $exists = UserVoucher::where('voucher_id', $voucher->id)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) continue;

            UserVoucher::create([
                'voucher_id' => $voucher->id,
                'user_id'    => $userId,
                'status'     => 'unused',
            ]);

            $voucher->increment('used_count');
            $created++;
        }

        if ($created === 0) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada user baru yang bisa diberikan voucher (sudah pernah diberikan atau kuota penuh).');
        }

        return redirect()->route('user_voucher.index')->with('success', "Voucher berhasil diberikan ke {$created} user.");
    }

    public function destroy($id)
    {
        $uv = UserVoucher::findOrFail($id);
        if ($uv->voucher) {
            $uv->voucher->decrement('used_count');
        }
        $uv->delete();

        return redirect()->route('user_voucher.index')->with('success', 'Data user voucher berhasil dihapus.');
    }
}
