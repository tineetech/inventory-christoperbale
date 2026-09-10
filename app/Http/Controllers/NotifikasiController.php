<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotifikasiController extends Controller
{
    /**
     * Halaman daftar notifikasi (Konfigurasi).
     * Default filter rentang 1 bulan ini (awal bulan s/d hari ini).
     */
    public function index(Request $request)
    {
        $dariTanggal = $request->filled('dari_tanggal')
            ? Carbon::parse($request->dari_tanggal)->toDateString()
            : now()->startOfMonth()->toDateString();

        $sampaiTanggal = $request->filled('sampai_tanggal')
            ? Carbon::parse($request->sampai_tanggal)->toDateString()
            : now()->toDateString();

        if ($dariTanggal > $sampaiTanggal) {
            [$dariTanggal, $sampaiTanggal] = [$sampaiTanggal, $dariTanggal];
        }

        $query = Notifikasi::with('user')
            ->whereDate('created_at', '>=', $dariTanggal)
            ->whereDate('created_at', '<=', $sampaiTanggal);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isi', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('nama', 'like', "%{$search}%"));
            });
        }

        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;
        $notifikasi = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        return view('pages.notifikasi.index', compact('notifikasi', 'dariTanggal', 'sampaiTanggal'));
    }

    /**
     * Data notifikasi untuk user yang login (JSON, dipakai polling header).
     */
    public function data()
    {
        $user = Auth::guard('pengguna')->user();

        $unreadCount = Notifikasi::forUser($user->id)->unread()->count();

        $list = Notifikasi::forUser($user->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function ($n) {
                return [
                    'id'          => $n->id,
                    'judul'       => $n->judul,
                    'isi'         => $n->isi,
                    'tipe'        => $n->tipe,
                    'link'        => $n->link,
                    'is_read'     => (bool) $n->is_read,
                    'created_at'  => $n->created_at ? $n->created_at->format('Y-m-d H:i:s') : null,
                    'created_diff'=> $n->created_at ? $n->created_at->diffForHumans() : null,
                ];
            });

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
            'data'         => $list,
        ]);
    }

    /**
     * Tandai notifikasi sebagai telah dibaca.
     * POST body: { id } → satu notif; tanpa id → semua notif user.
     */
    public function markRead(Request $request)
    {
        $user = Auth::guard('pengguna')->user();

        if ($id = $request->input('id')) {
            $notif = Notifikasi::forUser($user->id)->find($id);

            if (!$notif) {
                return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan.'], 404);
            }

            $notif->markAsRead();

            return response()->json(['success' => true, 'message' => 'Notifikasi ditandai dibaca.']);
        }

        // Mark all read
        Notifikasi::forUser($user->id)->unread()->get()->each->markAsRead();

        return response()->json(['success' => true, 'message' => 'Semua notifikasi ditandai dibaca.']);
    }
}
