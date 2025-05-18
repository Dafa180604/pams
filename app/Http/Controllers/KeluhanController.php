<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use Illuminate\Http\Request;
use Google\Cloud\Storage\StorageClient;

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

    // Kirim notifikasi WhatsApp ke admin menggunakan Fonnte
    $this->sendWhatsAppNotification($keluhan);

    return redirect()->route('keluhan.index')->with('success', 'Data berhasil ditambahkan.');
}

/**
 * Kirim notifikasi WhatsApp ke admin menggunakan Fonnte API
 * 
 * @param Keluhan $keluhan
 * @return void
 */
private function sendWhatsAppNotification(Keluhan $keluhan)
{
    try {
        // Dapatkan data user yang mengajukan keluhan dari relasi
        $users = \App\Models\Users::find($keluhan->id_users);
        $userName = $users ? $users->nama : 'Pelanggan';
        $userPhone = $users && $users->no_hp ? $users->no_hp : '-';
        // Format pesan dengan informasi lebih detail
        $message = "🔔 *NOTIFIKASI KELUHAN BARU* 🔔\n\n";
        $message .= "*Detail Keluhan:*\n";
        $message .= "-----------------------------------\n";
        $message .= "*ID Keluhan:* " . $keluhan->id_keluhan . "\n";
        $message .= "*Pelanggan:* " . $userName . "\n";
        // Format dan tambahkan nomor telepon dengan link chat WhatsApp
        $formattedPhone = $userPhone;
        if ($userPhone != '-') {
            // Pastikan format nomor HP sesuai (hapus karakter 0 di depan jika ada)
            if (substr($userPhone, 0, 1) === '0') {
                $formattedPhone = '62' . substr($userPhone, 1);
            } elseif (substr($userPhone, 0, 2) !== '62') {
                $formattedPhone = '62' . $userPhone;
            }
            
            // Buat link chat WhatsApp
            // $whatsappLink = "https://wa.me/{$formattedPhone}";
             $message .= "*No. HP:* " . $userPhone. "\n" ;
        } else {
            $message .= "*No. HP:* " . $userPhone . "\n";
        }
        // $message .= "*Status:* " . $keluhan->status . "\n\n";
        $message .= "*Isi Keluhan:*\n" . $keluhan->keterangan . "\n\n";
        
        
        // Tambahkan link untuk melihat detail keluhan
        $detailUrl = "https://dev.airtenggerlor.biz.id/keluhan/{$keluhan->id_keluhan}/edit";
        $message .= "*Lihat Detail & Tanggapi:*\n" . $detailUrl . "\n\n";
        
        $message .= "Silahkan klik link di atas untuk melihat detail dan menanggapi keluhan pelanggan.";
        
        // Ambil nomor telepon admin dari tabel users
    $adminUser = \App\Models\Users::where('role', 'admin')->first();
    
    if ($adminUser && $adminUser->no_hp) {
        $adminPhone = $adminUser->no_hp;
    
        // Format nomor HP ke format internasional (62)
        if (substr($adminPhone, 0, 1) === '0') {
            $adminPhone = '62' . substr($adminPhone, 1);
        } elseif (substr($adminPhone, 0, 2) !== '62') {
            $adminPhone = '62' . $adminPhone;
        }
    } else {
        // Fallback jika tidak ditemukan
        \Log::warning('Admin user atau nomor HP admin tidak ditemukan.');
        return;
    }
 // Akan diformat dengan countryCode
        
        // Menggunakan cURL sesuai contoh dari Fonnte
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $adminPhone,
                'message' => $message,
                'countryCode' => '62', // Kode negara Indonesia
                'device' => '6287769491493', // Device ID
                'typing' => true, // Tampilkan efek mengetik sebelum pesan terkirim
                'delay' => '2', // Delay 2 detik
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: y2GQtBubUi9fsNNcJfN6'
            ),
        ));
        
        $response = curl_exec($curl);
        
        // Tangani error cURL jika ada
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            \Log::error('cURL Error: ' . $error_msg);
        }
        
        curl_close($curl);
        
        // Log response untuk debugging
        \Log::info('WhatsApp notification response: ' . $response);
        
    } catch (\Exception $e) {
        // Log error jika terjadi kesalahan
        \Log::error('Error sending WhatsApp notification: ' . $e->getMessage());
    }
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

        // Hanya ubah status jika awalnya 'Terkirim'
        if ($keluhan->status === 'Terkirim') {
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