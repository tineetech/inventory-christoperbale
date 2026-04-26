<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangImportController;
use App\Http\Controllers\ImportPenjualanController;
use App\Http\Controllers\PenjualanScanOutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| API PRODUCTS PAGE
|--------------------------------------------------------------------------
*/
Route::get('/product/search', [BarangController::class, 'search']);
Route::get('/product/barcode/{id}', [BarangController::class, 'barcode']);
Route::post('/barang/import', [BarangImportController::class, 'import']);
Route::get('/barang/import/template', [BarangImportController::class, 'downloadTemplate']);


/*
|--------------------------------------------------------------------------
| IMPORT PENJUALAN
|--------------------------------------------------------------------------
*/
Route::post('/penjualan/import/tokped-jnt', [ImportPenjualanController::class, 'importTokped']);
Route::post('/penjualan/import/shopee', [ImportPenjualanController::class, 'importShopee']);


Route::prefix('penjualan')->group(function () {
 
    // GET  /api/penjualan/list      → ambil semua data penjualan (auto-refresh)
    Route::get('/list', [PenjualanScanOutController::class, 'list']);
 
    // POST /api/penjualan/scan-out  → scan SKU & update scan_out = done
    Route::post('/scan-out', [PenjualanScanOutController::class, 'scanOut']);
 
});
 