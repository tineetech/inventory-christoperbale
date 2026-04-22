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
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'loginView'])->name('login.view');
Route::post('/login', [AuthController::class, 'loginAction'])->name('login.action');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth.pengguna'])->group(function () {

    Route::get('/', function () {
        if (Auth::guard('pengguna')->check()) {
            return redirect('/dashboard');
        }
        return redirect('/login');
    });

    Route::get('/profile/edit', [ProfileController::class,'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class,'update'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD PAGE
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index')
        ->middleware('permission:lihat,dashboard');


    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */
    Route::get('/role', [RoleController::class, 'index'])
        ->middleware('permission:lihat,role');

    Route::get('/role/create', [RoleController::class, 'create'])
        ->middleware('permission:tambah,role');

    Route::post('/role/store', [RoleController::class, 'store'])
        ->middleware('permission:tambah,role');

    Route::get('/role/edit/{id}', [RoleController::class, 'edit'])
        ->middleware('permission:edit,role');

    Route::post('/role/update/{id}', [RoleController::class, 'update'])
        ->middleware('permission:edit,role');

    Route::get('/role/delete/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:hapus,role');


    /*
    |--------------------------------------------------------------------------
    | PENGGUNA
    |--------------------------------------------------------------------------
    */
    Route::get('/pengguna', [PenggunaController::class, 'index'])
        ->name('pengguna.index')
        ->middleware('permission:lihat,pengguna');

    Route::get('/pengguna/create', [PenggunaController::class, 'create'])
        ->name('pengguna.create')
        ->middleware('permission:tambah,pengguna');

    Route::post('/pengguna/store', [PenggunaController::class, 'store'])
        ->name('pengguna.store')
        ->middleware('permission:tambah,pengguna');

    Route::get('/pengguna/edit/{id}', [PenggunaController::class, 'edit'])
        ->name('pengguna.edit')
        ->middleware('permission:edit,pengguna');

    Route::post('/pengguna/update/{id}', [PenggunaController::class, 'update'])
        ->name('pengguna.update')
        ->middleware('permission:edit,pengguna');

    Route::delete('/pengguna/delete/{id}', [PenggunaController::class, 'destroy'])
        ->name('pengguna.destroy')
        ->middleware('permission:hapus,pengguna');


    /*
    |--------------------------------------------------------------------------
    | HAK AKSES
    |--------------------------------------------------------------------------
    */
    Route::get('/hak-akses', [HakAksesController::class, 'index'])
        ->name('hak_akses.index')
        ->middleware('permission:lihat,hak_akses');

    Route::get('/hak-akses/create', [HakAksesController::class, 'create'])
        ->name('hak_akses.create')
        ->middleware('permission:tambah,hak_akses');

    Route::post('/hak-akses/store', [HakAksesController::class, 'store'])
        ->name('hak_akses.store')
        ->middleware('permission:tambah,hak_akses');

    Route::get('/hak-akses/edit/{id}', [HakAksesController::class, 'edit'])
        ->name('hak_akses.edit')
        ->middleware('permission:edit,hak_akses');

    Route::post('/hak-akses/update/{id}', [HakAksesController::class, 'update'])
        ->name('hak_akses.update')
        ->middleware('permission:edit,hak_akses');

    Route::delete('/hak-akses/delete/{id}', [HakAksesController::class, 'destroy'])
        ->name('hak_akses.destroy')
        ->middleware('permission:hapus,hak_akses');


    /*
    |--------------------------------------------------------------------------
    | ROLE HAK AKSES
    |--------------------------------------------------------------------------
    */
    Route::get('/role-hak-akses', [RoleHakAksesController::class, 'index'])
        ->name('role_hak_akses.index')
        ->middleware('permission:lihat,role_hak_akses');

    Route::put('/role-hak-akses/{role}', [RoleHakAksesController::class, 'update'])
        ->name('role_hak_akses.update')
        ->middleware('permission:edit,role_hak_akses');


    /*
    |--------------------------------------------------------------------------
    | SATUAN
    |--------------------------------------------------------------------------
    */
    Route::get('/master/satuan', [SatuanController::class, 'index'])
        ->name('satuan.index')
        ->middleware('permission:lihat,satuan');

    Route::get('/master/satuan/create', [SatuanController::class, 'create'])
        ->name('satuan.create')
        ->middleware('permission:tambah,satuan');

    Route::post('/master/satuan/store', [SatuanController::class, 'store'])
        ->name('satuan.store')
        ->middleware('permission:tambah,satuan');

    Route::get('/master/satuan/edit/{id}', [SatuanController::class, 'edit'])
        ->name('satuan.edit')
        ->middleware('permission:edit,satuan');

    Route::put('/master/satuan/update/{id}', [SatuanController::class, 'update'])
        ->name('satuan.update')
        ->middleware('permission:edit,satuan');

    Route::delete('/master/satuan/delete/{id}', [SatuanController::class, 'destroy'])
        ->name('satuan.destroy')
        ->middleware('permission:hapus,satuan');


    /*
    |--------------------------------------------------------------------------
    | SUPPLIER
    |--------------------------------------------------------------------------
    */
    Route::get('/master/supplier', [SupplierController::class, 'index'])
        ->name('supplier.index')
        ->middleware('permission:lihat,supplier');

    Route::get('/master/supplier/create', [SupplierController::class, 'create'])
        ->name('supplier.create')
        ->middleware('permission:tambah,supplier');

    Route::post('/master/supplier/store', [SupplierController::class, 'store'])
        ->name('supplier.store')
        ->middleware('permission:tambah,supplier');

    Route::get('/master/supplier/edit/{id}', [SupplierController::class, 'edit'])
        ->name('supplier.edit')
        ->middleware('permission:edit,supplier');

    Route::put('/master/supplier/update/{id}', [SupplierController::class, 'update'])
        ->name('supplier.update')
        ->middleware('permission:edit,supplier');

    Route::delete('/master/supplier/delete/{id}', [SupplierController::class, 'destroy'])
        ->name('supplier.destroy')
        ->middleware('permission:hapus,supplier');


    /*
    |--------------------------------------------------------------------------
    | DROPSHIPPER
    |--------------------------------------------------------------------------
    */
    Route::get('/master/dropshipper', [DropshipperController::class, 'index'])
        ->name('dropshipper.index')
        ->middleware('permission:lihat,dropshipper');

    Route::get('/master/dropshipper/create', [DropshipperController::class, 'create'])
        ->name('dropshipper.create')
        ->middleware('permission:tambah,dropshipper');

    Route::post('/master/dropshipper/store', [DropshipperController::class, 'store'])
        ->name('dropshipper.store')
        ->middleware('permission:tambah,dropshipper');

    Route::get('/master/dropshipper/edit/{id}', [DropshipperController::class, 'edit'])
        ->name('dropshipper.edit')
        ->middleware('permission:edit,dropshipper');

    Route::put('/master/dropshipper/update/{id}', [DropshipperController::class, 'update'])
        ->name('dropshipper.update')
        ->middleware('permission:edit,dropshipper');

    Route::delete('/master/dropshipper/delete/{id}', [DropshipperController::class, 'destroy'])
        ->name('dropshipper.destroy')
        ->middleware('permission:hapus,dropshipper');


    /*
    |--------------------------------------------------------------------------
    | BARANG
    |--------------------------------------------------------------------------
    */
    Route::get('/master/barang', [BarangController::class, 'index'])
        ->name('barang.index')
        ->middleware('permission:lihat,barang');

    Route::get('/master/barang/create', [BarangController::class, 'create'])
        ->name('barang.create')
        ->middleware('permission:tambah,barang');

    Route::post('/master/barang/store', [BarangController::class, 'store'])
        ->name('barang.store')
        ->middleware('permission:tambah,barang');

    Route::get('/master/barang/edit/{id}', [BarangController::class, 'edit'])
        ->name('barang.edit')
        ->middleware('permission:edit,barang');

    Route::put('/master/barang/update/{id}', [BarangController::class, 'update'])
        ->name('barang.update')
        ->middleware('permission:edit,barang');

    Route::delete('/master/barang/delete/{id}', [BarangController::class, 'destroy'])
        ->name('barang.destroy')
        ->middleware('permission:hapus,barang');

    Route::get('/api/product/search', [BarangController::class, 'search'])
        ->middleware('permission:lihat,barang');

    Route::get('/api/product/barcode/{id}', [BarangController::class, 'barcode'])
        ->middleware('permission:lihat,barang');

    Route::get('/barang/{id}/barcode/download', [BarangController::class, 'downloadBarcode'])
        ->name('barang.barcode.download')
        ->middleware('permission:lihat,barang');


    /*
    |--------------------------------------------------------------------------
    | STOK BARANG
    |--------------------------------------------------------------------------
    */
    Route::get('/stok-barang', [StokBarangController::class, 'index'])
        ->middleware('permission:lihat,manajemen_stok');

    Route::get('/stok-barang/create', [StokBarangController::class, 'create'])
        ->middleware('permission:tambah,manajemen_stok');

    Route::post('/stok-barang/store', [StokBarangController::class, 'store'])
        ->middleware('permission:tambah,manajemen_stok');

    Route::get('/stok-barang/edit/{id}', [StokBarangController::class, 'edit'])
        ->middleware('permission:edit,manajemen_stok');

    Route::post('/stok-barang/update/{id}', [StokBarangController::class, 'update'])
        ->middleware('permission:edit,manajemen_stok');

    Route::get('/stok-barang/delete/{id}', [StokBarangController::class, 'destroy'])
        ->middleware('permission:edit,manajemen_stok');


    /*
    |--------------------------------------------------------------------------
    | STOK MOVEMENT
    |--------------------------------------------------------------------------
    */
    Route::get('/stok-movement', [StokMovementController::class, 'index'])
        ->middleware('permission:lihat,manajemen_stok');

    Route::get('/stok-movement/create', [StokMovementController::class, 'create'])
        ->middleware('permission:tambah,manajemen_stok');

    Route::post('/stok-movement/store', [StokMovementController::class, 'store'])
        ->middleware('permission:tambah,manajemen_stok');

    Route::get('/stok-movement/edit/{id}', [StokMovementController::class, 'edit'])
        ->middleware('permission:edit,manajemen_stok');

    Route::post('/stok-movement/update/{id}', [StokMovementController::class, 'update'])
        ->middleware('permission:edit,manajemen_stok');

    Route::get('/stok-movement/delete/{id}', [StokMovementController::class, 'destroy'])
        ->middleware('permission:edit,manajemen_stok');


    /*
    |--------------------------------------------------------------------------
    | PEMBELIAN
    |--------------------------------------------------------------------------
    */
    Route::get('/transaksi/pembelian', [PembelianController::class, 'index'])
        ->name('pembelian.index')
        ->middleware('permission:lihat,pembelian');

    Route::get('/transaksi/pembelian/create', [PembelianController::class, 'create'])
        ->name('pembelian.create')
        ->middleware('permission:buat,pembelian');

    Route::post('/transaksi/pembelian/store', [PembelianController::class, 'store'])
        ->name('pembelian.store')
        ->middleware('permission:buat,pembelian');

    Route::get('/transaksi/pembelian/edit/{id}', [PembelianController::class, 'edit'])
        ->name('pembelian.edit')
        ->middleware('permission:buat,pembelian');

    Route::post('/transaksi/pembelian/update/{id}', [PembelianController::class, 'update'])
        ->name('pembelian.update')
        ->middleware('permission:buat,pembelian');

    Route::delete('/transaksi/pembelian/delete/{id}', [PembelianController::class, 'destroy'])
        ->name('pembelian.destroy')
        ->middleware('permission:hapus,pembelian');


    /*
    |--------------------------------------------------------------------------
    | PENJUALAN
    |--------------------------------------------------------------------------
    */
    Route::get('/transaksi/penjualan', [PenjualanController::class, 'index'])
        ->name('penjualan.index')
        ->middleware('permission:lihat,penjualan');

    Route::get('/transaksi/penjualan/create', [PenjualanController::class, 'create'])
        ->name('penjualan.create')
        ->middleware('permission:buat,penjualan');

    Route::post('/transaksi/penjualan/store', [PenjualanController::class, 'store'])
        ->name('penjualan.store')
        ->middleware('permission:buat,penjualan');

    Route::get('/transaksi/penjualan/edit/{id}', [PenjualanController::class, 'edit'])
        ->name('penjualan.edit')
        ->middleware('permission:buat,penjualan');

    Route::post('/transaksi/penjualan/update/{id}', [PenjualanController::class, 'update'])
        ->name('penjualan.update')
        ->middleware('permission:buat,penjualan');

    Route::delete('/transaksi/penjualan/delete/{id}', [PenjualanController::class, 'destroy'])
        ->name('penjualan.destroy')
        ->middleware('permission:hapus,penjualan');


    /*
    |--------------------------------------------------------------------------
    | MANAGE STOK
    |--------------------------------------------------------------------------
    */
    Route::get('/transaksi/manage-stok', [AdjustStokController::class, 'index'])
        ->name('manage-stok.index')
        ->middleware('permission:lihat,manajemen_stok');

    Route::get('/transaksi/manage-stok/create', [AdjustStokController::class, 'create'])
        ->name('manage-stok.create')
        ->middleware('permission:tambah,manajemen_stok');

    Route::post('/transaksi/manage-stok/store', [AdjustStokController::class, 'store'])
        ->name('manage-stok.store')
        ->middleware('permission:tambah,manajemen_stok');

    Route::get('/transaksi/manage-stok/edit/{id}', [AdjustStokController::class, 'edit'])
        ->name('manage-stok.edit')
        ->middleware('permission:edit,manajemen_stok');

    Route::post('/transaksi/manage-stok/update/{id}', [AdjustStokController::class, 'update'])
        ->name('manage-stok.update')
        ->middleware('permission:edit,manajemen_stok');

    Route::get('/transaksi/manage-stok/delete/{id}', [AdjustStokController::class, 'destroy'])
        ->name('manage-stok.destroy')
        ->middleware('permission:edit,manajemen_stok');

    /*
    |--------------------------------------------------------------------------
    | LAPORAN
    |--------------------------------------------------------------------------
    */
    Route::get('/laporan/pembelian', [ReportController::class, 'pembelian'])->name('laporan.pembelian');
    Route::get('/laporan/pembelian/excel', [ReportController::class, 'pembelianExcel'])->name('laporan.pembelian.excel');
    Route::get('/laporan/pembelian/print', [ReportController::class, 'pembelianPrint'])->name('laporan.pembelian.print');
    Route::get('/laporan/penjualan', [ReportController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/laporan/penjualan/excel', [ReportController::class, 'penjualanExcel'])->name('laporan.penjualan.excel');
    Route::get('/laporan/penjualan/print', [ReportController::class, 'penjualanPrint'])->name('laporan.penjualan.print');
    Route::get('/laporan/stok', [ReportController::class, 'stok'])->name('laporan.stok');
    Route::get('/laporan/stok/excel', [ReportController::class, 'stokExcel'])->name('laporan.stok.excel');
    Route::get('/laporan/stok/print', [ReportController::class, 'stokPrint'])->name('laporan.stok.print');
    Route::get('/laporan/barang', [ReportController::class, 'barang'])->name('laporan.barang');
    Route::get('/laporan/barang/pdf', [ReportController::class, 'barangPdf'])->name('laporan.barang.pdf');
    Route::get('/laporan/barang/excel', [ReportController::class, 'barangExcel'])->name('laporan.barang.excel');
    Route::get('/laporan/barang/print', [ReportController::class, 'barangPrint'])->name('laporan.barang.print');



    /*
    |--------------------------------------------------------------------------
    | BACKUP DATABASE
    |--------------------------------------------------------------------------
    */
    Route::get('/backup-database', [BackupController::class, 'index'])
        ->name('backup.index')
        ->middleware('permission:lihat,backup_database');

    Route::post('/backup-database', [BackupController::class, 'backup'])
        ->name('backup.run')
        ->middleware('permission:buat,backup_database');
});
