<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $vouchers = Voucher::with('creator')->withCount('userVouchers');

        if ($search) {
            $vouchers->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $vouchers = $vouchers->latest()->paginate($perPage);

        return view('pages.master.voucher.index', compact('vouchers'));
    }

    public function create()
    {
        return view('pages.master.voucher.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'               => 'required|string|max:50|unique:vouchers,code',
            'name'               => 'required|string|max:150',
            'description'        => 'nullable|string',
            'type'               => 'required|in:fixed,percent,shipping',
            'value'              => 'required|numeric|min:0',
            'minimum_purchase'   => 'nullable|numeric|min:0',
            'maximum_discount'   => 'nullable|numeric|min:0',
            'quota'              => 'nullable|integer|min:1',
            'claim_limit_per_user'=> 'nullable|integer|min:1',
            'start_at'           => 'required|date',
            'end_at'             => 'required|date|after_or_equal:start_at',
            'status'             => 'required|in:active,inactive',
        ]);

        Voucher::create([
            'code'                => $request->code,
            'name'                => $request->name,
            'description'         => $request->description,
            'type'                => $request->type,
            'value'               => $request->value,
            'minimum_purchase'    => $request->minimum_purchase ?? 0,
            'maximum_discount'    => $request->maximum_discount,
            'quota'               => $request->quota,
            'claim_limit_per_user'=> $request->claim_limit_per_user ?? 1,
            'start_at'            => $request->start_at,
            'end_at'              => $request->end_at,
            'status'              => $request->status,
            'created_by'          => Auth::guard('pengguna')->id(),
        ]);

        return redirect()->route('voucher.index')->with('success', 'Voucher "' . $request->name . '" berhasil dibuat.');
    }

    public function edit($id)
    {
        $voucher = Voucher::findOrFail($id);
        return view('pages.master.voucher.edit', compact('voucher'));
    }

    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $request->validate([
            'code'               => 'required|string|max:50|unique:vouchers,code,' . $id,
            'name'               => 'required|string|max:150',
            'description'        => 'nullable|string',
            'type'               => 'required|in:fixed,percent,shipping',
            'value'              => 'required|numeric|min:0',
            'minimum_purchase'   => 'nullable|numeric|min:0',
            'maximum_discount'   => 'nullable|numeric|min:0',
            'quota'              => 'nullable|integer|min:1',
            'claim_limit_per_user'=> 'nullable|integer|min:1',
            'start_at'           => 'required|date',
            'end_at'             => 'required|date|after_or_equal:start_at',
            'status'             => 'required|in:active,inactive',
        ]);

        $voucher->update([
            'code'                => $request->code,
            'name'                => $request->name,
            'description'         => $request->description,
            'type'                => $request->type,
            'value'               => $request->value,
            'minimum_purchase'    => $request->minimum_purchase ?? 0,
            'maximum_discount'    => $request->maximum_discount,
            'quota'               => $request->quota,
            'claim_limit_per_user'=> $request->claim_limit_per_user ?? 1,
            'start_at'            => $request->start_at,
            'end_at'              => $request->end_at,
            'status'              => $request->status,
        ]);

        return redirect()->route('voucher.index')->with('success', 'Voucher "' . $voucher->name . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $voucher = Voucher::findOrFail($id);
        $name = $voucher->name;
        $voucher->delete();

        return redirect()->route('voucher.index')->with('success', 'Voucher "' . $name . '" berhasil dihapus.');
    }
}
