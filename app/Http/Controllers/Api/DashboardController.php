<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pemakaian;
use App\Models\Transaksi;
use App\Models\Keluhan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dataDashboardPetugas()
    {
        $petugasId = Auth::user()->id_users;
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // Ambil ID pemakaian milik petugas untuk bulan ini
        $pemakaianIds = Pemakaian::where('petugas', $petugasId)
            ->whereMonth('waktu_catat', $bulanIni)
            ->whereYear('waktu_catat', $tahunIni)
            ->pluck('id_pemakaian');

        // Ambil transaksi yang berkaitan dan sesuai bulan/tahun
        $transaksi = Transaksi::whereIn('id_pemakaian', $pemakaianIds)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->get();

        $jumlahKeluhan = Keluhan::all()->count();     

        // Hitung jumlah & total
        $jumlahLunas = $transaksi->where('status_pembayaran', 'Lunas')->count();
        $jumlahBelumLunas = $transaksi->where('status_pembayaran', 'Belum Bayar')->count();
        $totalUang = $transaksi->where('status_pembayaran', 'Lunas')->sum('jumlah_rp');

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => [
                'id_petugas' => $petugasId,
                'jumlah_keluhan' => $jumlahKeluhan,
                'jumlah_transaksi_lunas' => $jumlahLunas,
                'jumlah_transaksi_belum_lunas' => $jumlahBelumLunas,
                'total_uang_bulan_ini' => $totalUang,
            ]
        ]);
    }

}
