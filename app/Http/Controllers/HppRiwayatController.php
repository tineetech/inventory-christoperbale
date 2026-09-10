<?php

namespace App\Http\Controllers;

use App\Models\HppRiwayat;
use Illuminate\Http\Request;

class HppRiwayatController extends Controller
{
    public function index(Request $request)
    {
        $query = HppRiwayat::with(['barang', 'user', 'detail']);

        // ── Search ──────────────────────────────────────────
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('barang', function ($qb) use ($search) {
                    $qb->where('nama_barang', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            });
        }

        // ── Sort ────────────────────────────────────────────
        $sortCol = $request->sort_col;
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['hpp_lama', 'hpp_baru', 'tanggal', 'created_at'];
        if ($sortCol && in_array($sortCol, $allowedSort)) {
            $query->orderBy($sortCol, $sortDir);
        } else {
            $query->orderByDesc('tanggal');
        }

        // ── Pagination ──────────────────────────────────────
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;
        $hppRiwayat = $query->paginate($perPage)->withQueryString();

        return view('pages.master.hpp-riwayat.index', compact('hppRiwayat'));
    }
}