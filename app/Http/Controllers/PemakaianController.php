<?php

namespace App\Http\Controllers;
use App\Models\Laporan;
use Auth;
use Google\Cloud\Storage\StorageClient;
use App\Models\BebanBiaya;
use App\Models\KategoriBiayaAir;
use App\Models\Pemakaian;
use App\Models\Transaksi;
use App\Models\Users;
use Illuminate\Http\Request;

class PemakaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataPelanggan = Users::where('role', 'pelanggan')->get()->map(function ($user) {
            // Ambil data penggunaan terakhir berdasarkan id_users
            $penggunaanTerakhir = Pemakaian::where('id_users', $user->id_users)->latest()->first();
    
            // Ambil nilai jumlah_air dari tabel users
            $defaultValue = $user->jumlah_air ?? 0;
    
            // Tambahkan atribut meter_akhir ke objek user
            $user->meter_akhir = $penggunaanTerakhir ? $penggunaanTerakhir->meter_akhir : $defaultValue;
    
            return $user;
        });
    
        return view('pemakaian.index', ['dataPelanggan' => $dataPelanggan]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
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
    
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
            // 'foto' => 'required|image',  // Uncomment jika foto wajib
        ], [
            'meter_awal.required' => 'Meter Awal Wajib Diisi!',
            'meter_awal.numeric' => 'Meter Awal Wajib di Isi Angka!',
            'meter_akhir.required' => 'Meter Akhir Wajib Diisi!',
            'meter_akhir.numeric' => 'Meter Akhir Wajib di Isi Angka!',
            'meter_akhir.gte' => 'Meter Akhir Tidak Boleh Lebih Kecil dari Meter Awal!',
            // 'foto.required' => 'Foto Wajib di isi!',
            // 'foto.image' => 'File harus berupa gambar!',
        ]);

        // Membuat data pemakaian baru
        $pemakaian = new Pemakaian();
        $pemakaian->id_users = $request->users;
        $pemakaian->meter_awal = $request->meter_awal;
        $pemakaian->meter_akhir = $request->meter_akhir;
        $pemakaian->jumlah_pemakaian = $pemakaian->meter_akhir - $pemakaian->meter_awal;
        $pemakaian->waktu_catat = now();
        $pemakaian->petugas = Auth::user()->id_users;
        $pemakaian->foto_meteran = null; // or some default URL
        
        // Simpan pemakaian terlebih dahulu untuk mendapatkan ID
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

            // Buat file publik (pastikan bisa diakses)
            $object = $bucket->object($fileName);
            $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);

            // Dapatkan URL publik
            $fotoUrl = 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;
            $pemakaian->foto_meteran = $fotoUrl;
            // Dapatkan URL publik dan simpan di model Pemakaian
            $pemakaian->save();
        }

        // Hitung tagihan berdasarkan pemakaian dari data pencatatan
        $billDetails = $this->calculateBill($pemakaian->jumlah_pemakaian);

        // Membuat data transaksi baru
        $transaksi = new Transaksi();
        $transaksi->id_pemakaian = $pemakaian->id_pemakaian;
        $transaksi->id_beban_biaya = $billDetails['beban_id'];
        $transaksi->id_kategori_biaya = $billDetails['kategori_id'];
        $transaksi->tgl_pembayaran = null;
        $transaksi->jumlah_rp = $billDetails['total'];
        $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Belum Bayar';
        
        // Simpan detail perhitungan sebagai JSON
        $transaksi->detail_biaya = json_encode($billDetails['detail']);
        
        $transaksi->save();

        return redirect()->route('pemakaian.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function bayar(Request $request)
    {
        // Validasi data input
        $request->validate([
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
            // 'foto_meteran' => 'required|image',
        ], [
            'meter_awal.required' => 'Meter Awal Wajib Diisi!',
            'meter_awal.numeric' => 'Meter Awal Wajib di Isi Angka!',
            'meter_akhir.required' => 'Meter Akhir Wajib Diisi!',
            'meter_akhir.numeric' => 'Meter Akhir Wajib di Isi Angka!',
            'meter_akhir.gte' => 'Meter Akhir Tidak Boleh Lebih Kecil dari Meter Awal!',
            // 'foto_meteran.required' => 'Foto Meteran Wajib di isi!',
            // 'foto_meteran.image' => 'File harus berupa gambar!',
        ]);

        // Membuat data pemakaian baru
        $pemakaian = new Pemakaian();
        $pemakaian->id_users = $request->users;
        $pemakaian->meter_awal = $request->meter_awal;
        $pemakaian->meter_akhir = $request->meter_akhir;
        $pemakaian->jumlah_pemakaian = $pemakaian->meter_akhir - $pemakaian->meter_awal;
        $pemakaian->waktu_catat = now();
        $pemakaian->petugas = Auth::user()->id_users;
        $pemakaian->foto_meteran = null; // or some default URL
        
        // Simpan pemakaian terlebih dahulu untuk mendapatkan ID
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

            // Buat file publik (pastikan bisa diakses)
            $object = $bucket->object($fileName);
            $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);

            // Dapatkan URL publik
            $fotoUrl = 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;
            $pemakaian->foto_meteran = $fotoUrl;
            // Dapatkan URL publik dan simpan di model Pemakaian
            $pemakaian->save();
        }

        // Hitung tagihan berdasarkan pemakaian dari data pencatatan
        $billDetails = $this->calculateBill($pemakaian->jumlah_pemakaian);

        // Membuat data transaksi baru
        $transaksi = new Transaksi();
        $transaksi->id_pemakaian = $pemakaian->id_pemakaian;
        $transaksi->id_beban_biaya = $billDetails['beban_id'];
        $transaksi->id_kategori_biaya = $billDetails['kategori_id'];
        $transaksi->tgl_pembayaran = null;
        $transaksi->jumlah_rp = $billDetails['total'];
        $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Belum Bayar';
        
        // Simpan detail perhitungan sebagai JSON
        $transaksi->detail_biaya = json_encode($billDetails['detail']);
        
        $transaksi->save();

        // Selalu arahkan ke halaman pembayaran untuk metode bayar
        // Tidak perlu mengecek action karena route ini khusus untuk bayar

        // Ambil data untuk view pembayaran
        // Load transaksi dengan relasi yang dibutuhkan
        $data = Transaksi::with(['pemakaian.users'])->find($transaksi->id_transaksi);

        // Redirect ke halaman pembayaran
        return view('pemakaian.bayar', compact('data'));
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id_users)
    {
        $data = Users::find($id_users);
        return view('pemakaian.edit', compact('data'));
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
    
            // Save the changes to the existing record
            $transaksi->save();
    
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
                // Assuming there's a relationship between Transaksi and Pemakaian
                $pemakaian = $transaksi->pemakaian; // Or however you access the related record
    
                // Get the petugas value - you might need to adjust this based on your exact relationship
                $petugas = $pemakaian ? $pemakaian->petugas : 'Unknown';
    
                $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;
                $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh petugas {$petugas}";
    
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
