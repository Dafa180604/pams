<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BebanBiaya;
use App\Models\KategoriBiayaAir;
use Illuminate\Http\Request;
use App\Models\Pemakaian;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;
use App\Models\Laporan;

class PemakaianController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default 10
        $search = $request->get('search');
        $userId = $request->get('id_users'); // tangkap id dari query jika ada

        // Ambil user yang login
        $authUser = Auth::user();

        // Pastikan akses_pelanggan adalah array (misal disimpan sebagai JSON string di DB)
        $aksesPelanggan = json_decode($authUser->akses_pelanggan, true);

        // Jika akses_pelanggan kosong/null, anggap sebagai tidak ada akses
        if (empty($aksesPelanggan) || !is_array($aksesPelanggan)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data pelanggan',
                'data' => []
            ], 403);
        }

        // Validasi jika ada pencarian berdasarkan id_users, pastikan id_users termasuk akses_pelanggan
        if ($userId && !in_array($userId, $aksesPelanggan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke pelanggan ini',
                'data' => []
            ], 403);
        }

        $query = Users::where('role', 'pelanggan')
            ->whereIn('id_users', $aksesPelanggan); // Batasi hanya pelanggan yang diizinkan

        // Jika ada pencarian berdasarkan nama/alamat/no_hp
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Jika ada pencarian berdasarkan id_users
        if ($userId) {
            $query->where('id_users', $userId);
        }

        $users = $query->paginate($perPage);

        // Mapping data user
        $mapped = $users->getCollection()->map(function ($user) {
            $penggunaanTerakhir = Pemakaian::where('id_users', $user->id_users)->latest()->first();
            $defaultValue = $user->jumlah_air ?? 0;

            return [
                'id_users' => $user->id_users,
                'nama' => $user->nama,
                'alamat' => $user->alamat,
                'rw' => $user->rw,
                'rt' => $user->rt,
                'no_hp' => $user->no_hp,
                'jumlah_air' => $defaultValue,
                'meter_akhir' => $penggunaanTerakhir ? $penggunaanTerakhir->meter_akhir : $defaultValue,
                'waktu_catat' => $penggunaanTerakhir ? $penggunaanTerakhir->waktu_catat : null,
            ];
        });

        // Set kembali collection hasil map
        $users->setCollection($mapped);

        return response()->json([
            'success' => true,
            'message' => 'Data pemakaian berhasil diambil',
            'data' => $users
        ]);
    }

    // Method untuk menyimpan data pemakaian
    public function store(Request $request)
    {
        // Validasi data input
        $validated = $request->validate([
            'id_users' => 'required|exists:users,id_users', 
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
            'foto_meteran' => 'nullable|image|mimes:jpg,jpeg,png|max:5048'
            // 'foto_meteran' => 'nullable|image', // Foto meteran opsional
        ], [
            'meter_awal.required' => 'Meter Awal Wajib Diisi!',
            'meter_awal.numeric' => 'Meter Awal Wajib di Isi Angka!',
            'meter_akhir.required' => 'Meter Akhir Wajib Diisi!',
            'meter_akhir.numeric' => 'Meter Akhir Wajib di Isi Angka!',
            'meter_akhir.gte' => 'Meter Akhir Tidak Boleh Lebih Kecil dari Meter Awal!',
        ]);

        // Membuat data pemakaian baru
        $pemakaian = new Pemakaian();
        $pemakaian->id_users = $request->id_users;  // Gantilah 'user_id' sesuai parameter
        $pemakaian->meter_awal = $request->meter_awal;
        $pemakaian->meter_akhir = $request->meter_akhir;
        $pemakaian->jumlah_pemakaian = $pemakaian->meter_akhir - $pemakaian->meter_awal;
        $pemakaian->waktu_catat = now();
        $pemakaian->petugas = Auth::user()->id_users;
        $pemakaian->foto_meteran = null;

        // Simpan pemakaian
        $pemakaian->save();

        //Upload foto ke Firebase jika ada
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
            try {
                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $fileName,
                    ]
                );
                \Log::info('Firebase Upload Success');
            } catch (\Exception $e) {
                \Log::error('Firebase Upload Failed: ' . $e->getMessage());
                throw $e;  // optionally throw lagi biar tetap error
            }


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

    // Method untuk form 
    public function bayar(Request $request)
    {
        // Validasi data input
        $request->validate([
            'id_users' => 'required|exists:users,id_users',
            'meter_awal' => 'required|numeric',
            'meter_akhir' => 'required|numeric|gte:meter_awal',
            'foto_meteran' => 'nullable|image|mimes:jpg,jpeg,png|max:5048'
            // 'foto_meteran' => 'nullable|image', // Foto meteran opsional
        ], [
            'meter_awal.required' => 'Meter Awal Wajib Diisi!',
            'meter_awal.numeric' => 'Meter Awal Wajib di Isi Angka!',
            'meter_akhir.required' => 'Meter Akhir Wajib Diisi!',
            'meter_akhir.numeric' => 'Meter Akhir Wajib di Isi Angka!',
            'meter_akhir.gte' => 'Meter Akhir Tidak Boleh Lebih Kecil dari Meter Awal!',
        ]);
    
        // Membuat data pemakaian baru
        $pemakaian = new Pemakaian();
        $pemakaian->id_users = $request->id_users;
        $pemakaian->meter_awal = $request->meter_awal;
        $pemakaian->meter_akhir = $request->meter_akhir;
        $pemakaian->jumlah_pemakaian = $pemakaian->meter_akhir - $pemakaian->meter_awal;
        $pemakaian->waktu_catat = now();
        $pemakaian->petugas = Auth::user()->id_users;
        
        // Simpan pemakaian terlebih dahulu untuk mendapatkan ID
        $pemakaian->save();
        
        //Upload foto ke Firebase jika ada
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
            try {
                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $fileName,
                    ]
                );
                \Log::info('Firebase Upload Success');
            } catch (\Exception $e) {
                \Log::error('Firebase Upload Failed: ' . $e->getMessage());
                throw $e;  // optionally throw lagi biar tetap error
            }


            // Buat file publik
            $object = $bucket->object($fileName);
            $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);

            // Dapatkan URL publik dan simpan di model Pemakaian
            $fotoUrl = 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;
            $pemakaian->foto_meteran = $fotoUrl;
            $pemakaian->save();
        }
    
        // Hitung tagihan berdasarkan pemakaian dari data pencatatan
        $billDetails = $this->calculateBill($pemakaian->jumlah_pemakaian);
    
        // Membuat data transaksi baru
        $transaksi = new Transaksi();
        $transaksi->id_pemakaian = $pemakaian->id_pemakaian;
        $transaksi->id_beban_biaya = $billDetails['beban_id'];
        $transaksi->id_kategori_biaya = $billDetails['kategori_id'];
        $transaksi->jumlah_rp = $billDetails['total'];
        $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Belum Bayar';
        $transaksi->detail_biaya = json_encode($billDetails['detail']);
        $transaksi->save();
    
        // Ambil data untuk response API
        $data = Transaksi::with(['pemakaian.users'])->find($transaksi->id_transaksi);
    
        // Return response dengan format yang diinginkan
        return response()->json([
            'success' => true,
            'message' => 'Data pemakaian dan transaksi berhasil dibuat',
            'data' => [
                'id_pelanggan' => $data->pemakaian->users->id_users,
                'id_transaksi' => $transaksi->id_transaksi,
                'meter_awal' => $data->pemakaian->meter_awal,
                'meter_akhir' => $data->pemakaian->meter_akhir,
                'jumlah_pemakaian' => $data->pemakaian->jumlah_pemakaian,
                'detail_biaya' => json_decode($data->detail_biaya),
                'total_tagihan' => $data->jumlah_rp,
            ]
        ],201);
    }
    
    // Methid untuk store pembayaran
    public function update(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required',
            'uang_bayar' => 'required|numeric'
        ]);
    
        try {
            $transaksi = Transaksi::findOrFail($request->id_transaksi);
    
            $kembalian = $request->uang_bayar - $transaksi->jumlah_rp;
    
            $transaksi->tgl_pembayaran = now();
            $transaksi->status_pembayaran = $request->status_pembayaran ?? 'Lunas';
            $transaksi->uang_bayar = $request->uang_bayar;
            $transaksi->kembalian = $kembalian;
            $transaksi->save();
    
            if ($transaksi->status_pembayaran == 'Lunas') {
                $bulan = date('F');
                $tahun = date('Y');
    
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
    
                $pemakaian = $transaksi->pemakaian; 
                
                // Get the petugas value - you might need to adjust this based on your exact relationship
                $petugas = $pemakaian ? $pemakaian->petugas : 'Unknown';
        
                $bulanTeks = $bulanIndonesia[$bulan] ?? $bulan;
                $keterangan = "Terima bayar {$bulanTeks} {$tahun} oleh petugas {$petugas}";
     
                $existingLaporan = Laporan::where('keterangan', $keterangan)->first();
    
                if ($existingLaporan) {
                    $existingLaporan->uang_masuk += $transaksi->jumlah_rp;
                    $existingLaporan->save();
                } else { 
                    $laporan = new Laporan();
                    $laporan->tanggal = now(); // Tetap menggunakan tanggal hari ini untuk field tanggal
                    $laporan->uang_masuk = $transaksi->jumlah_rp;
                    $laporan->keterangan = $keterangan;
                    $laporan->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses.',
                'data' => [
                    'id_transaksi' => $transaksi->id_transaksi,
                    'nama_petugas' => Auth::user()->nama ?? 'Unknown',
                    'nama_pelanggan' => $transaksi->pemakaian->users->nama ?? null,
                    'alamat_pelanggan' => $transaksi->pemakaian && $transaksi->pemakaian->users
                    ? trim("{$transaksi->pemakaian->users->alamat}, RT {$transaksi->pemakaian->users->rt} RW {$transaksi->pemakaian->users->rw}")
                    : '-',
                    'tanggal_pencatatan' => $transaksi->pemakaian->waktu_catat ?? null,
                    'tanggal_pembayaran' => $transaksi->tgl_pembayaran ? $transaksi->tgl_pembayaran->format('Y-m-d H:i:s') : null,
            
                    'meter_awal' => $transaksi->pemakaian->meter_awal ?? null,
                    'meter_akhir' => $transaksi->pemakaian->meter_akhir ?? null,
                    'jumlah_pemakaian' => $transaksi->pemakaian->jumlah_pemakaian ?? null,
            
                    'denda' => $transaksi->rp_denda ?? 0,
                    'detail_biaya' => json_decode($transaksi->detail_biaya, true),
                    'total_tagihan' => $transaksi->jumlah_rp,
                    'jumlah_bayar' => $transaksi->uang_bayar,
                    'kembalian' => $transaksi->kembalian
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
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
