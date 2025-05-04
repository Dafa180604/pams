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
        // Only apply late fee if at least 2 days late (according to your image)
        if ($daysDifference >= 2 && !$data->id_biaya_denda) {
            // Get all late fee categories
            $allDendaCategories = BiayaDenda::orderBy('jumlah_telat', 'asc')->get();
            $biayaDenda = null;
            // Find the appropriate category based on days difference
            foreach ($allDendaCategories as $index => $category) {
                $currentThreshold = $category->jumlah_telat;
                // Find the next threshold if available
                $nextThreshold = PHP_INT_MAX; // Default to maximum value
                if (isset($allDendaCategories[$index + 1])) {
                    $nextThreshold = $allDendaCategories[$index + 1]->jumlah_telat;
                }
                // Check if days difference falls in this range
                if ($daysDifference >= $currentThreshold && $daysDifference < $nextThreshold) {
                    $biayaDenda = $category;
                    break;
                }
            }
            // If a matching late fee category is found
            if ($biayaDenda) {
                // Calculate the late fee (percentage)
                $percentageFee = $biayaDenda->biaya_telat;
                $rpDenda = $data->jumlah_rp * ($percentageFee / 100);
                // Update the transaction data with late fee information
                $data->id_biaya_denda = $biayaDenda->id_biaya_denda;
                $data->rp_denda = $rpDenda;
                // Update the total amount to include the late fee
                $originalTotal = $data->jumlah_rp;
                $data->jumlah_rp = $originalTotal + $rpDenda;
                // Update the detail_biaya JSON to include late fee
                $detailBiaya = json_decode($data->detail_biaya, true);
                $detailBiaya['denda'] = [
                    'id' => $biayaDenda->id_biaya_denda,
                    'jumlah_telat' => $daysDifference, // Actual days late
                    'biaya_telat' => $biayaDenda->biaya_telat, // Percentage value
                    'rp_denda' => $rpDenda  // Calculated amount
                ];
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

            // Calculate kembalian
            $kembalian = $request->uang_bayar - $transaksi->jumlah_rp;

            // Update the attributes
            $transaksi->tgl_pembayaran = now();
            $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Lunas';
            $transaksi->uang_bayar = $request->uang_bayar;
            $transaksi->kembalian = $kembalian;
            $transaksi->pemakaian->petugas;

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
                $userName = auth()->user()->nama ?? 'Unknown';

                $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;
                $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh admin {$userName}";

                // Cek apakah sudah ada laporan dengan bulan dan tahun yang sama
                $existingLaporan = Laporan::where('keterangan', $keterangan)->first();

                if ($existingLaporan) {
                    // Jika sudah ada, update jumlah uang masuk
                    $existingLaporan->uang_masuk += $transaksi->jumlah_rp;
                    $existingLaporan->save();
                } else {
                    // Jika belum ada, buat laporan baru
                    $laporan = new Laporan();
                    $laporan->tanggal = now(); // Tetap menggunakan tanggal hari ini untuk field tanggal
                    $laporan->uang_masuk = $transaksi->jumlah_rp;
                    $laporan->keterangan = $keterangan;
                    $laporan->save();
                }
            }

            // Fetch the transaction data for the receipt view
            // Generate the print receipt URL
            $cetakUrl = route('lunas.cetak', ['id_transaksi' => $id_transaksi]);

            // Redirect to index with success message and JavaScript to open receipt in new tab
            return redirect()->route('lunas.index')->with([
                'pembayaran_berhasil' => 'Pembayaran berhasil diproses.',
                'cetakUrl' => $cetakUrl
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
