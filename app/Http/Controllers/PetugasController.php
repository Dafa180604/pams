<?php

namespace App\Http\Controllers;

use App\Models\Pemakaian;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Google\Cloud\Storage\StorageClient;
class PetugasController extends Controller
{
    public function index()
    {
        $datapetugas = Users::where('role', 'petugas')->get();
        return view('petugas.index', ['datapetugas' => $datapetugas]);
    }

    public function show($id, Request $request)
    {
        // Cari data petugas termasuk yang sudah soft delete
        $data = Users::withTrashed()->findOrFail($id);

        // Query pemakaian berdasarkan ID petugas
        $pemakaianQuery = Pemakaian::where('petugas', $data->id_users);

        // Filter berdasarkan bulan jika ada
        if ($request->has('end_date')) {
            $selectedMonth = $request->input('end_date');
            $startDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();
            $pemakaianQuery->whereBetween('waktu_catat', [$startDate, $endDate]);
        } else {
            // Default to current month if no date is selected
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $pemakaianQuery->whereBetween('waktu_catat', [$startDate, $endDate]);
        }

        // Calculate total transactions amount for the filtered period
        // Assuming there's a relationship between Pemakaian and Transaksi or a price field
        // Adjust this calculation based on your actual database structure
        $totalTransaksi = 0;

        // Option 1: If you have a direct price/amount field in Pemakaian table
        // $totalTransaksi = $pemakaianQuery->sum('amount');

        // Option 2: If transactions are in a separate related table (more common scenario)
        // Get pemakaian IDs for the filtered period
        $pemakaianIds = $pemakaianQuery->pluck('id_pemakaian')->toArray();

        // Calculate total from Transaksi table based on these pemakaian IDs
        if (!empty($pemakaianIds)) {
            $totalTransaksi = Transaksi::whereIn('id_pemakaian', $pemakaianIds)->sum('jumlah_rp');
        }

        // Format total transaction amount
        $formattedTotal = 'Rp ' . number_format($totalTransaksi, 0, ',', '.');

        // Ambil data pemakaian dan user (termasuk soft deleted)
        $pencatatan = $pemakaianQuery->with([
            'users' => function ($query) {
                $query->withTrashed();
            }
        ])
            ->orderBy('waktu_catat', 'desc')
            ->paginate(10);

        return view('petugas.detail', [
            'data' => $data,
            'pencatatan' => $pencatatan,
            'totalTransaksi' => $formattedTotal
        ]);
    }





