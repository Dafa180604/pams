<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Pemakaian;
use App\Models\Transaksi;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Models\BiayaDenda;
use DateTime;
class BelumLunasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataPemakaian = Pemakaian::all();
        $dataTransaksi = Transaksi::with('pemakaian')->where('status_pembayaran', '!=', 'lunas')->get();

        // Collect all petugas IDs
        $petugasIds = collect();
        foreach ($dataTransaksi as $transaksi) {
            if ($transaksi->pemakaian && $transaksi->pemakaian->petugas) {
                $petugasIds->push($transaksi->pemakaian->petugas);
            }
        }
        $petugasIds = $petugasIds->unique()->filter();

        // Get all petugas users in one query
        $petugasUsers = [];
        if ($petugasIds->isNotEmpty()) {
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }

        return view('belumlunas.index', [
            'dataTransaksi' => $dataTransaksi,
            'dataPencatatan' => $dataPemakaian,
            'petugasUsers' => $petugasUsers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
public function edit(string $id_transaksi)
{
    // Find the transaction with related data
    $data = Transaksi::with(['pemakaian'])->find($id_transaksi);
    if ($data) {
        // Calculate days since the recording date
        $waktuCatat = new DateTime($data->pemakaian->waktu_catat);
        $today = new DateTime();
        $interval = $waktuCatat->diff($today);
        $daysDifference = $interval->days;
        
        // Check if should apply, update, or remove late fee
        if ($daysDifference >= 1) {
            
            // Find the appropriate late fee entry based on days late
            $biayaDenda = BiayaDenda::where('jumlah_telat', $daysDifference)
                ->first();
            
            // If no exact match, find the appropriate category
            if (!$biayaDenda) {
                // Get all late fee categories ordered by jumlah_telat descending
                // This ensures we check from highest threshold to lowest
                $allDendaCategories = BiayaDenda::orderBy('jumlah_telat', 'desc')->get();
                
                // Find the appropriate category based on days difference
                // Use the highest threshold that the daysDifference meets or exceeds
                foreach ($allDendaCategories as $category) {
                    if ($daysDifference >= $category->jumlah_telat) {
                        $biayaDenda = $category;
                        break; // Take the first match (highest applicable threshold)
                    }
                }
            }
            
            // If a matching late fee category is found
            if ($biayaDenda) {
                // Check if we need to update the late fee
                $needsUpdate = false;
                
                if (!$data->id_biaya_denda) {
                    // No late fee applied yet
                    $needsUpdate = true;
                } else {
                    // Late fee exists, check if we need to update
                    $detailBiaya = json_decode($data->detail_biaya, true);
                    
                    // Update if:
                    // 1. Days have changed (increased or decreased), OR
                    // 2. Category has changed (different id_biaya_denda), OR
                    // 3. No detail recorded properly
                    if (isset($detailBiaya['denda']['jumlah_telat'])) {
                        $savedDays = $detailBiaya['denda']['jumlah_telat'];
                        $savedCategoryId = $detailBiaya['denda']['id'] ?? null;
                        
                        if ($daysDifference != $savedDays || 
                            $biayaDenda->id_biaya_denda != $savedCategoryId) {
                            $needsUpdate = true;
                        }
                    } else {
                        // Late fee exists but no detail recorded, update it
                        $needsUpdate = true;
                    }
                }
                
                if ($needsUpdate) {
                    // Use the direct Rupiah amount from the biaya_telat column
                    $rpDenda = $biayaDenda->biaya_telat;
                    
                    // Calculate original total (subtract old late fee if exists)
                    $originalTotal = $data->jumlah_rp;
                    if ($data->rp_denda) {
                        $originalTotal -= $data->rp_denda;
                    }
                    
                    // Update the transaction data with late fee information
                    $data->id_biaya_denda = $biayaDenda->id_biaya_denda;
                    $data->rp_denda = $rpDenda;
                    
                    // Update the total amount to include the new late fee
                    $data->jumlah_rp = $originalTotal + $rpDenda;
                    
                    // Update the detail_biaya JSON to include late fee
                    $detailBiaya = json_decode($data->detail_biaya, true);
                    $detailBiaya['denda'] = [
                        'id' => $biayaDenda->id_biaya_denda,
                        'jumlah_telat' => $daysDifference, // Actual days late
                        'kategori_telat' => $biayaDenda->jumlah_telat, // Category threshold used
                        'biaya_telat' => $rpDenda, // Direct Rupiah amount
                        'rp_denda' => $rpDenda,  // Same as biaya_telat since it's a direct amount
                        'updated_at' => date('Y-m-d H:i:s') // Track when fee was last updated
                    ];
                    $data->detail_biaya = json_encode($detailBiaya);
                    
                    // Save the updated transaction
                    $data->save();
                    
                    // Refresh the data after updates
                    $data = Transaksi::with(['pemakaian'])->find($id_transaksi);
                }
            }
        } else {
            // Not late anymore, remove late fee if exists
            if ($data->id_biaya_denda) {
                // Calculate original total (subtract old late fee)
                $originalTotal = $data->jumlah_rp;
                if ($data->rp_denda) {
                    $originalTotal -= $data->rp_denda;
                }
                
                // Remove late fee
                $data->id_biaya_denda = null;
                $data->rp_denda = 0;
                $data->jumlah_rp = $originalTotal;
                
                // Update detail_biaya to remove late fee
                $detailBiaya = json_decode($data->detail_biaya, true);
                if (isset($detailBiaya['denda'])) {
                    unset($detailBiaya['denda']);
                }
                $data->detail_biaya = json_encode($detailBiaya);
                
                // Save the updated transaction
                $data->save();
                
                // Refresh the data after updates
                $data = Transaksi::with(['pemakaian'])->find($id_transaksi);
            }
        }
    }
    
    // Get petugas user data
    $petugasUser = null;
    if ($data && $data->pemakaian && $data->pemakaian->petugas) {
        $petugasUser = Users::find($data->pemakaian->petugas);
    }
    
    return view('belumlunas.edit', compact('data', 'petugasUser'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id_transaksi)
{
    $request->validate([
        'uang_bayar' => 'required|numeric',
    ], [
        'uang_bayar.required' => 'Jumlah uang bayar wajib diisi!',
        'uang_bayar.numeric' => 'Jumlah uang bayar harus berupa angka!',
    ]);

    try {
        // Find the existing transaction
        $transaksi = Transaksi::findOrFail($id_transaksi);

        // Decode detail_biaya untuk mendapatkan informasi denda
        $detailBiaya = json_decode($transaksi->detail_biaya ?? '{}', true);
        $dendaAmount = 0;
        $jumlahRpFinal = $transaksi->jumlah_rp;

        // Cek apakah ada pemulihan denda
        if ($request->has('pemulihan_denda') && $request->pemulihan_denda == '1') {
            // Ambil nilai denda dari detail_biaya
            if (isset($detailBiaya['denda']) && isset($detailBiaya['denda']['rp_denda'])) {
                $dendaAmount = $detailBiaya['denda']['rp_denda'];
                
                // Pastikan jumlah_rp - rp_denda tidak negatif
                $jumlahRpFinal = max(0, $transaksi->jumlah_rp - $dendaAmount);
                
                // Simpan denda asli untuk riwayat
                $detailBiaya['denda']['rp_denda_asli'] = $detailBiaya['denda']['rp_denda'];
                
                // Update detail_biaya untuk menandai denda telah diampuni
                $detailBiaya['denda']['rp_denda'] = 0;
                $detailBiaya['denda']['status_pemulihan'] = true;
                $detailBiaya['denda']['tanggal_pemulihan'] = now()->toDateTimeString();
                $detailBiaya['denda']['petugas_pemulihan'] = auth()->user()->id_users ?? 'Unknown';
                
                // Simpan detail_biaya yang sudah diupdate
                $transaksi->detail_biaya = json_encode($detailBiaya);
            }
        }

        // Calculate kembalian berdasarkan jumlah final
        $kembalian = $request->uang_bayar - $jumlahRpFinal;

        // Update the attributes
        $transaksi->tgl_pembayaran = now();
        $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Lunas';
        $transaksi->uang_bayar = $request->uang_bayar;
        $transaksi->kembalian = $kembalian;
        $transaksi->jumlah_rp = $jumlahRpFinal; // Update jumlah_rp dengan nilai final
        
        // Update rp_denda menjadi 0 jika ada pemulihan
        if ($request->has('pemulihan_denda') && $request->pemulihan_denda == '1' && $dendaAmount > 0) {
            $transaksi->rp_denda = 0;
        }

        // Save the changes to the existing record
        $transaksi->save();
        
        // Ambil data pemakaian dari transaksi
        $pemakaian = $transaksi->pemakaian;

        // Cek apakah kolom petugas sudah ada isinya
        $existingPetugas = $pemakaian->petugas ?? '';

        // Ambil nama user yang sedang login
        $namaUser = auth()->user()->id_users;

        // Gabungkan nama jika belum ada dalam daftar (hindari duplikasi)
        $daftarPetugas = collect(explode(',', $existingPetugas))
            ->map(fn($item) => trim($item))
            ->filter()
            ->unique();

        // Tambahkan nama user login jika belum ada
        if (!$daftarPetugas->contains($namaUser)) {
            $daftarPetugas->push($namaUser);
        }

        // Simpan kembali ke kolom petugas sebagai string dipisahkan koma
        $pemakaian->petugas = $daftarPetugas->implode(',');

        // Simpan perubahan
        $pemakaian->save();

        // Jika status pembayaran adalah Lunas, tambahkan ke tabel laporan
        if ($transaksi->status_pembayaran == 'Lunas') {
            // Dapatkan bulan dan tahun saat ini
            $bulan = date('F'); // Nama bulan dalam bahasa Inggris
            $tahun = date('Y');

            // Ubah nama bulan ke bahasa Indonesia jika diperlukan
            $bulanIndonesia = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            ];

            // Get currently logged in user's name
            $userName = auth()->user()->id_users ?? 'Unknown';

            $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;
            
            // Tambahkan keterangan pemulihan denda jika ada
            $keteranganPemulihan = '';
            if ($dendaAmount > 0) {
                $keteranganPemulihan = " (Pemulihan denda: Rp " . number_format($dendaAmount, 0, ',', '.') . ")";
            }
            
            $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh admin {$userName}{$keteranganPemulihan}";

            // Cek apakah sudah ada laporan dengan bulan dan tahun yang sama
            $existingLaporan = Laporan::where('keterangan', 'like', "Terima bayar {$bulanTeks} {$tahun} oleh admin {$userName}%")->first();

            if ($existingLaporan) {
                // Jika sudah ada, update jumlah uang masuk dengan jumlah final
                $existingLaporan->uang_masuk += $jumlahRpFinal;
                $existingLaporan->keterangan = $keterangan; // Update keterangan jika ada pemulihan
                $existingLaporan->save();
            } else {
                // Jika belum ada, buat laporan baru
                $laporan = new Laporan();
                $laporan->tanggal = now(); // Tetap menggunakan tanggal hari ini untuk field tanggal
                $laporan->uang_masuk = $jumlahRpFinal; // Gunakan jumlah final
                $laporan->keterangan = $keterangan;
                $laporan->save();
            }
        }

        // Get updated transaction data with relationship for detail view
        $dataTransaksi = Transaksi::with('pemakaian')->find($id_transaksi);
        
        // Get petugas users data
        $petugasUsers = collect();
        if ($dataTransaksi && $dataTransaksi->pemakaian && $dataTransaksi->pemakaian->petugas) {
            $petugasIds = explode(',', $dataTransaksi->pemakaian->petugas);
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }

        // Generate the print receipt URL
        $cetakUrl = route('lunas.cetak', ['id_transaksi' => $id_transaksi]);

        // Redirect to detail page with success message and data
        return view('lunas.detail', compact('dataTransaksi', 'petugasUsers'))->with([
            'pembayaran_berhasil' => 'Pembayaran berhasil diproses.',
            'cetakUrl' => $cetakUrl,
            'pemulihan_denda' => $dendaAmount > 0 ? $dendaAmount : null
        ]);

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
