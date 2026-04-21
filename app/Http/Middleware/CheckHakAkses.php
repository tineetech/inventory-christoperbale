<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RoleHakAkses;

class CheckHakAkses
{
    public function handle(Request $request, Closure $next, $action, $module)
    {
        $user = Auth::guard('pengguna')->user();
        $permission = $action . '_' . $module;

        $cek = RoleHakAkses::where('role_id', $user->role_id)
            ->whereHas('hakAkses', function ($q) use ($permission) {
                $q->where('nama_permission', $permission);
            })
            ->exists();

        if (!$cek) {
            abort(403, 'Tidak memiliki akses');
        }

        return $next($request);
    }
}
