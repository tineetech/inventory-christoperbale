<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountProduct;
use App\Models\Produk;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $discounts = Discount::withCount('discountProducts');

        if ($search) {
            $discounts->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $discounts = $discounts->latest()->paginate($perPage);

        return view('pages.master.discount.index', compact('discounts'));
    }

    public function create()
    {
        return view('pages.master.discount.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|max:50',
            'value'          => 'required|numeric|min:0',
            'start_at'       => 'nullable|date',
            'end_at'         => 'nullable|date|after_or_equal:start_at',
            'products'       => 'nullable|array',
            'products.*.id'  => 'exists:produk,id',
            'products.*.status' => 'in:active,nonactive',
        ]);

        $discount = Discount::create([
            'name'     => $request->name,
            'type'     => $request->type,
            'value'    => $request->value,
            'start_at' => $request->start_at,
            'end_at'   => $request->end_at,
        ]);

        if ($request->products) {
            foreach ($request->products as $p) {
                DiscountProduct::create([
                    'discount_id' => $discount->id,
                    'product_id'  => $p['id'],
                    'status'      => $p['status'] ?? 'active',
                ]);
            }
        }

        return redirect()->route('discount.index')->with('success', 'Discount "' . $discount->name . '" berhasil dibuat.');
    }

    public function edit($id)
    {
        $discount = Discount::with('discountProducts.product')->findOrFail($id);
        return view('pages.master.discount.edit', compact('discount'));
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string|max:50',
            'value'          => 'required|numeric|min:0',
            'start_at'       => 'nullable|date',
            'end_at'         => 'nullable|date|after_or_equal:start_at',
            'products'       => 'nullable|array',
            'products.*.id'  => 'exists:produk,id',
            'products.*.status' => 'in:active,nonactive',
        ]);

        $discount->update([
            'name'     => $request->name,
            'type'     => $request->type,
            'value'    => $request->value,
            'start_at' => $request->start_at,
            'end_at'   => $request->end_at,
        ]);

        $discount->discountProducts()->delete();

        if ($request->products) {
            foreach ($request->products as $p) {
                DiscountProduct::create([
                    'discount_id' => $discount->id,
                    'product_id'  => $p['id'],
                    'status'      => $p['status'] ?? 'active',
                ]);
            }
        }

        return redirect()->route('discount.index')->with('success', 'Discount "' . $discount->name . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $name = $discount->name;
        $discount->delete();

        return redirect()->route('discount.index')->with('success', 'Discount "' . $name . '" berhasil dihapus.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->q;
        return Produk::where('nama_produk', 'like', "%{$q}%")
            ->orWhere('slug', 'like', "%{$q}%")
            ->limit(50)
            ->get();
    }
}
