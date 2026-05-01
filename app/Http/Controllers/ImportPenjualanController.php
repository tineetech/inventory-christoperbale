<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImportPenjualanController extends Controller
{
    
    public function importTokped(Request $request)
    {
        $url = env('FASTAPI_URL') . '/scan-resi';
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post($url, [
            'mode' => 'tiktok',
        ]);

        return response()->json($response->json());
    }

    public function importShopee(Request $request)
    {
        $url = env('FASTAPI_URL') . '/scan-resi';
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post($url, [
            'mode' => 'shopee',
        ]);

        return response()->json($response->json());
    }
}