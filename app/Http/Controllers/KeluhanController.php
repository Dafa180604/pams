<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Http;

class KeluhanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataKeluhan = Keluhan::orderBy('created_at', 'desc')->get();
        return view('keluhan.index', ['dataKeluhan' => $dataKeluhan]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('keluhan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'keterangan' => 'required',
        ], [
            'keterangan.required' => 'Keterangan wajib diisi!',
        ]);

        // Membuat data keluhan baru
        $keluhan = new Keluhan();
        $keluhan->id_users = auth()->id();
        $keluhan->keterangan = $request->keterangan;
        $keluhan->status = $request->status ?? 'Terkirim';
        $keluhan->tanggal = now();
        $keluhan->foto_keluhan = null;

        // Simpan keluhan terlebih dahulu untuk mendapatkan ID
        $keluhan->save();

        // Upload foto ke Firebase jika ada
        if ($request->hasFile('foto_keluhan')) {
            $file = $request->file('foto_keluhan');
            $fileName = 'foto_keluhan/' . $keluhan->id_keluhan . '/' . time() . '_' . $file->getClientOriginalName();

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
            $keluhan->foto_keluhan = $fotoUrl;
            $keluhan->save();
        }

        // Simpan data ke Firebase Realtime Database dengan ID yang sama
        $firebaseUrl = 'https://dafaq-542a5-default-rtdb.asia-southeast1.firebasedatabase.app/keluhan/' . $keluhan->id_keluhan . '.json';

        $firebaseData = [
            'id_keluhan' => $keluhan->id_keluhan,
            'id_users' => $keluhan->id_users,
            'keterangan' => $keluhan->keterangan,
            'tanggal' => $keluhan->tanggal,
            'foto_keluhan' => $keluhan->foto_keluhan,
            'tanggapan' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        $response = Http::put($firebaseUrl, $firebaseData);

        if ($response->failed()) {
            // Jika gagal menyimpan ke Firebase, hapus data lokal yang sudah disimpan
            $keluhan->delete();
            throw new \Exception('Gagal menyimpan data ke Firebase');
        }

        return redirect()->route('keluhan.index')->with('success', 'Data berhasil ditambahkan.');
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
    public function edit(string $id_keluhan)
    {
        $keluhan = Keluhan::findOrFail($id_keluhan);

        if ($keluhan->status !== 'Dibaca') {
            $keluhan->status = 'Dibaca';
            $keluhan->save();
        }

        return view('keluhan.edit', ['data' => $keluhan]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id_keluhan)
    {
        // Validasi data input
        $request->validate([
            'tanggapan' => 'required',
        ], [
            'tanggapan.required' => 'Tanggapan Wajib Diisi!',
        ]);

        try {
            // Update database lokal
            $data = Keluhan::findOrFail($id_keluhan);
            $data->tanggapan = $request->tanggapan;
            $data->status = $request->status ?? 'Diproses';
            $data->save();

            // Update data di Firebase Realtime Database pada path yang sama berdasarkan ID
            $firebaseUrl = 'https://dafaq-542a5-default-rtdb.asia-southeast1.firebasedatabase.app/keluhan/' . $id_keluhan . '.json';

            $firebaseData = [
                'tanggapan' => $request->tanggapan,
                'updated_at' => now()->toDateTimeString(),
            ];

            // Gunakan PATCH untuk hanya mengupdate field yang diubah
            $response = Http::patch($firebaseUrl, $firebaseData);

            if ($response->failed()) {
                throw new \Exception('Gagal memperbarui data di Firebase');
            }

            return redirect('/keluhan')->with('update', 'Data berhasil diperbarui.');
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