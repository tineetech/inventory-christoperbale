<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['penjualan', 'penjualanDraft']);

        // ── Search ──────────────────────────────────────────
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_id_midtrans', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhere('payment_type', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('penjualan', fn($q2) => $q2->where('kode_penjualan', 'like', "%{$search}%"))
                  ->orWhereHas('penjualanDraft', fn($q2) => $q2->where('kode_penjualan', 'like', "%{$search}%"));
            });
        }

        // ── Filter Tanggal (default: hari ini) ──────────────
        $dateFrom = $request->date_from ?? today()->format('Y-m-d');
        $dateTo   = $request->date_to   ?? today()->format('Y-m-d');

        $from = $dateFrom . ' 00:00:00';
        $to   = $dateTo   . ' 23:59:59';
        $query->whereBetween('created_at', [$from, $to]);

        // ── Filter Status ───────────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Sort ────────────────────────────────────────────
        $sortCol = $request->sort_col;
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['order_id_midtrans', 'payment_method', 'payment_type', 'amount', 'status', 'created_at'];
        if ($sortCol && in_array($sortCol, $allowedSort)) {
            $query->orderBy($sortCol, $sortDir);
        } else {
            $query->orderByDesc('id');
        }

        // ── Pagination ──────────────────────────────────────
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;
        $pembayaran = $query->paginate($perPage)->withQueryString();

        return view('pages.transaksi.pembayaran.index', compact('pembayaran'));
    }
}
