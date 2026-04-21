<?php

// namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\RoleHakAkses;

if (!function_exists('hasPermission')) {

    function hasPermission($action, $module)
    {
        $user = Auth::guard('pengguna')->user();

        if (!$user) {
            return false;
        }

        $permission = $action . '_' . $module;

        return RoleHakAkses::where('role_id', $user->role_id)
            ->whereHas('hakAkses', function ($q) use ($permission) {
                $q->where('nama_permission', $permission);
            })
            ->exists();
    }
}