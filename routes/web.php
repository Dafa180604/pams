<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BelumLunasController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\EditSalahCatatController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LunasController;
use App\Http\Controllers\PemakaianController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CekLogin;
use App\Http\Middleware\CekRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategoriBiayaAirController;
use App\Http\Controllers\BebanBiayaController;
use App\Http\Controllers\GolonganController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\DendaController;

Route::get('/', [AuthController::class, 'login'])->name('login');
Route::post('loginsukses', [AuthController::class, 'loginsukses'])->name('loginsukses');
Route::get('auth/lupa-password', [AuthController::class, 'lupaPassword'])->name('auth.lupa-password');
Route::post('/api/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('/profile', ProfileController::class);Route::get('/profile/{username}/edit-password', [ProfileController::class, 'editPassword'])
        ->name('profile.edit-password');
    Route::put('/profile/{username}/update-password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::middleware(['auth', 'role:petugas'])->group(function () {
        Route::resource('/pemakaian', PemakaianController::class);
        Route::get('/pemakaian/{id_users}/meter-akhir', [PemakaianController::class, 'getMeterAkhir']);

    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('/petugas', PetugasController::class);
        Route::resource('/pelanggan', PelangganController::class);
        Route::resource('/bebanbiaya', BebanBiayaController::class);
        Route::resource('/biayagolongan', GolonganController::class);
        Route::resource('/kategoribiayaair', KategoriBiayaAirController::class);
        Route::post('/pelanggan/cetak-semua-qr', [PelangganController::class, 'cetakSemuaQr'])->name('pelanggan.cetakSemuaQr');
        Route::resource('/BiayaDenda', DendaController::class);
        Route::get('/petugas/{id_users}/pilih-pelanggan', [PetugasController::class, 'pilih'])->name('petugas.pilihPelanggan');
        Route::post('/petugas/{id_petugas}/update-akses', [PetugasController::class, 'updateAksesPelanggan'])->name('petugas.updateAkses');
        Route::resource('/belumlunas', BelumLunasController::class);
        Route::resource('/lunas', LunasController::class);
        Route::get('/lunas/cetak/{id_transaksi}', [LunasController::class, 'cetak'])->name('lunas.cetak');

        Route::resource('/pemakaian', PemakaianController::class);
        Route::get('/pemakaian/{id_users}/meter-akhir', [PemakaianController::class, 'getMeterAkhir']);
        //
        Route::post('pemakaian/bayar', [PemakaianController::class, 'bayar'])->name('pemakaian.bayar');
    });
    Route::resource('/pengeluaran', PengeluaranController::class);
    Route::resource('/laporan', LaporanController::class);
    Route::resource('/pengeluaran', PengeluaranController::class);
    Route::resource('/EditSalahCatat', EditSalahCatatController::class);
    Route::resource('/keluhan', KeluhanController::class);
    Route::resource('/DashboardAdmin', DashboardAdminController::class);
    Route::get('/api/laporan-data', [DashboardAdminController::class, 'getLaporanData'])->name('api.laporan-data');
    // Add this to your routes/api.php file:
    Route::get('/laporan-data', [App\Http\Controllers\DashboardAdminController::class, 'getLaporanData']);

});
