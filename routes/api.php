<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PemakaianControllerApi;
  
Route::post('/pemakaian/store', [PemakaianControllerApi::class, 'store']);  
Route::post('/pemakaian/{id}/bayar', [PemakaianControllerApi::class, 'bayar']);  
Route::put('/pemakaian/{id}/bayar/{id_transaksi}', [PemakaianControllerApi::class, 'update']);


Route::prefix('auth')->group(function () {
    // Login Route
    Route::post('/login', [AuthController::class, 'login']);
}); 


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
