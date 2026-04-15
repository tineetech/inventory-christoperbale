<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    
    public function index() {
        if (!Auth::guard('pengguna')->check()) {
            return redirect('/login');
        }
        return view('pages.dashboard');
    }
}
