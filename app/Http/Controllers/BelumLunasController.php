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
        $dataPemakaian = Pemakaian::whereHas('users', function ($query) {
            $query->where('status', '!=', 'Tidak Aktif')
                ->orWhereNull('status');
        })->get();

        $dataTransaksi = Transaksi::with([
            'pemakaian' => function ($query) {
                $query->whereHas('users', function ($subQuery) {
                    $subQuery->where('status', '!=', 'Tidak Aktif')
                        ->orWhereNull('status');
                });
            }
        ])->where('status_pembayaran', '!=', 'lunas')->get();

        // Filter out transactions where pemakaian is null (due to user status filter)
        $dataTransaksi = $dataTransaksi->filter(function ($transaksi) {
            return $transaksi->pemakaian !== null;
        });

        // Collect all petugas IDs
        $petugasIds = collect();
        foreach ($dataTransaksi as $transaksi) {
            if ($transaksi->pemakaian && $transaksi->pemakaian->petugas) {
                $petugasIds->push($transaksi->pemakaian->petugas);
            }
        }
        $petugasIds = $petugasIds->unique()->filter();

        // Get all petugas users in one query (exclude inactive users)
        $petugasUsers = [];
        if ($petugasIds->isNotEmpty()) {
            $petugasUsers = Users::whereIn('id_users', $petugasIds)
                ->where(function ($query) {
                    $query->where('status', '!=', 'Tidak Aktif')
                        ->orWhereNull('status');
                })->get()->keyBy('id_users');
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
        // Find the transaction with related data including user (exclude inactive users and only belum lunas)
        $data = Transaksi::with([
            'pemakaian' => function ($query) {
                $query->whereHas('users', function ($subQuery) {
                    $subQuery->where('status', '!=', 'Tidak Aktif')
                        ->orWhereNull('status');
                });
            },
            'pemakaian.users' => function ($query) {
                $query->where('status', '!=', 'Tidak Aktif')
                    ->orWhereNull('status');
            }
        ])
            ->where('status_pembayaran', '!=', 'lunas')
            ->where(function ($query) {
                $query->where('status_pembayaran', 'Belum Bayar')
                    ->orWhere('status_pembayaran', 'belum lunas')
                    ->orWhereNull('status_pembayaran');
            })
            ->find($id_transaksi);

        // Check if transaction exists, has valid pemakaian (user not inactive), and is not paid yet
        if (!$data) {
            return redirect()->back()->with('error', '');//???
        }

        if (!$data->pemakaian) {
            return redirect()->back()->with('error', 'Transaksi tidak dapat diakses karena user tidak aktif.');
        }

        if ($data) {
            // Calculate days since the recording date
            $waktuCatat = new DateTime($data->pemakaian->waktu_catat);
            $today = new DateTime();
            $interval = $waktuCatat->diff($today);
            $daysDifference = $interval->days;

            // Check if should apply, update, or remove late fee
            // PERBAIKAN: Cek apakah ada kategori denda yang sesuai dengan jumlah hari terlambat
            $biayaDenda = null;

            if ($daysDifference >= 1) {
                // TERLAMBAT - Cari kategori denda yang sesuai

                // Find the appropriate late fee entry based on days late (exact match first)
                $biayaDenda = BiayaDenda::where('jumlah_telat', $daysDifference)
                    ->first();

                // If no exact match, find the appropriate category
                if (!$biayaDenda) {
                    // Get the minimum threshold that applies to this daysDifference
                    // PERBAIKAN: Cari kategori dengan threshold terkecil yang <= daysDifference
                    $biayaDenda = BiayaDenda::where('jumlah_telat', '<=', $daysDifference)
                        ->orderBy('jumlah_telat', 'desc') // Ambil yang terbesar dari yang memenuhi syarat
                        ->first();
                }
            }

            if ($biayaDenda) {
                // Ada kategori denda yang sesuai - Apply atau update denda

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

                        if (
                            $daysDifference != $savedDays ||
                            $biayaDenda->id_biaya_denda != $savedCategoryId
                        ) {
                            $needsUpdate = true;
                        }
                    } else {
                        // Late fee exists but no detail recorded, update it
                        $needsUpdate = true;
                    }
                }

                if ($needsUpdate) {
                    \Log::info("Applying late fee for transaction {$id_transaksi}. Days late: {$daysDifference}, Category: {$biayaDenda->jumlah_telat} days, Amount: Rp." . number_format($biayaDenda->biaya_telat));

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
                    $data = Transaksi::with([
                        'pemakaian.users' => function ($query) {
                            $query->where('status', '!=', 'Tidak Aktif')
                                ->orWhereNull('status');
                        }
                    ])->find($id_transaksi);
                }

                // [PERBAIKAN UTAMA] Check if user should be deactivated based on BiayaDenda configuration
                // User akan menjadi "Tidak Aktif" jika biaya_telat mencapai 1000000
                if ($biayaDenda->biaya_telat >= 1000000 && $data->pemakaian && $data->pemakaian->id_users) {
                    $user = Users::where('id_users', $data->pemakaian->id_users)
                        ->where(function ($query) {
                            $query->where('status', '!=', 'Tidak Aktif')
                                ->orWhereNull('status');
                        })->first();

                    if ($user && ($user->status == 'Aktif' || $user->status == null || $user->status == '')) {
                        $user->status = 'Tidak Aktif';
                        $user->save();

                        // Log for debugging with specific reason
                        \Log::info("User {$user->id_users} status changed to 'Tidak Aktif' due to biaya_telat reaching Rp " . number_format($biayaDenda->biaya_telat) . " (threshold: Rp 1,000,000)");
                    }
                }
            } else {
                // TIDAK ADA KATEGORI DENDA YANG SESUAI atau TIDAK TERLAMBAT
                // Hapus denda jika ada

                if ($data->id_biaya_denda || $data->rp_denda > 0) {
                    \Log::info("Removing late fee for transaction {$id_transaksi}. Days difference: {$daysDifference} (no applicable late fee category or not late)");

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

                    \Log::info("Late fee removed successfully for transaction {$id_transaksi}");

                    // Refresh the data after updates
                    $data = Transaksi::with([
                        'pemakaian.users' => function ($query) {
                            $query->where('status', '!=', 'Tidak Aktif')
                                ->orWhereNull('status');
                        }
                    ])->find($id_transaksi);

                    // [PERBAIKAN] Reactivate user if payment is no longer late and they don't have other high penalty transactions
                    if ($data->pemakaian && $data->pemakaian->id_users) {
                        $user = Users::where('id_users', $data->pemakaian->id_users)
                            ->where('status', 'Tidak Aktif')
                            ->first();

                        if ($user) {
                            // Check if user has other transactions with biaya_telat >= 1000000 before reactivating
                            $otherHighPenaltyTransactions = Transaksi::join('pemakaian', 'transaksi.id_pemakaian', '=', 'pemakaian.id_pemakaian')
                                ->join('biaya_denda', 'transaksi.id_biaya_denda', '=', 'biaya_denda.id_biaya_denda')
                                ->where('pemakaian.id_users', $user->id_users)
                                ->where('transaksi.id_transaksi', '!=', $id_transaksi)
                                ->where('transaksi.status_pembayaran', 'Belum Bayar')
                                ->where('biaya_denda.biaya_telat', '>=', 1000000)
                                ->exists();

                            // Only reactivate if no other high penalty transactions exist
                            if (!$otherHighPenaltyTransactions) {
                                $user->status = 'Aktif';
                                $user->save();

                                // Log for debugging
                                \Log::info("User {$user->id_users} status changed back to 'Aktif' - no more high penalty transactions (>= Rp 1,000,000)");
                            }
                        }
                    }
                } else {
                    // Log ketika tidak ada denda dan memang tidak perlu denda
                    if ($daysDifference >= 1) {
                        \Log::info("Transaction {$id_transaksi} is {$daysDifference} days late but no applicable late fee category found");
                    } else {
                        \Log::info("Transaction {$id_transaksi} is not late (days difference: {$daysDifference})");
                    }
                }
            }
        }

        // Get petugas user data (exclude inactive users)
        $petugasUser = null;
        if ($data && $data->pemakaian && $data->pemakaian->petugas) {
            $petugasUser = Users::where('id_users', $data->pemakaian->petugas)
                ->where(function ($query) {
                    $query->where('status', '!=', 'Tidak Aktif')
                        ->orWhereNull('status');
                })->first();
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
