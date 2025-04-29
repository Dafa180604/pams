<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PemakaianController;  


//Route Api Pemakaian 
Route::get('/pemakaian', [PemakaianController::class, 'indexPemakaian']);
Route::put('/transaksi/bayar/{id_transaksi}', [PemakaianController::class, 'update']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pemakaian/store', [PemakaianController::class, 'store']);  
    Route::post('/pemakaian/bayar', [PemakaianController::class, 'bayar']); 
});


//Route Api Auth
Route::prefix('auth')->group(function () {
    // Login Route
    Route::post('/login', [AuthController::class, 'login']);
}); 


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
