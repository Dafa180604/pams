<?php

namespace App\Http\Controllers;

use App\Models\Pemakaian;
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
    // Find the officer by ID
    $data = Users::findOrFail($id);

    // Initialize query for pemakaian records using the officer's name
    $pemakaianQuery = Pemakaian::where('petugas', $data->id_users);

    // Filter by month if provided
    if ($request->has('end_date')) {
        $selectedMonth = $request->input('end_date');
        
        // Parse the month input (YYYY-MM format)
        $startDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();

        // Filter records between the start and end of the selected month
        $pemakaianQuery->whereBetween('waktu_catat', [$startDate, $endDate]);
    }

    // Paginate the pemakaian records
    $pencatatan = $pemakaianQuery->with(['users'])
        ->orderBy('waktu_catat', 'desc')
        ->paginate(10);

    // Return the view with data
    return view('petugas.detail', [
        'data' => $data, 
        'pencatatan' => $pencatatan
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
            'no_hp' => 'required|numeric',
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
            'no_hp.required' => 'Nomor no_hp Wajib Diisi!',
            'no_hp.numeric' => 'Nomor no_hp Harus Berupa Angka!',
            'no_hp.digits_between' => 'Nomor no_hp Harus Berjumlah 10 hingga 13 Digit!',
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
        $defaultPassword = '123456';
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
        'no_hp' => 'required|numeric',
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
        'no_hp.required' => 'Nomor no_hp Wajib Diisi!',
        'no_hp.numeric' => 'Nomor no_hp Harus Berupa Angka!',
        'no_hp.digits_between' => 'Nomor no_hp Harus Berjumlah 10 hingga 13 Digit!',
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
        $dataPelanggan = Users::where('role', 'pelanggan')
            ->when($filterAlamat, function ($query, $filterAlamat) {
                $query->where('alamat', 'like', "%{$filterAlamat}%");
            })
            ->when($filterRW, function ($query, $filterRW) {
                $query->where('rw', $filterRW);
            })
            ->when($filterRT, function ($query, $filterRT) {
                $query->where('rt', $filterRT);
            })
            ->get();

        // Ambil daftar ID pelanggan yang sudah diassign ke petugas ini
        $aksesPelanggan = [];
        if (!empty($petugas->akses_pelanggan)) {
            $decoded = json_decode($petugas->akses_pelanggan, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $aksesPelanggan = $decoded;
            }
        }

        // Ambil data untuk dropdown filter hanya dari pelanggan
        $alamatList = Users::where('role', 'pelanggan')->select('alamat')->distinct()->pluck('alamat');
        $rwList = Users::where('role', 'pelanggan')->select('rw')->distinct()->pluck('rw');
        $rtList = Users::where('role', 'pelanggan')->select('rt')->distinct()->pluck('rt');

        return view('petugas.pilih_pelanggan', [
            'dataPelanggan' => $dataPelanggan,
            'aksesPelanggan' => $aksesPelanggan,
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
        $petugas = Users::findOrFail($id_users);

        // Update akses pelanggan
        $petugas->akses_pelanggan = json_encode($request->pelanggan_ids ?? []);
        $petugas->save();

        return redirect('/petugas')->with('berhasil', 'Pilih Penugasan berhasil diperbarui.');
    }
}
