<?php

namespace App\Http\Controllers;

use App\Models\Pemakaian;
use App\Models\Transaksi;
use App\Models\Users;
use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EditSalahCatatController extends Controller
{
    /**
     * Display the form for editing meter reading.
     */
    public function edit($id_transaksi)
    {
        // Find the transaction record
        $transaksi = Transaksi::findOrFail($id_transaksi);

        // Find the related pemakaian
        $pemakaian = Pemakaian::findOrFail($transaksi->id_pemakaian);

        // Get user information
        $user = Users::find($pemakaian->id_users);

        if (!$user) {
            return redirect()->back()->with('error', 'Data pengguna tidak ditemukan.');
        }

        return view('EditSalahCatat.edit', compact('pemakaian', 'transaksi', 'user'));
    }

    /**
     * Update the meter reading and recalculate all dependent data.
     */
    public function update(Request $request, $id_pemakaian)
    {
        // Validate request
        $request->validate([
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
        ], [
            'meter_awal.required' => 'Meter Awal Wajib Diisi!',
            'meter_awal.numeric' => 'Meter Awal Wajib di Isi Angka!',
            'meter_akhir.required' => 'Meter Akhir Wajib Diisi!',
            'meter_akhir.numeric' => 'Meter Akhir Wajib di Isi Angka!',
            'meter_akhir.gte' => 'Meter Akhir Tidak Boleh Lebih Kecil dari Meter Awal!',
        ]);

        // Begin database transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Get the pemakaian record
            $pemakaian = Pemakaian::findOrFail($id_pemakaian);
            $oldJumlahPemakaian = $pemakaian->jumlah_pemakaian;

            // Get related transaction
            $transaksi = Transaksi::where('id_pemakaian', $id_pemakaian)->first();
            if (!$transaksi) {
                throw new \Exception('Transaksi terkait tidak ditemukan');
            }

            // Store old values for reference
            $oldJumlahRp = $transaksi->jumlah_rp;
            $wasAlreadyPaid = ($transaksi->status_pembayaran == 'Lunas');

            // Update pemakaian data
            $pemakaian->meter_awal = $request->meter_awal;
            $pemakaian->meter_akhir = $request->meter_akhir;
            $pemakaian->jumlah_pemakaian = $request->meter_akhir - $request->meter_awal;

            // Save pemakaian without catatan_edit
            $pemakaian->save();

            // Recalculate bill
            $billDetails = $this->calculateBill($pemakaian->jumlah_pemakaian);

            // Update transaction details
            $transaksi->id_beban_biaya = $billDetails['beban_id'];
            $transaksi->id_kategori_biaya = $billDetails['kategori_id'];
            $transaksi->jumlah_rp = $billDetails['total'];
            $transaksi->detail_biaya = json_encode($billDetails['detail']);

            // If payment was already made, adjust kembalian accordingly
            if ($wasAlreadyPaid && $transaksi->uang_bayar) {
                $transaksi->kembalian = $transaksi->uang_bayar - $billDetails['total'];

                // Only update catatan_edit if the column exists
                if (Schema::hasColumn('transaksi', 'catatan_edit')) {
                    // Add edit history to transaction
                    $editHistory = json_decode($transaksi->catatan_edit ?? '[]', true);
                    $editHistory[] = [
                        'tanggal_edit' => now()->format('Y-m-d H:i:s'),
                        'petugas_edit' => Auth::user()->nama,
                        'nilai_lama' => [
                            'jumlah_rp' => $oldJumlahRp,
                            'kembalian' => $transaksi->getOriginal('kembalian')
                        ],
                        'nilai_baru' => [
                            'jumlah_rp' => $billDetails['total'],
                            'kembalian' => $transaksi->uang_bayar - $billDetails['total']
                        ]
                    ];
                    $transaksi->catatan_edit = json_encode($editHistory);
                }
            }

            $transaksi->save();

            // Update report if payment was already made
            if ($wasAlreadyPaid) {
                // Get payment month and year
                $paymentDate = $transaksi->tgl_pembayaran ?? now();
                $bulan = date('F', strtotime($paymentDate));
                $tahun = date('Y', strtotime($paymentDate));

                // Convert month name to Indonesian
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

                // Ambil nilai petugas dari database
                $petugasValue = $pemakaian->petugas;

                // Cek apakah terdapat koma dalam string petugas
                if (strpos($petugasValue, ',') !== false) {
                    // Kondisi 2: Ada koma, berarti format "salsa,Dafa"
                    $petugasArray = explode(',', $petugasValue);
                    // Trim semua elemen dan buang spasi berlebih
                    $petugasArray = array_map('trim', $petugasArray);
                    // Ambil petugas terakhir (setelah koma)
                    $petugasName = end($petugasArray);
                    // Gunakan awalan "admin"
                    $prefiks = 'admin';
                } else {
                    // Kondisi 1: Tidak ada koma, hanya satu nama petugas
                    $petugasName = trim($petugasValue);
                    // Gunakan awalan "petugas"
                    $prefiks = 'petugas';
                }

                // Format bulan
                $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;

                // Buat keterangan sesuai kondisi
                $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh {$prefiks} {$petugasName}";

                // Cari laporan berdasarkan keterangan
                $laporan = Laporan::where('keterangan', 'like', "%{$keterangan}%")->first();
                if ($laporan) {
                    // Update report amount
                    $laporan->uang_masuk = $laporan->uang_masuk - $oldJumlahRp + $billDetails['total'];

                    // Only update catatan_edit if the column exists
                    if (Schema::hasColumn('laporan', 'catatan_edit')) {
                        // Add edit history to report
                        $editHistory = json_decode($laporan->catatan_edit ?? '[]', true);
                        $editHistory[] = [
                            'tanggal_edit' => now()->format('Y-m-d H:i:s'),
                            'petugas_edit' => Auth::user()->nama,
                            'nilai_lama' => ['uang_masuk' => $laporan->getOriginal('uang_masuk')],
                            'nilai_baru' => ['uang_masuk' => $laporan->uang_masuk]
                        ];
                        $laporan->catatan_edit = json_encode($editHistory);
                    }

                    $laporan->save();
                }
            }

            // Commit transaction
            DB::commit();

            $redirectRoute = $wasAlreadyPaid ? 'lunas.index' : 'pemakaian.index';
            $message = 'Data pencatatan meter berhasil diperbarui' . ($wasAlreadyPaid ? ' beserta pembayarannya.' : '.');

            return redirect()->route($redirectRoute)->with('success', $message);

        } catch (\Exception $e) {
            // Roll back transaction on error
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Calculate bill amount based on water usage
     */
    private function calculateBill(float $pemakaian): array
    {
        // Get beban from database
        $beban = $this->getBeban();
        $bebanBiaya = $beban ? $beban['tarif'] : 0;
        $bebanId = $beban ? $beban['id'] : null;

        // If usage is 0, only charge the beban
        if ($pemakaian <= 0) {
            return [
                'total' => $bebanBiaya,
                'beban_id' => $bebanId,
                'kategori_id' => null,
                'detail' => [
                    'beban' => [
                        'id' => $bebanId,
                        'tarif' => $bebanBiaya
                    ],
                    'kategori' => []
                ]
            ];
        }

        // Get rate categories from database
        $kategori = $this->getKategoriBiaya();

        // If no categories found, return only beban charge
        if (empty($kategori)) {
            return [
                'total' => $bebanBiaya,
                'beban_id' => $bebanId,
                'kategori_id' => null,
                'detail' => [
                    'beban' => [
                        'id' => $bebanId,
                        'tarif' => $bebanBiaya
                    ],
                    'kategori' => []
                ]
            ];
        }

        $total = $bebanBiaya;
        $sisaPemakaian = $pemakaian;
        $lastUsedKategoriId = null;
        $kategoriSnapshot = [];
        $lastKategori = end($kategori);
        reset($kategori);

        foreach ($kategori as $index => $tarif) {
            $min = $tarif['min'];
            $max = $tarif['max'];
            $rate = $tarif['rate'];

            if ($sisaPemakaian <= 0 || $pemakaian < $min) {
                break;
            }

            $volume = 0;

            if ($index == 0) {
                $volume = min($max, $pemakaian);
            } else {
                $prevMax = $kategori[$index - 1]['max'];
                if ($pemakaian > $prevMax) {
                    // If this is the last category and usage exceeds upper limit
                    if ($tarif === $lastKategori && $pemakaian > $max) {
                        $volume = $pemakaian - $prevMax; // Calculate all remaining usage
                    } else {
                        $volume = min($pemakaian - $prevMax, $max - $prevMax);
                    }
                }
            }

            if ($volume > 0) {
                $subtotal = $volume * $rate;
                $total += $subtotal;
                $lastUsedKategoriId = $tarif['id'];

                // Save category details for snapshot
                $kategoriSnapshot[] = [
                    'id_kategori' => $tarif['id'],
                    'batas_bawah' => $min,
                    'batas_atas' => $max,
                    'tarif' => $rate,
                    'volume' => $volume,
                    'subtotal' => $subtotal
                ];
            }

            $sisaPemakaian -= $volume;
        }

        return [
            'total' => $total,
            'beban_id' => $bebanId,
            'kategori_id' => $lastUsedKategoriId,
            'detail' => [
                'beban' => [
                    'id' => $bebanId,
                    'tarif' => $bebanBiaya
                ],
                'kategori' => $kategoriSnapshot
            ]
        ];
    }

    /**
     * Get beban biaya from database
     */
    private function getBeban(): ?array
    {
        $bebanBiaya = \App\Models\BebanBiaya::first();
        return $bebanBiaya ? [
            'id' => $bebanBiaya->id_beban_biaya,
            'tarif' => (int) $bebanBiaya->tarif
        ] : null;
    }

    /**
     * Get kategori biaya from database
     */
    private function getKategoriBiaya(): array
    {
        return \App\Models\KategoriBiayaAir::orderBy('batas_bawah', 'asc')
            ->get()
            ->map(function ($kategori) {
                return [
                    'id' => $kategori->id_kategori_biaya,
                    'min' => (int) $kategori->batas_bawah,
                    'max' => (int) $kategori->batas_atas,
                    'rate' => (int) $kategori->tarif
                ];
            })
            ->toArray();
    }
}