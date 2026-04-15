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
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::guard('pengguna')->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'loginView'])->name('login.view');
Route::post('/login', [AuthController::class, 'loginAction'])->name('login.action');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

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
Route::get('/master/satuan', [SatuanController::class, 'index'])->name('satuan.index');
Route::get('/master/satuan/create', [SatuanController::class, 'create'])->name('satuan.create');
Route::post('/master/satuan/store', [SatuanController::class, 'store'])->name('satuan.store');
Route::get('/master/satuan/edit/{id}', [SatuanController::class, 'edit'])->name('satuan.edit');
Route::put('/master/satuan/update/{id}', [SatuanController::class, 'update'])->name('satuan.update');
Route::delete('/master/satuan/delete/{id}', [SatuanController::class, 'destroy'])->name('satuan.destroy');



/*
|--------------------------------------------------------------------------
| SUPPLIER
|--------------------------------------------------------------------------
*/
Route::get('/master/supplier', [SupplierController::class, 'index'])->name('supplier.index');
Route::get('/master/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
Route::post('/master/supplier/store', [SupplierController::class, 'store'])->name('supplier.store');
Route::get('/master/supplier/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
Route::put('/master/supplier/update/{id}', [SupplierController::class, 'update'])->name('supplier.update');
Route::delete('/master/supplier/delete/{id}', [SupplierController::class, 'destroy'])->name('supplier.destroy');



/*
|--------------------------------------------------------------------------
| DROPSHIPPER
|--------------------------------------------------------------------------
*/
Route::get('/master/dropshipper', [DropshipperController::class, 'index'])->name('dropshipper.index');
Route::get('/master/dropshipper/create', [DropshipperController::class, 'create'])->name('dropshipper.create');
Route::post('/master/dropshipper/store', [DropshipperController::class, 'store'])->name('dropshipper.store');
Route::get('/master/dropshipper/edit/{id}', [DropshipperController::class, 'edit'])->name('dropshipper.edit');
Route::post('/master/dropshipper/update/{id}', [DropshipperController::class, 'update'])->name('dropshipper.update');
Route::get('/master/dropshipper/delete/{id}', [DropshipperController::class, 'destroy'])->name('dropshipper.destroy');



/*
|--------------------------------------------------------------------------
| BARANG
|--------------------------------------------------------------------------
*/
Route::get('/master/barang', [BarangController::class, 'index'])->name('barang.index');
Route::get('/master/barang/create', [BarangController::class, 'create'])->name('barang.create');
Route::post('/master/barang/store', [BarangController::class, 'store'])->name('barang.store');
Route::get('/master/barang/edit/{id}', [BarangController::class, 'edit'])->name('barang.edit');
Route::post('/master/barang/update/{id}', [BarangController::class, 'update'])->name('barang.update');
Route::get('/master/barang/delete/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');



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
Route::get('/transaksi/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
Route::get('/transaksi/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
Route::post('/transaksi/pembelian/store', [PembelianController::class, 'store'])->name('pembelian.store');
Route::get('/transaksi/pembelian/edit/{id}', [PembelianController::class, 'edit'])->name('pembelian.edit');
Route::post('/transaksi/pembelian/update/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
Route::get('/transaksi/pembelian/delete/{id}', [PembelianController::class, 'destroy'])->name('pembelian.destroy');



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
Route::get('/transaksi/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
Route::get('/transaksi/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
Route::post('/transaksi/penjualan/store', [PenjualanController::class, 'store'])->name('penjualan.store');
Route::get('/transaksi/penjualan/edit/{id}', [PenjualanController::class, 'edit'])->name('penjualan.edit');
Route::post('/transaksi/penjualan/update/{id}', [PenjualanController::class, 'update'])->name('penjualan.update');
Route::get('/transaksi/penjualan/delete/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');



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
Route::get('/transaksi/manage-stok', [AdjustStokController::class, 'index'])->name('manange-stok.index');
Route::get('/transaksi/manage-stok/create', [AdjustStokController::class, 'create'])->name('manange-stok.create');
Route::post('/transaksi/manage-stok/store', [AdjustStokController::class, 'store'])->name('manange-stok.store');
Route::get('/transaksi/manage-stok/edit/{id}', [AdjustStokController::class, 'edit'])->name('manange-stok.edit');
Route::post('/transaksi/manage-stok/update/{id}', [AdjustStokController::class, 'update'])->name('manange-stok.update');
Route::get('/transaksi/manage-stok/delete/{id}', [AdjustStokController::class, 'destroy'])->name('manange-stok.destroy');



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