    public function create()
    {
        return view('petugas.create');
    }

    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'rt' => 'required|numeric',
            'rw' => 'required|numeric',
            'no_hp' => 'required|numeric|unique:users',
            'username' => 'required|unique:users',
            // 'golongan' => 'required',
            // 'jumlah_air' => 'required|numeric',
            'foto_profile' => 'required|image|mimes:jpeg,png,jpg', // Validasi untuk file gambar
        ], [
            'nama.required' => 'Nama Wajib Diisi!',
            'alamat.required' => 'Alamat Wajib Diisi!',
            'rt.required' => 'RT Wajib Diisi!',
            'rt.numeric' => 'RT Wajib Angka!',
            'rw.required' => 'RW Wajib Diisi!',
            'rw.numeric' => 'RW Wajib Angka!',
            'no_hp.required' => 'Nomor HP Wajib Diisi!',
            'no_hp.numeric' => 'Nomor HP Harus Berupa Angka!',
            'no_hp.digits_between' => 'Nomor HP Harus Berjumlah 10 hingga 13 Digit!',
            'no_hp.unique' => 'Nomor HP sudah digunakan, silakan pilih yang lain.',
            'username.required' => 'Username Wajib Diisi!',
            // 'golongan.required' => 'Golongan Wajib Diisi!',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            // 'jumlah_air.required' => 'Jumlah Air Wajib Diisi!',
            // 'jumlah_air' => 'Jumlah Air Harus Berupa Angka!',
            'foto_profile.required' => 'Foto Profil Wajib Diisi!',
            'foto_profile.image' => 'File harus berupa gambar!',
            'foto_profile.mimes' => 'Format gambar harus jpeg, png, atau jpg!',
        ]);

        // Buat Password
        $defaultPassword = 'PetugasPams';
        $passwordToStore = $request->password ? Hash::make($request->password) : Hash::make($defaultPassword);

        // Logika untuk menentukan id_users yang kosong
        $idUsers = 1;
        $existingIds = Users::withTrashed()->pluck('id_users')->sort()->values();
        foreach ($existingIds as $existingId) {
            if ($idUsers != $existingId) {
                break;
            }
            $idUsers++;
        }

        try {
            // Membuat data Users baru di database lokal
            $data = new Users();
            $data->id_users = $idUsers;
            $data->nama = $request->nama;
            $data->alamat = $request->alamat;
            $data->rw = $request->rw;
            $data->rt = $request->rt;
            $data->no_hp = $request->no_hp;
            $data->username = $request->username;
            $data->password = $passwordToStore;
            $data->role = 'petugas';
            // $data->golongan = $request->golongan;
            // $data->jumlah_air = $request->jumlah_air;
            $data->foto_profile = null; // Default ke null
            $data->save();

            // Upload foto ke Firebase jika ada
            if ($request->hasFile('foto_profile')) {
                $file = $request->file('foto_profile');
                $fileName = 'profile_photos/' . $data->id_users . '/' . time() . '_' . $file->getClientOriginalName();

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

                // Update data user dengan URL foto
                $data->foto_profile = $fotoUrl;
                $data->save();
            }

            return redirect('/petugas')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Firebase upload error: ' . $e->getMessage());

            // Tampilkan pesan error yang lebih spesifik
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(string $id_users)
    {
        $data = Users::find($id_users);
        return view('petugas.edit', compact('data'));
    }

    public function update(Request $request, string $id_users)
    {
        // Validasi data input
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'rt' => 'required|numeric',
            'rw' => 'required|numeric',
            'no_hp' => 'required|numeric|unique:users,no_hp,' . $id_users . ',id_users',
            'username' => 'required|unique:users,username,' . $id_users . ',id_users',
            // 'golongan' => 'required',
            // 'jumlah_air' => 'required|numeric',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg',
        ], [
            'nama.required' => 'Nama Wajib Diisi!',
            'alamat.required' => 'Alamat Wajib Diisi!',
            'rt.required' => 'RT Wajib Diisi!',
            'rt.numeric' => 'RT Wajib Angka!',
            'rw.required' => 'RW Wajib Diisi!',
            'rw.numeric' => 'RW Wajib Angka!',
            'no_hp.required' => 'Nomor HP Wajib Diisi!',
            'no_hp.numeric' => 'Nomor HP Harus Berupa Angka!',
            'no_hp.digits_between' => 'Nomor no_hp Harus Berjumlah 10 hingga 13 Digit!',
            'no_hp.unique' => 'Nomor HP sudah digunakan, silakan pilih yang lain.',
            'username.required' => 'Username Wajib Diisi!',
            // 'golongan.required' => 'Golongan Wajib Diisi!',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            // 'jumlah_air.required' => 'Jumlah Air Wajib Diisi!',
            // 'jumlah_air' => 'Jumlah Air Harus Berupa Angka!',
            'foto_profile.image' => 'File harus berupa gambar!',
            'foto_profile.mimes' => 'Format gambar harus jpeg, png, atau jpg!',
        ]);

        try {
            // Ambil data user yang akan diupdate
            $data = Users::findOrFail($id_users);

            // Update data dasar di database lokal
            $data->nama = $request->nama;
            $data->alamat = $request->alamat;
            $data->rw = $request->rw;
            $data->rt = $request->rt;
            $data->no_hp = $request->no_hp;
            $data->username = $request->username;
            // $data->golongan = $request->golongan;
            // $data->jumlah_air = $request->jumlah_air;

            // Update password jika disediakan
            if (!empty($request->password)) {
                $data->password = Hash::make($request->password);
            }

            // Upload foto ke Firebase jika ada
            if ($request->hasFile('foto_profile')) {
                $file = $request->file('foto_profile');
                $fileName = 'profile_photos/' . $data->id_users . '/' . time() . '_' . $file->getClientOriginalName();

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

                // Format URL persis seperti yang diharapkan
                $fotoUrl = 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;

                // Update foto URL di database
                $data->foto_profile = $fotoUrl;
            }

            // Simpan perubahan ke database MySQL
            $data->save();

            return redirect('/petugas')->with('update', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Update error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id_users)
    {
        try {
            // Cari data user
            $data = Users::findOrFail($id_users);

            // Hapus foto dari Firebase jika ada
            if ($data->foto_profile) {
                $storage = new StorageClient([
                    'keyFilePath' => base_path('app/firebase/dafaq-542a5-firebase-adminsdk-nezyi-2e2d42888b.json'),
                ]);

                $bucketName = env('FIREBASE_STORAGE_BUCKET', 'dafaq-542a5.appspot.com');
                $bucket = $storage->bucket($bucketName);

                // Ekstrak path file dari URL
                $urlParts = parse_url($data->foto_profile);
                $filePath = ltrim($urlParts['path'], '/');
                $filePath = str_replace($bucketName . '/', '', $filePath);

                // Hapus file dari Firebase
                if ($bucket->object($filePath)->exists()) {
                    $bucket->object($filePath)->delete();
                }
            }

            // Hapus data dari database lokal
            $data->delete();

            return redirect('/petugas')->with('delete', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Delete error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function pilih(Request $request, $id_users)
    {
        // Cari petugas berdasarkan ID
        $petugas = Users::findOrFail($id_users);

        // Ambil filter dari request
        $filterAlamat = $request->input('alamat');
        $filterRW = $request->input('rw');
        $filterRT = $request->input('rt');

        // Query pelanggan dengan filter, hanya yang memiliki role 'pelanggan'
        $dataPelangganRaw = Users::where('role', 'pelanggan')
            ->when($filterAlamat, function ($query, $filterAlamat) {
                $query->where('alamat', 'like', "%{$filterAlamat}%");
            })
            ->when($filterRW, function ($query, $filterRW) {
                $query->where('rw', $filterRW);
            })
            ->when($filterRT, function ($query, $filterRT) {
                $query->where('rt', $filterRT);
            })
            ->orderBy('alamat', 'asc')
            ->orderBy('rw', 'asc')
            ->orderBy('rt', 'asc')
            ->get();

        // Kelompokkan data berdasarkan alamat, RW, dan RT
        $dataPelangganGrouped = $dataPelangganRaw->groupBy(function ($item) {
            return $item->alamat . '|' . $item->rw . '|' . $item->rt;
        });

        // Buat struktur data baru untuk area/tempat
        $dataArea = [];
        foreach ($dataPelangganGrouped as $key => $group) {
            $parts = explode('|', $key);
            $alamat = $parts[0];
            $rw = $parts[1];
            $rt = $parts[2];

            // Ambil semua ID pelanggan dalam group ini
            $pelangganIds = $group->pluck('id_users')->toArray();
            $jumlahPelanggan = $group->count();

            $dataArea[] = [
                'alamat' => $alamat,
                'rw' => $rw,
                'rt' => $rt,
                'pelanggan_ids' => $pelangganIds,
                'jumlah_pelanggan' => $jumlahPelanggan,
                'area_key' => $key
            ];
        }

        // Ambil daftar ID pelanggan yang sudah diassign ke petugas ini
        $aksesPelanggan = [];
        if (!empty($petugas->akses_pelanggan)) {
            $decoded = json_decode($petugas->akses_pelanggan, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $aksesPelanggan = $decoded;
            }
        }

        // Ambil semua petugas beserta akses pelanggan mereka
        $semuaPetugas = Users::where('role', 'petugas')
            ->whereNotNull('akses_pelanggan')
            ->where('akses_pelanggan', '!=', '')
            ->where('akses_pelanggan', '!=', 'null')
            ->get();

        // Buat mapping petugas yang memiliki akses ke setiap area
        $petugasAksesArea = [];

        foreach ($dataArea as $area) {
            $petugasAksesArea[$area['area_key']] = [];
            $petugasYangSudahDitambahkan = [];

            // Cek setiap petugas apakah memiliki akses ke area ini
            foreach ($semuaPetugas as $ptgs) {
                $aksesPetugasIni = [];

                // Decode akses pelanggan petugas ini
                if (!empty($ptgs->akses_pelanggan)) {
                    $decoded = json_decode($ptgs->akses_pelanggan, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $aksesPetugasIni = array_map('intval', $decoded); // Convert ke integer
                    }
                }

                // Jika petugas ini memiliki akses
                if (!empty($aksesPetugasIni)) {
                    // Cek apakah ada pelanggan di area ini yang ada dalam akses petugas ini
                    $adaAkses = false;
                    foreach ($area['pelanggan_ids'] as $pelangganId) {
                        if (in_array((int) $pelangganId, $aksesPetugasIni)) {
                            $adaAkses = true;
                            break;
                        }
                    }

                    // Jika ada akses dan petugas belum ditambahkan
                    if ($adaAkses && !in_array($ptgs->id_users, $petugasYangSudahDitambahkan)) {
                        $petugasAksesArea[$area['area_key']][] = [
                            'id' => $ptgs->id_users,
                            'nama' => $ptgs->nama
                        ];
                        $petugasYangSudahDitambahkan[] = $ptgs->id_users;
                    }
                }
            }
        }

        // Tentukan area mana yang sudah dipilih oleh petugas saat ini
        $areaSelected = [];
        foreach ($dataArea as $area) {
            // Cek apakah semua pelanggan di area ini sudah diassign ke petugas saat ini
            $allAssigned = true;
            foreach ($area['pelanggan_ids'] as $pelangganId) {
                if (!in_array((int) $pelangganId, $aksesPelanggan)) {
                    $allAssigned = false;
                    break;
                }
            }
            $areaSelected[$area['area_key']] = $allAssigned && count($area['pelanggan_ids']) > 0;
        }

        // Ambil data untuk dropdown filter hanya dari pelanggan
        $alamatList = Users::where('role', 'pelanggan')->select('alamat')->distinct()->pluck('alamat');
        $rwList = Users::where('role', 'pelanggan')->select('rw')->distinct()->pluck('rw');
        $rtList = Users::where('role', 'pelanggan')->select('rt')->distinct()->pluck('rt');

        // Debug untuk melihat data petugas akses area
        // \Log::info('Petugas Akses Area Debug:', $petugasAksesArea);

        return view('petugas.pilih_pelanggan', [
            'dataArea' => $dataArea,
            'aksesPelanggan' => $aksesPelanggan,
            'petugasAksesArea' => $petugasAksesArea,
            'areaSelected' => $areaSelected,
            'petugas' => $petugas,
            'alamatList' => $alamatList,
            'rwList' => $rwList,
            'rtList' => $rtList,
            'filterAlamat' => $filterAlamat,
            'filterRW' => $filterRW,
            'filterRT' => $filterRT,
        ]);
    }
    public function updateAksesPelanggan(Request $request, $id_users)
{
    try {
        $petugas = Users::findOrFail($id_users);
        $newPelangganIds = $request->input('pelanggan_ids', []);
        
        // Pastikan pelanggan_ids adalah array dan filter nilai kosong
        if (!is_array($newPelangganIds)) {
            $newPelangganIds = [];
        }
        
        // Filter dan convert ke integer untuk memastikan data valid
        $newPelangganIds = array_filter(array_map('intval', $newPelangganIds), function($id) {
            return $id > 0;
        });
        
        // Re-index array untuk menghindari gap
        $newPelangganIds = array_values($newPelangganIds);
        
        // Validasi apakah semua ID pelanggan yang dipilih benar-benar ada dan berole pelanggan
        $validPelangganIds = Users::where('role', 'pelanggan')
            ->whereIn('id_users', $newPelangganIds)
            ->pluck('id_users')
            ->toArray();
        
        // Gunakan hanya ID yang valid
        $newPelangganIds = array_intersect($newPelangganIds, $validPelangganIds);
        
        // Find all staff members who have access to the selected customers
        $conflictingAssignments = [];
        $conflictingAreas = []; // Array untuk menyimpan area yang konflik

        // Get all petugas users (excluding current petugas)
        $allPetugas = Users::where('role', 'petugas')
            ->where('id_users', '!=', $id_users)
            ->whereNotNull('akses_pelanggan')
            ->where('akses_pelanggan', '!=', '')
            ->get();

        // Get pelanggan data untuk mendapatkan alamat, RT, RW
        $pelangganData = Users::where('role', 'pelanggan')
            ->whereIn('id_users', $newPelangganIds)
            ->get()
            ->keyBy('id_users');

        // Check each selected customer for existing assignments to other staff
        foreach ($newPelangganIds as $pelangganId) {
            foreach ($allPetugas as $otherPetugas) {
                $otherAccessList = [];

                // Decode the other staff's access list
                if (!empty($otherPetugas->akses_pelanggan)) {
                    $decoded = json_decode($otherPetugas->akses_pelanggan, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $otherAccessList = array_map('intval', $decoded);
                    }
                }

                // Check if this pelanggan is in the other staff's access list
                if (in_array($pelangganId, $otherAccessList)) {
                    // Get the customer data
                    $pelanggan = $pelangganData->get($pelangganId);
                    
                    if ($pelanggan) {
                        // Buat area key untuk grouping
                        $areaKey = $pelanggan->alamat . '|' . $pelanggan->rw . '|' . $pelanggan->rt;
                        $areaDisplay = $pelanggan->alamat . ' RT ' . $pelanggan->rt . ' RW ' . $pelanggan->rw;
                        
                        // Check if this area conflict is already recorded for this petugas
                        $conflictKey = $areaKey . '|' . $otherPetugas->id_users;
                        
                        if (!isset($conflictingAreas[$conflictKey])) {
                            $conflictingAreas[$conflictKey] = [
                                'area_display' => $areaDisplay,
                                'area_key' => $areaKey,
                                'petugas_id' => $otherPetugas->id_users,
                                'petugas_name' => $otherPetugas->nama,
                                'pelanggan_count' => 0,
                                'pelanggan_ids' => []
                            ];
                        }
                        
                        // Tambahkan pelanggan ke area conflict ini
                        $conflictingAreas[$conflictKey]['pelanggan_count']++;
                        $conflictingAreas[$conflictKey]['pelanggan_ids'][] = $pelangganId;
                    }
                }
            }
        }

        // Convert conflicting areas to the format expected by the view
        foreach ($conflictingAreas as $conflict) {
            $conflictingAssignments[] = [
                'area_display' => $conflict['area_display'],
                'area_key' => $conflict['area_key'],
                'petugas_id' => $conflict['petugas_id'],
                'petugas_name' => $conflict['petugas_name'],
                'pelanggan_count' => $conflict['pelanggan_count'],
                'pelanggan_ids' => $conflict['pelanggan_ids']
            ];
        }

        // If there are conflicts and we're not confirming changes yet
        if (!empty($conflictingAssignments) && !$request->has('confirm_reassign')) {
            // Return to the form with conflicts for confirmation
            return redirect()->back()
                ->with('conflicts', $conflictingAssignments)
                ->with('new_assignments', $newPelangganIds)
                ->with('warning', 'Terdapat area yang sudah ditugaskan ke petugas lain. Pastikan Anda ingin mengubah penugasan.');
        }

        // If we're confirming changes or no conflicts exist, proceed with updates

        // If confirming changes, remove customer access from other staff members
        if ($request->has('confirm_reassign') && !empty($conflictingAssignments)) {
            foreach ($allPetugas as $otherPetugas) {
                $otherAccessList = [];
                $updated = false;

                // Decode the other staff's access list
                if (!empty($otherPetugas->akses_pelanggan)) {
                    $decoded = json_decode($otherPetugas->akses_pelanggan, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $otherAccessList = array_map('intval', $decoded);

                        // Remove any pelanggan that's being reassigned
                        $originalCount = count($otherAccessList);
                        $otherAccessList = array_diff($otherAccessList, $newPelangganIds);
                        
                        if (count($otherAccessList) !== $originalCount) {
                            $updated = true;
                        }

                        // Re-index the array to avoid having arrays with gaps
                        $otherAccessList = array_values($otherAccessList);

                        // Update if changes were made
                        if ($updated) {
                            $otherPetugas->akses_pelanggan = empty($otherAccessList) ? null : json_encode($otherAccessList);
                            $otherPetugas->save();
                        }
                    }
                }
            }
        }

        // Update the current petugas access list
        if (empty($newPelangganIds)) {
            $petugas->akses_pelanggan = null;
        } else {
            // Pastikan tidak ada duplikasi dan urutkan ID
            $newPelangganIds = array_unique($newPelangganIds);
            sort($newPelangganIds);
            $petugas->akses_pelanggan = json_encode($newPelangganIds);
        }
        
        $petugas->save();

        // Log untuk debugging (opsional, bisa dihapus di production)
        \Log::info('Akses pelanggan updated', [
            'petugas_id' => $id_users,
            'pelanggan_ids' => $newPelangganIds,
            'total_pelanggan' => count($newPelangganIds)
        ]);

        return redirect()->route('petugas.index')->with('berhasil', 'Penugasan area berhasil diperbarui. Total ' . count($newPelangganIds) . ' pelanggan ditugaskan.');
        
    } catch (\Exception $e) {
        // Log error untuk debugging
        \Log::error('Error updating akses pelanggan: ' . $e->getMessage());
        
        return redirect()->back()->with('gagal', 'Terjadi kesalahan saat memperbarui penugasan area. Silakan coba lagi.');
    }
}
}
