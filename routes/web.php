<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\RoleHakAksesController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DropshipperController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\StokBarangController;
use App\Http\Controllers\StokMovementController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PembelianDetailController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PenjualanDetailController;
use App\Http\Controllers\AdjustStokController;
use App\Http\Controllers\AdjustStokDetailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'loginView'])->name('login.view');
Route::post('/login', [AuthController::class, 'loginAction'])->name('login.action');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| DASHBOARD PAGE
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

/*
|--------------------------------------------------------------------------
| ROLE
|--------------------------------------------------------------------------
*/
Route::get('/role', [RoleController::class, 'index']);
Route::get('/role/create', [RoleController::class, 'create']);
Route::post('/role/store', [RoleController::class, 'store']);
Route::get('/role/edit/{id}', [RoleController::class, 'edit']);
Route::post('/role/update/{id}', [RoleController::class, 'update']);
Route::get('/role/delete/{id}', [RoleController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| PENGGUNA
|--------------------------------------------------------------------------
*/
Route::get('/pengguna', [PenggunaController::class, 'index']);
Route::get('/pengguna/create', [PenggunaController::class, 'create']);
Route::post('/pengguna/store', [PenggunaController::class, 'store']);
Route::get('/pengguna/edit/{id}', [PenggunaController::class, 'edit']);
Route::post('/pengguna/update/{id}', [PenggunaController::class, 'update']);
Route::get('/pengguna/delete/{id}', [PenggunaController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| HAK AKSES
|--------------------------------------------------------------------------
*/
Route::get('/hak-akses', [HakAksesController::class, 'index']);
Route::get('/hak-akses/create', [HakAksesController::class, 'create']);
Route::post('/hak-akses/store', [HakAksesController::class, 'store']);
Route::get('/hak-akses/edit/{id}', [HakAksesController::class, 'edit']);
Route::post('/hak-akses/update/{id}', [HakAksesController::class, 'update']);
Route::get('/hak-akses/delete/{id}', [HakAksesController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| ROLE HAK AKSES
|--------------------------------------------------------------------------
*/
Route::get('/role-hak-akses', [RoleHakAksesController::class, 'index']);
Route::get('/role-hak-akses/create', [RoleHakAksesController::class, 'create']);
Route::post('/role-hak-akses/store', [RoleHakAksesController::class, 'store']);
Route::get('/role-hak-akses/edit/{id}', [RoleHakAksesController::class, 'edit']);
Route::post('/role-hak-akses/update/{id}', [RoleHakAksesController::class, 'update']);
Route::get('/role-hak-akses/delete/{id}', [RoleHakAksesController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| SATUAN
|--------------------------------------------------------------------------
*/
Route::get('/satuan', [SatuanController::class, 'index']);
Route::get('/satuan/create', [SatuanController::class, 'create']);
Route::post('/satuan/store', [SatuanController::class, 'store']);
Route::get('/satuan/edit/{id}', [SatuanController::class, 'edit']);
Route::post('/satuan/update/{id}', [SatuanController::class, 'update']);
Route::get('/satuan/delete/{id}', [SatuanController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| SUPPLIER
|--------------------------------------------------------------------------
*/
Route::get('/supplier', [SupplierController::class, 'index']);
Route::get('/supplier/create', [SupplierController::class, 'create']);
Route::post('/supplier/store', [SupplierController::class, 'store']);
Route::get('/supplier/edit/{id}', [SupplierController::class, 'edit']);
Route::post('/supplier/update/{id}', [SupplierController::class, 'update']);
Route::get('/supplier/delete/{id}', [SupplierController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| DROPSHIPPER
|--------------------------------------------------------------------------
*/
Route::get('/dropshipper', [DropshipperController::class, 'index']);
Route::get('/dropshipper/create', [DropshipperController::class, 'create']);
Route::post('/dropshipper/store', [DropshipperController::class, 'store']);
Route::get('/dropshipper/edit/{id}', [DropshipperController::class, 'edit']);
Route::post('/dropshipper/update/{id}', [DropshipperController::class, 'update']);
Route::get('/dropshipper/delete/{id}', [DropshipperController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| BARANG
|--------------------------------------------------------------------------
*/
Route::get('/barang', [BarangController::class, 'index']);
Route::get('/barang/create', [BarangController::class, 'create']);
Route::post('/barang/store', [BarangController::class, 'store']);
Route::get('/barang/edit/{id}', [BarangController::class, 'edit']);
Route::post('/barang/update/{id}', [BarangController::class, 'update']);
Route::get('/barang/delete/{id}', [BarangController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| STOK BARANG
|--------------------------------------------------------------------------
*/
Route::get('/stok-barang', [StokBarangController::class, 'index']);
Route::get('/stok-barang/create', [StokBarangController::class, 'create']);
Route::post('/stok-barang/store', [StokBarangController::class, 'store']);
Route::get('/stok-barang/edit/{id}', [StokBarangController::class, 'edit']);
Route::post('/stok-barang/update/{id}', [StokBarangController::class, 'update']);
Route::get('/stok-barang/delete/{id}', [StokBarangController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| STOK MOVEMENT
|--------------------------------------------------------------------------
*/
Route::get('/stok-movement', [StokMovementController::class, 'index']);
Route::get('/stok-movement/create', [StokMovementController::class, 'create']);
Route::post('/stok-movement/store', [StokMovementController::class, 'store']);
Route::get('/stok-movement/edit/{id}', [StokMovementController::class, 'edit']);
Route::post('/stok-movement/update/{id}', [StokMovementController::class, 'update']);
Route::get('/stok-movement/delete/{id}', [StokMovementController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| PEMBELIAN
|--------------------------------------------------------------------------
*/
Route::get('/pembelian', [PembelianController::class, 'index']);
Route::get('/pembelian/create', [PembelianController::class, 'create']);
Route::post('/pembelian/store', [PembelianController::class, 'store']);
Route::get('/pembelian/edit/{id}', [PembelianController::class, 'edit']);
Route::post('/pembelian/update/{id}', [PembelianController::class, 'update']);
Route::get('/pembelian/delete/{id}', [PembelianController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| PEMBELIAN DETAIL
|--------------------------------------------------------------------------
*/
Route::get('/pembelian-detail', [PembelianDetailController::class, 'index']);
Route::get('/pembelian-detail/create', [PembelianDetailController::class, 'create']);
Route::post('/pembelian-detail/store', [PembelianDetailController::class, 'store']);
Route::get('/pembelian-detail/edit/{id}', [PembelianDetailController::class, 'edit']);
Route::post('/pembelian-detail/update/{id}', [PembelianDetailController::class, 'update']);
Route::get('/pembelian-detail/delete/{id}', [PembelianDetailController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| PENJUALAN
|--------------------------------------------------------------------------
*/
Route::get('/penjualan', [PenjualanController::class, 'index']);
Route::get('/penjualan/create', [PenjualanController::class, 'create']);
Route::post('/penjualan/store', [PenjualanController::class, 'store']);
Route::get('/penjualan/edit/{id}', [PenjualanController::class, 'edit']);
Route::post('/penjualan/update/{id}', [PenjualanController::class, 'update']);
Route::get('/penjualan/delete/{id}', [PenjualanController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| PENJUALAN DETAIL
|--------------------------------------------------------------------------
*/
Route::get('/penjualan-detail', [PenjualanDetailController::class, 'index']);
Route::get('/penjualan-detail/create', [PenjualanDetailController::class, 'create']);
Route::post('/penjualan-detail/store', [PenjualanDetailController::class, 'store']);
Route::get('/penjualan-detail/edit/{id}', [PenjualanDetailController::class, 'edit']);
Route::post('/penjualan-detail/update/{id}', [PenjualanDetailController::class, 'update']);
Route::get('/penjualan-detail/delete/{id}', [PenjualanDetailController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| ADJUST STOK
|--------------------------------------------------------------------------
*/
Route::get('/adjust-stok', [AdjustStokController::class, 'index']);
Route::get('/adjust-stok/create', [AdjustStokController::class, 'create']);
Route::post('/adjust-stok/store', [AdjustStokController::class, 'store']);
Route::get('/adjust-stok/edit/{id}', [AdjustStokController::class, 'edit']);
Route::post('/adjust-stok/update/{id}', [AdjustStokController::class, 'update']);
Route::get('/adjust-stok/delete/{id}', [AdjustStokController::class, 'destroy']);



/*
|--------------------------------------------------------------------------
| ADJUST STOK DETAIL
|--------------------------------------------------------------------------
*/
Route::get('/adjust-stok-detail', [AdjustStokDetailController::class, 'index']);
Route::get('/adjust-stok-detail/create', [AdjustStokDetailController::class, 'create']);
Route::post('/adjust-stok-detail/store', [AdjustStokDetailController::class, 'store']);
Route::get('/adjust-stok-detail/edit/{id}', [AdjustStokDetailController::class, 'edit']);
Route::post('/adjust-stok-detail/update/{id}', [AdjustStokDetailController::class, 'update']);
Route::get('/adjust-stok-detail/delete/{id}', [AdjustStokDetailController::class, 'destroy']);