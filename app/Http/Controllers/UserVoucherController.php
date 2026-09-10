<?php

namespace App\Http\Controllers;

use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $voucherId = $request->voucher_id;
        $query = Pengguna::whereHas('role', function ($query) {
            $query->where('nama_role', 'user');
        })->when($q, function ($query, $q) {
            $query->where('nama', 'like', "%{$q}%");
        });

        // Jika voucher_id dikirim, exclude user yang sudah punya voucher tersebut (untuk UX)
        if ($voucherId) {
            $query->whereNotExists(function ($sub) use ($voucherId) {
                $sub->select(DB::raw(1))
                    ->from('user_vouchers')
                    ->whereColumn('user_vouchers.user_id', 'pengguna.id')
                    ->where('user_vouchers.voucher_id', $voucherId);
            });
        }

        $users = $query->limit(50)->get(['id', 'nama']);

        return response()->json($users);
    }

    public function edit($id)
    {
        $userVoucher = UserVoucher::with(['voucher', 'user'])->findOrFail($id);
        $vouchers = Voucher::where('status', 'active')->latest()->get();
        return view('pages.master.user_voucher.edit', compact('userVoucher', 'vouchers'));
    }

    public function update(Request $request, $id)
    {
        $userVoucher = UserVoucher::findOrFail($id);

        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'user_id'    => 'required|exists:pengguna,id',
        ]);

        $exists = UserVoucher::where('voucher_id', $request->voucher_id)
            ->where('user_id', $request->user_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            $userName = Pengguna::find($request->user_id)?->nama ?? 'User';
            $voucherName = Voucher::find($request->voucher_id)?->code ?? 'Voucher';
            throw ValidationException::withMessages([
                'user_id' => "User \"{$userName}\" sudah memiliki voucher \"{$voucherName}\" tersebut.",
                'voucher_id' => "Voucher sudah dimiliki user tersebut.",
            ]);
        }

        try {
            $userVoucher->update([
                'voucher_id' => $request->voucher_id,
                'user_id'    => $request->user_id,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                throw ValidationException::withMessages([
                    'user_id' => 'User sudah memiliki voucher tersebut (duplikat).',
                ]);
            }
            throw $e;
        }

        return redirect()->route('user_voucher.index')->with('success', 'User voucher berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:pengguna,id',
        ]);

        $voucher = Voucher::findOrFail($request->voucher_id);

        // Kumpulkan user yang sudah punya voucher ini
        $existing = UserVoucher::where('voucher_id', $voucher->id)
            ->whereIn('user_id', $request->user_ids)
            ->pluck('user_id')
            ->toArray();

        if (!empty($existing)) {
            $names = Pengguna::whereIn('id', $existing)->pluck('nama')->implode(', ');
            throw ValidationException::withMessages([
                'user_ids' => "User berikut sudah memiliki voucher \"{$voucher->code}\" tersebut: {$names}. Silakan pilih user lain.",
            ]);
        }

        // Cek kuota
        $need = count($request->user_ids);
        if ($voucher->quota && ($voucher->used_count + $need) > $voucher->quota) {
            $sisa = max(0, $voucher->quota - $voucher->used_count);
            throw ValidationException::withMessages([
                'voucher_id' => "Kuota voucher tidak cukup. Sisa kuota: {$sisa}, dibutuhkan: {$need}.",
            ]);
        }

        $created = 0;
        DB::beginTransaction();
        try {
            foreach ($request->user_ids as $userId) {
                UserVoucher::create([
                    'voucher_id' => $voucher->id,
                    'user_id'    => $userId,
                    'status'     => 'unused',
                ]);
                $created++;
            }
            $voucher->increment('used_count', $created);
            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == 23000) {
                throw ValidationException::withMessages([
                    'user_ids' => 'Terdeteksi duplikat: salah satu user sudah memiliki voucher tersebut. Silakan refresh dan pilih ulang.',
                ]);
            }
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
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
