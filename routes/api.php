<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
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
