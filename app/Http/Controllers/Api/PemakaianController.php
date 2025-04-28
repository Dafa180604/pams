<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PemakaianController extends Controller
{
    
    // Method untuk menyimpan data pemakaian
    public function store(Request $request)
    {
        // Validasi data input
        $validated = $request->validate([
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
            'foto_meteran' => 'nullable|image', // Foto meteran opsional
        ], [
            'meter_awal.required' => 'Meter Awal Wajib Diisi!',
            'meter_awal.numeric' => 'Meter Awal Wajib di Isi Angka!',
            'meter_akhir.required' => 'Meter Akhir Wajib Diisi!',
            'meter_akhir.numeric' => 'Meter Akhir Wajib di Isi Angka!',
            'meter_akhir.gte' => 'Meter Akhir Tidak Boleh Lebih Kecil dari Meter Awal!',
        ]);

        // Membuat data pemakaian baru
        $pemakaian = new Pemakaian();
        $pemakaian->id_users = $request->user_id;  // Gantilah 'user_id' sesuai parameter
        $pemakaian->meter_awal = $request->meter_awal;
        $pemakaian->meter_akhir = $request->meter_akhir;
        $pemakaian->jumlah_pemakaian = $pemakaian->meter_akhir - $pemakaian->meter_awal;
        $pemakaian->waktu_catat = now();
        $pemakaian->petugas = Auth::user()->nama;
        $pemakaian->foto_meteran = null;

        // Simpan pemakaian
        $pemakaian->save();

        // Upload foto ke Firebase jika ada
        if ($request->hasFile('foto_meteran')) {
            $file = $request->file('foto_meteran');
            $fileName = 'foto_meteran/' . $pemakaian->id_pemakaian . '/' . time() . '_' . $file->getClientOriginalName();

            // Inisialisasi Google Cloud Storage
            $storage = new StorageClient([
                'keyFilePath' => base_path('app/firebase/dafaq-542a5-firebase-adminsdk-nezyi-2e2d42888b.json'),
            ]);

            $bucketName = env('FIREBASE_STORAGE_BUCKET', 'dafaq-542a5.appspot.com');
            $bucket = $storage->bucket($bucketName);

            // Upload file ke Firebase Storage
            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                [
                    'name' => $fileName,
                ]
            );

            // Buat file publik
            $object = $bucket->object($fileName);
            $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);

            // Dapatkan URL publik dan simpan di model Pemakaian
            $fotoUrl = 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;
            $pemakaian->foto_meteran = $fotoUrl;
            $pemakaian->save();
        }

        // Hitung tagihan berdasarkan pemakaian
        $billDetails = $this->calculateBill($pemakaian->jumlah_pemakaian);

        // Membuat data transaksi baru
        $transaksi = new Transaksi();
        $transaksi->id_pemakaian = $pemakaian->id_pemakaian;
        $transaksi->id_beban_biaya = $billDetails['beban_id'];
        $transaksi->id_kategori_biaya = $billDetails['kategori_id'];
        $transaksi->tgl_pembayaran = null;
        $transaksi->jumlah_rp = $billDetails['total'];
        $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Belum Bayar';
        $transaksi->detail_biaya = json_encode($billDetails['detail']);
        $transaksi->save();

        return response()->json([
            'message' => 'Data Pemakaian berhasil ditambahkan.',
            'data' => $pemakaian
        ], 201); // Response berhasil ditambahkan dengan status 201
    }

    // Method untuk form pembayaran
    public function bayar(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
            'foto_meteran' => 'nullable|image', // Foto meteran opsional
        ]);

        // Temukan pemakaian berdasarkan ID
        $pemakaian = Pemakaian::find($id);
        if (!$pemakaian) {
            return response()->json(['message' => 'Pemakaian tidak ditemukan'], 404);
        }

        // Update pemakaian data
        $pemakaian->meter_awal = $request->meter_awal;
        $pemakaian->meter_akhir = $request->meter_akhir;
        $pemakaian->jumlah_pemakaian = $pemakaian->meter_akhir - $pemakaian->meter_awal;
        $pemakaian->waktu_catat = now();
        $pemakaian->save();

        // Hitung tagihan berdasarkan pemakaian
        $billDetails = $this->calculateBill($pemakaian->jumlah_pemakaian);

        // Membuat data transaksi baru
        $transaksi = new Transaksi();
        $transaksi->id_pemakaian = $pemakaian->id_pemakaian;
        $transaksi->id_beban_biaya = $billDetails['beban_id'];
        $transaksi->id_kategori_biaya = $billDetails['kategori_id'];
        $transaksi->tgl_pembayaran = now();
        $transaksi->jumlah_rp = $billDetails['total'];
        $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Belum Bayar';
        $transaksi->detail_biaya = json_encode($billDetails['detail']);
        $transaksi->save();

        return response()->json([
            'message' => 'Pembayaran berhasil diproses.',
            'data' => $transaksi
        ], 200); // Response berhasil dengan status 200
    }
    
    // Methid untuk store pembayaran
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
    
            // Calculate kembalian (change)
            $kembalian = $request->uang_bayar - $transaksi->jumlah_rp;
    
            // Update the transaction attributes
            $transaksi->tgl_pembayaran = now();
            $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Lunas';
            $transaksi->uang_bayar = $request->uang_bayar;
            $transaksi->kembalian = $kembalian;
    
            // Save the updated transaction
            $transaksi->save();
    
            // If the payment status is 'Lunas', add to the report table
            if ($transaksi->status_pembayaran == 'Lunas') {
                // Get the current month and year
                $bulan = date('F'); // English month name
                $tahun = date('Y');
    
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
    
                // Get petugas from the related Pemakaian record
                $pemakaian = $transaksi->pemakaian;
                $petugas = $pemakaian ? $pemakaian->petugas : 'Unknown';
    
                // Prepare report text in Indonesian
                $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;
                $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh petugas {$petugas}";
    
                // Check if a report for the same month and year already exists
                $existingLaporan = Laporan::where('keterangan', $keterangan)->first();
    
                if ($existingLaporan) {
                    // If report exists, update the amount
                    $existingLaporan->uang_masuk += $transaksi->jumlah_rp;
                    $existingLaporan->save();
                } else {
                    // If no report exists, create a new one
                    $laporan = new Laporan();
                    $laporan->tanggal = now();
                    $laporan->uang_masuk = $transaksi->jumlah_rp;
                    $laporan->keterangan = $keterangan;
                    $laporan->save();
                }
            }
    
            // Return a response with success and receipt URL
            $cetakUrl = route('lunas.cetak', ['id_transaksi' => $id_transaksi]);
    
            return response()->json([
                'message' => 'Pembayaran berhasil diproses.',
                'cetakUrl' => $cetakUrl
            ], 200);
    
        } catch (\Exception $e) {
            // Return an error response if there's an exception
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    
    //Perhitungan
    private static function getBeban(): ?array
    {
        $bebanBiaya = BebanBiaya::first();
        return $bebanBiaya ? [
            'id' => $bebanBiaya->id_beban_biaya,
            'tarif' => (int) $bebanBiaya->tarif
        ] : null;
    }

    private static function getKategoriBiaya(): array
    {
        return KategoriBiayaAir::orderBy('batas_bawah', 'asc')
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
    
    private static function calculateBill(float $pemakaian): array
    {
        // Ambil nilai beban dari database
        $beban = self::getBeban();
        $bebanBiaya = $beban ? $beban['tarif'] : 0;
        $bebanId = $beban ? $beban['id'] : null;
    
        // Jika pemakaian 0, hanya mengenakan biaya beban
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
    
        // Ambil kategori biaya dari database
        $kategori = self::getKategoriBiaya();
    
        // Jika tidak ada kategori biaya, return hanya biaya beban
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
                    // Jika ini kategori terakhir dan pemakaian melebihi batas_atas
                    if ($tarif === $lastKategori && $pemakaian > $max) {
                        $volume = $pemakaian - $prevMax; // Hitung semua sisa pemakaian
                    } else {
                        $volume = min($pemakaian - $prevMax, $max - $prevMax);
                    }
                }
            }
    
            if ($volume > 0) {
                $subtotal = $volume * $rate;
                $total += $subtotal;
                $lastUsedKategoriId = $tarif['id'];
                
                // Simpan detail kategori untuk snapshot
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
    
    public function getMeterAkhir($id_users)
    {
        // Ambil data penggunaan terakhir berdasarkan id_users
        $penggunaanTerakhir = Pemakaian::where('id_users', $id_users)->latest()->first();

        // Ambil nilai jumlah_air dari tabel users
        $user = Users::find($id_users);
        $defaultValue = $user ? $user->jumlah_air : 0;

        // Kembalikan nilai Meter Akhir jika data ada, jika tidak kembalikan nilai jumlah_air dari user
        return response()->json([
            'meter_akhir' => $penggunaanTerakhir ? $penggunaanTerakhir->meter_akhir : $defaultValue
        ]);
    }
    
}
