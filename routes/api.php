<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangImportController;
use App\Http\Controllers\ImportPenjualanController;
use App\Http\Controllers\PenjualanScanOutController;
use App\Http\Controllers\UserAddressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'apiRegister']);
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/auth/google', [AuthController::class, 'apiLoginGoogle']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/barang', [BarangController::class, 'apiIndex']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    Route::apiResource('addresses', UserAddressController::class, [
        'except' => ['create', 'edit'],
    ]);
});

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
Route::post(
    '/penjualan/import/multiple-resi',
    [ImportPenjualanController::class, 'importMultipleResi']
)->name('api.penjualan.import.multiple-resi');
Route::post('/penjualan/import/tokped-jnt', [ImportPenjualanController::class, 'importTokped']);
Route::post('/penjualan/import/shopee', [ImportPenjualanController::class, 'importShopee']);

Route::post('/import-multiple-resi-async', [ImportPenjualanController::class, 'submitMultipleResiJob'])->name('api.import.multi-job');
Route::get('/import-job-status/{jobId}', [ImportPenjualanController::class, 'pollJobStatus'])->name('api.import.multi-job.status');


Route::prefix('penjualan')->group(function () {
    Route::get('/list', [PenjualanScanOutController::class, 'list']);
    Route::post('/scan-out', [PenjualanScanOutController::class, 'scanOut']);
});
