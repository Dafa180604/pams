<?php

namespace App\Http\Controllers;

use App\Models\Pemakaian;
use App\Models\Transaksi;
use App\Models\Users;
use App\Models\Laporan;
use App\Models\JadwalCatat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelunasanController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data jadwal catat dari tabel
        $jadwalCatat = JadwalCatat::whereIn('id_jadwal_catat', [1, 2])->get()->keyBy('id_jadwal_catat');

        // Base query untuk transaksi yang belum lunas
        $query = Transaksi::with([
            'pemakaian.users' => function ($query) {
                $query->where('status', '!=', 'Tidak Aktif')
                    ->orWhereNull('status');
            }
        ])->where('status_pembayaran', '!=', 'lunas');

        // Filter berdasarkan bulan
        if ($request->filled('end_date')) {
            $yearMonth = $request->end_date;
            $year = substr($yearMonth, 0, 4);
            $month = substr($yearMonth, 5, 2);
            
            $query->whereHas('pemakaian', function ($q) use ($year, $month) {
                $q->whereYear('waktu_catat', $year)
                  ->whereMonth('waktu_catat', $month);
            });
        }

        // Filter berdasarkan periode
        if ($request->filled('periode')) {
            $periode = $request->periode;
            
            $query->whereHas('pemakaian', function ($q) use ($periode, $jadwalCatat) {
                // Filter berdasarkan rentang tanggal waktu_catat dari tabel jadwal_catat
                if (isset($jadwalCatat[$periode])) {
                    $jadwal = $jadwalCatat[$periode];
                    $tanggalAwal = (int) $jadwal->tanggal_awal;
                    $tanggalAkhir = (int) $jadwal->tanggal_akhir;
                    $q->whereRaw('DAY(waktu_catat) BETWEEN ? AND ?', [$tanggalAwal, $tanggalAkhir]);
                }
            });
        }

        // Filter berdasarkan petugas
        if ($request->filled('petugas')) {
            $petugasId = $request->petugas;
            $query->whereHas('pemakaian', function ($q) use ($petugasId) {
                $q->where('petugas', 'LIKE', "%{$petugasId}%")
                  ->orWhere('petugas', $petugasId);
            });
        }

        $dataTransaksi = $query->get()->filter(function ($transaksi) {
            return $transaksi->pemakaian && $transaksi->pemakaian->users;
        });

        // Get petugas users untuk dropdown
        $petugasUsers = Users::where('role', 'petugas')
            ->where(function ($query) {
                $query->where('status', '!=', 'Tidak Aktif')
                    ->orWhereNull('status');
            })
            ->get();

        return view('pelunasan.index', [
            'dataTransaksi' => $dataTransaksi,
            'petugasUsers' => $petugasUsers,
            'jadwalCatat' => $jadwalCatat
        ]);
    }

    /**
     * Proses pelunasan banyak transaksi sekaligus
     */
    public function prosesPelunasan(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'change_amount' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Ambil semua transaksi yang akan dilunasi
            $transaksiList = Transaksi::with(['pemakaian', 'pemakaian.users'])
                ->whereIn('id_pemakaian', $request->transaction_ids)
                ->where('status_pembayaran', '!=', 'lunas')
                ->get();

            if ($transaksiList->isEmpty()) {
                return redirect('/pelunasan')->with('error', 'Tidak ada transaksi yang ditemukan atau semua sudah lunas');
            }

            $totalLunasAmount = 0;
            $updatedCount = 0;
            $petugasTransaksiData = []; // Array untuk menyimpan data per petugas

            // Hitung total amount yang harus dibayar
            $totalTagihan = $transaksiList->sum('jumlah_rp');

            // Proses setiap transaksi
            foreach ($transaksiList as $transaksi) {
                // Decode detail_biaya untuk cek denda
                $detailBiaya = json_decode($transaksi->detail_biaya ?? '{}', true);
                $jumlahRpFinal = $transaksi->jumlah_rp;

                // Update transaksi - Set uang_bayar = jumlah_rp untuk setiap transaksi
                $transaksi->tgl_pembayaran = now();
                $transaksi->status_pembayaran = 'Lunas';
                $transaksi->uang_bayar = $jumlahRpFinal; // Uang bayar sama dengan jumlah tagihan
                $transaksi->kembalian = 0; // Kembalian selalu 0
                $transaksi->save();

                // Ambil data petugas dari pemakaian untuk laporan (tidak update kolom petugas)
                $pemakaian = $transaksi->pemakaian;
                if ($pemakaian) {
                    $existingPetugas = $pemakaian->petugas ?? '';
                    
                    // Kumpulkan data per petugas untuk laporan berdasarkan petugas yang ada
                    $petugasIds = explode(',', $existingPetugas);
                    foreach ($petugasIds as $petugasId) {
                        $petugasId = trim($petugasId);
                        if (!empty($petugasId)) {
                            if (!isset($petugasTransaksiData[$petugasId])) {
                                $petugasTransaksiData[$petugasId] = [
                                    'total_amount' => 0,
                                    'jumlah_transaksi' => 0
                                ];
                            }
                            $petugasTransaksiData[$petugasId]['total_amount'] += $jumlahRpFinal;
                            $petugasTransaksiData[$petugasId]['jumlah_transaksi']++;
                        }
                    }
                }

                $totalLunasAmount += $jumlahRpFinal;
                $updatedCount++;
            }

            // Tambahkan ke laporan untuk setiap petugas
            $this->addToLaporanPerPetugas($petugasTransaksiData);

            DB::commit();

            return redirect('/pelunasan')->with('success', 'Data berhasil dilunasi.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect('/pelunasan')->with('error', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }

    /**
     * Tambahkan ke laporan per petugas
     */
    private function addToLaporanPerPetugas($petugasTransaksiData)
    {
        // Dapatkan bulan dan tahun saat ini
        $bulan = date('F');
        $tahun = date('Y');

        // Ubah nama bulan ke bahasa Indonesia
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

        $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;

        // Proses laporan untuk setiap petugas
        foreach ($petugasTransaksiData as $petugasId => $data) {
            // $userName = value dari kolom petugas tabel pemakaian
            $userName = $petugasId;
            
            $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh petugas {$userName}";

            // Cek apakah sudah ada laporan dengan bulan dan tahun yang sama dari petugas yang sama
            $existingLaporan = Laporan::where('keterangan', 'like', "Terima bayar {$bulanTeks} {$tahun} oleh petugas {$userName}%")->first();

            if ($existingLaporan) {
                // Jika sudah ada, update jumlah uang masuk
                $existingLaporan->uang_masuk += $data['total_amount'];
                $existingLaporan->keterangan = $keterangan; // Update keterangan
                $existingLaporan->save();
            } else {
                // Jika belum ada, buat laporan baru
                $laporan = new Laporan();
                $laporan->tanggal = now();
                $laporan->uang_masuk = $data['total_amount'];
                $laporan->keterangan = $keterangan;
                $laporan->save();
            }
        }
    }

    /**
     * Cetak struk penggunaan air dengan detail biaya berdasarkan filter
     */
    public function cetakStruk(Request $request)
{
    try {
        // Query dasar untuk transaksi belum lunas
        $query = Transaksi::with([
            'pemakaian.users' => function ($query) {
                $query->where('status', '!=', 'Tidak Aktif')
                    ->orWhereNull('status');
            }
        ])->where('status_pembayaran', '!=', 'lunas');

        // Filter berdasarkan bulan
        if ($request->filled('end_date')) {
            $yearMonth = $request->end_date;
            $year = substr($yearMonth, 0, 4);
            $month = substr($yearMonth, 5, 2);
            $query->whereHas('pemakaian', function ($q) use ($year, $month) {
                $q->whereYear('waktu_catat', $year)->whereMonth('waktu_catat', $month);
            });
        } else {
            // Default ke bulan saat ini jika tidak ada filter
            $year = date('Y');
            $month = date('m');
            $query->whereHas('pemakaian', function ($q) use ($year, $month) {
                $q->whereYear('waktu_catat', $year)->whereMonth('waktu_catat', $month);
            });
        }

        // Filter berdasarkan petugas (HAPUS BAGIAN PERIODE)
        if ($request->filled('petugas')) {
            $petugasId = $request->petugas;
            $query->whereHas('pemakaian', function ($q) use ($petugasId) {
                $q->where('petugas', 'LIKE', "%{$petugasId}%")->orWhere('petugas', $petugasId);
            });
        }
        
        // Ambil semua data transaksi sesuai filter
        $dataTransaksi = $query->get()->filter(function ($transaksi) {
            return $transaksi->pemakaian && $transaksi->pemakaian->users;
        });
        
        // Validasi data
        if ($dataTransaksi->isEmpty()) {
            return redirect()->back()->with('error', 'Data transaksi tidak ditemukan sesuai filter yang dipilih.');
        }

        // Get petugas users untuk mapping nama
        $petugasUsers = Users::where('role', 'petugas')
            ->where(function ($query) {
                $query->where('status', '!=', 'Tidak Aktif')
                      ->orWhereNull('status');
            })
            ->get()
            ->keyBy('id_users');

        // Menambahkan detail biaya untuk setiap transaksi
        $dataTransaksi = $dataTransaksi->map(function ($transaksi) {
            $detailBiaya = json_decode($transaksi->detail_biaya ?? '{}', true);
            $transaksi->detail_biaya_parsed = [
                'beban' => $detailBiaya['beban'] ?? ['tarif' => 0],
                'kategori' => $detailBiaya['kategori'] ?? [],
                'denda' => $detailBiaya['denda'] ?? ['rp_denda' => 0],
                'total_kategori' => collect($detailBiaya['kategori'] ?? [])->sum('subtotal'),
                'total_denda' => isset($detailBiaya['denda']) ? $detailBiaya['denda']['rp_denda'] : 0
            ];
            return $transaksi;
        });

        // Informasi filter untuk ditampilkan di struk (TANPA PERIODE)
        $filterInfo = [
            'tanggal' => $request->filled('end_date') ? $request->end_date : date('Y-m'),
            'petugas' => $request->filled('petugas') ? $request->petugas : null,
            'petugas_nama' => $request->filled('petugas') ? 
                ($petugasUsers->get($request->petugas)?->nama ?? 'Tidak Diketahui') : 'Semua Petugas',
            'total_transaksi' => $dataTransaksi->count(),
            'total_nominal' => $dataTransaksi->sum('jumlah_rp')
        ];

        // Return view cetak struk
        return view('pelunasan.cetak_struk', [
            'dataTransaksi' => $dataTransaksi,
            'petugasUsers' => $petugasUsers,
            'filterInfo' => $filterInfo
        ]);

    } catch (\Exception $e) {
        // Log error untuk debugging
        \Log::error('Error in cetakStruk: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses data cetak.');
    }
}

    /**
     * Cetak detail transaksi tunggal (untuk referensi)
     */
    public function cetak(string $id_transaksi)
    {
        $dataTransaksi = Transaksi::with('pemakaian')->find($id_transaksi);
        
        if (!$dataTransaksi) {
            return redirect()->back()->with('error', 'Data transaksi tidak ditemukan');
        }

        // Get petugas users data
        $petugasUsers = collect();
        if ($dataTransaksi && $dataTransaksi->pemakaian && $dataTransaksi->pemakaian->petugas) {
            $petugasIds = explode(',', $dataTransaksi->pemakaian->petugas);
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }

        return view('lunas.cetak', compact('dataTransaksi', 'petugasUsers'));
    }

    /**
     * Helper method untuk mendapatkan nama petugas
     */
    private function getPetugasNames($petugasIds, $petugasUsers)
    {
        if (empty($petugasIds)) {
            return '-';
        }

        $petugasIdArray = is_array($petugasIds) ? $petugasIds : explode(',', $petugasIds);
        $petugasNames = [];

        foreach ($petugasIdArray as $petugasId) {
            $petugasId = trim($petugasId);
            
            if (isset($petugasUsers[$petugasId])) {
                $petugasNames[] = $petugasUsers[$petugasId]->nama;
            } else {
                // Fallback query jika tidak ditemukan dalam collection
                $user = Users::find($petugasId);
                if ($user) {
                    $petugasNames[] = $user->nama;
                } else {
                    $petugasNames[] = "ID: {$petugasId}";
                }
            }
        }

        return implode(', ', $petugasNames);
    }
}