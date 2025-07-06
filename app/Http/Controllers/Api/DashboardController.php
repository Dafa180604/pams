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

    // Ambil akses pelanggan dari user
    $aksesPelanggan = json_decode(Auth::user()->akses_pelanggan, true);
    if (!is_array($aksesPelanggan)) {
        $aksesPelanggan = [];
    }

    // Filter hanya pelanggan dengan status "Aktif"
    $pelangganAktif = collect($aksesPelanggan)->filter(function($pelangganId) {
        return \App\Models\User::where('id_users', $pelangganId)
            ->where('status', 'Aktif')
            ->exists();
    })->values()->toArray();

    // Total yang harus dicatat = semua pelanggan aktif dalam akses_pelanggan
    $totalHarusDicatat = count($pelangganAktif);

    // Ambil ID pemakaian milik petugas untuk bulan ini, hanya untuk pelanggan aktif
    $pemakaian = Pemakaian::where('petugas', $petugasId)
        ->whereMonth('waktu_catat', $bulanIni)
        ->whereYear('waktu_catat', $tahunIni)
        ->whereHas('users', function ($query) {
            $query->where('status', 'Aktif');
        })
        ->get();

    $pemakaianIds = $pemakaian->pluck('id_pemakaian');

    // Ambil transaksi yang berkaitan dan sesuai bulan/tahun
    $transaksi = Transaksi::whereIn('id_pemakaian', $pemakaianIds)
        ->whereMonth('created_at', $bulanIni)
        ->whereYear('created_at', $tahunIni)
        ->get();

    // Ambil id_users (pelanggan) yang sudah dicatat dari Pemakaian
    $dicatatIds = $pemakaian->pluck('id_users')->unique();

    // Hitung yang belum dicatat (akses pelanggan aktif yang tidak ada di pemakaian)
    $totalBelumDicatat = collect($pelangganAktif)->diff($dicatatIds)->count();

    // Total uang tetap seperti sebelumnya (yang Lunas)
    $totalUang = $transaksi->where('status_pembayaran', 'Lunas')->sum('jumlah_rp');

    // Hitung jumlah keluhan untuk tambahan info
    $jumlahKeluhan = Keluhan::count();

    // Ambil data transaksi terbaru dari function baru
    $transaksiTerbaru = $this->getTransaksiTerbaruData($petugasId);

    return response()->json([
        'success' => true,
        'message' => 'Data dashboard berhasil diambil',
        'data' => [
            'id_petugas' => $petugasId,
            'jumlah_keluhan' => $jumlahKeluhan,
            'total_harus_dicatat_bulan_ini' => $totalHarusDicatat,
            'total_belum_dicatat_bulan_ini' => $totalBelumDicatat,
            'total_uang_bulan_ini' => $totalUang,
            'transaksi_terbaru' => $transaksiTerbaru,
        ]
    ]);
}

private function getTransaksiTerbaruData($petugasId)
{
    $transaksi = Transaksi::with(['pemakaian.users'])
        ->whereHas('pemakaian', function ($query) use ($petugasId) {
            $query->where('petugas', $petugasId);
        })
        ->whereHas('pemakaian.users', function ($query) {
            $query->where('status', 'Aktif');
        })
        ->latest()
        ->first();

    if (!$transaksi) {
        return null;
    }

    return [
        'id_transaksi'       => $transaksi->id_transaksi,
        'id_pemakaian'       => $transaksi->pemakaian->id_pemakaian ?? null,
        'id_pelanggan'       => $transaksi->pemakaian->users->id_users ?? null,
        'nama_pelanggan'     => $transaksi->pemakaian->users->nama ?? '-',
        'alamat_pelanggan' => $transaksi->pemakaian && $transaksi->pemakaian->users
            ? trim("{$transaksi->pemakaian->users->alamat}, RT {$transaksi->pemakaian->users->rt} RW {$transaksi->pemakaian->users->rw}")
            : '-',
        'tanggal_pencatatan' => $transaksi->pemakaian->waktu_catat ?? null,
        'tanggal_pembayaran' => $transaksi->tgl_pembayaran,
        'meter_awal'         => $transaksi->pemakaian->meter_awal ?? null,
        'meter_akhir'        => $transaksi->pemakaian->meter_akhir ?? null,
        'jumlah_pemakaian'   => $transaksi->pemakaian->jumlah_pemakaian ?? null,
        'denda'              => $transaksi->rp_denda,
        'total_tagihan'      => $transaksi->jumlah_rp,
        'foto_meteran'       => $transaksi->pemakaian->foto_meteran ?? null,  
        'status_pembayaran'  => $transaksi->tgl_pembayaran ? 'Lunas' : null,
        'detail_biaya'       => json_decode($transaksi->detail_biaya),
    ];
}

    public function dataDashboardPelanggan()
    {
        $pelangganId = Auth::user()->id_users;
        $namaPelanggan = Auth::user()->nama;

        // Ambil pemakaian terbaru oleh pelanggan ini
        $pemakaianTerbaru = Pemakaian::where('id_users', $pelangganId)
            ->latest('waktu_catat')
            ->first();

        $transaksiTerbaru = null;

        if ($pemakaianTerbaru) {
            $transaksi = Transaksi::where('id_pemakaian', $pemakaianTerbaru->id_pemakaian)
                ->latest('created_at')
                ->with(['pemakaian.users']) 
                ->first();

            if ($transaksi) {
                $transaksiTerbaru = [
                    'id_transaksi'       => $transaksi->id_transaksi,
                    'id_pemakaian'       => $transaksi->pemakaian->id_pemakaian ?? null,
                    'id_pelanggan'       => $transaksi->pemakaian->users->id_users ?? null,
                    'nama_pelanggan'     => $transaksi->pemakaian->users->nama ?? '-',
                    'alamat_pelanggan'   => $transaksi->pemakaian && $transaksi->pemakaian->users
                        ? trim("{$transaksi->pemakaian->users->alamat}, RT {$transaksi->pemakaian->users->rt} RW {$transaksi->pemakaian->users->rw}")
                        : '-',
                    'tanggal_pencatatan' => $transaksi->pemakaian->waktu_catat ?? null,
                    'tanggal_pembayaran' => $transaksi->tgl_pembayaran,
                    'meter_awal'         => $transaksi->pemakaian->meter_awal ?? null,
                    'meter_akhir'        => $transaksi->pemakaian->meter_akhir ?? null,
                    'jumlah_pemakaian'   => $transaksi->pemakaian->jumlah_pemakaian ?? null,
                    'denda'              => $transaksi->rp_denda,
                    'total_tagihan'      => $transaksi->jumlah_rp,
                    'foto_meteran'       => $transaksi->pemakaian->foto_meteran ?? null,
                    'status_pembayaran'  => $transaksi->tgl_pembayaran ? 'Lunas' : null,
                    'detail_biaya'       => json_decode($transaksi->detail_biaya),
                ];
            }
        }

        // Ambil keluhan terakhir oleh pelanggan ini
        $keluhanTerbaru = Keluhan::where('id_users', $pelangganId)
            ->latest('created_at')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard pelanggan berhasil diambil',
            'data' => [
                'id_pelanggan' => $pelangganId,
                'nama_pelanggan' => $namaPelanggan,
                'transaksi_terbaru' => $transaksiTerbaru,
                'keluhan_terbaru' => $keluhanTerbaru,
            ]
        ]);
    }

}
