<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Users;
use Illuminate\Support\Facades\DB;

class RiwayatPemulihan extends Controller
{
    public function index()
    {
        $dataTransaksi = Transaksi::with('pemakaian')
            ->where('status_pembayaran', '!=', 'Belum Bayar')
            ->whereNotNull('rp_pengampunan')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Collect all petugas IDs
        $petugasIds = collect();
        foreach ($dataTransaksi as $transaksi) {
            if ($transaksi->pemakaian && $transaksi->pemakaian->petugas) {
                $ids = explode(',', $transaksi->pemakaian->petugas);
                foreach ($ids as $id) {
                    $petugasIds->push(trim($id));
                }
            }
        }
        $petugasIds = $petugasIds->unique()->filter();
        
        // Get all petugas users in one query
        $petugasUsers = [];
        if ($petugasIds->isNotEmpty()) {
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }
        
        // Hitung total RP Pengampunan
        $totalRpPengampunan = $dataTransaksi->sum('rp_pengampunan');
        
        return view('pemulihan.riwayat_pemulihan', [
            'dataTransaksi' => $dataTransaksi,
            'petugasUsers' => $petugasUsers,
            'totalRpPengampunan' => $totalRpPengampunan
        ]);
    }
}