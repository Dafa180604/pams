<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PemakaianController;  
use App\Http\Controllers\Api\DashboardController;  
use App\Http\Controllers\Api\KeluhanController;  
use App\Http\Controllers\Api\TransaksiController;  


Route::middleware('auth:sanctum')->group(function () {  

    //Route Api Keluahan 
    Route::get('/keluhan', [KeluhanController::class, 'index']);
    //Route Api Transaksi 
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show']);  
    Route::get('/transaksi', [TransaksiController::class, 'index']); 
    //Route Api Pemakaian
    Route::get('/pemakaian', [PemakaianController::class, 'index']);
    Route::post('/pemakaian/store', [PemakaianController::class, 'store']);  
    Route::post('/pemakaian/bayar/langsung', [PemakaianController::class, 'bayar']); 
    Route::put('/pemakaian/bayar/lunas', [PemakaianController::class, 'update']);
    //Route Api Data Dashboard
    Route::get('/dashboard/petugas', [DashboardController::class, 'dataDashboardPetugas']);
    //Route Api ubah password
    Route::post('/ubah-password', [AuthController::class, 'ubahPassword']);

}); 

//Route Api Auth
Route::prefix('auth')->group(function () {
    // Login Route
    Route::post('/login', [AuthController::class, 'login']);
}); 


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
