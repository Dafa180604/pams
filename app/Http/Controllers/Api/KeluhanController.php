<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keluhan;
use Illuminate\Support\Facades\Http;
use Google\Cloud\Storage\StorageClient; 
use Illuminate\Support\Facades\Auth;

class KeluhanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $keluhan = Keluhan::with('users') 
            ->when($user->role === 'pelanggan', function ($query) use ($user) {
                return $query->where('id_users', $user->id_users); 
            })
            ->when($search, function ($query, $search) {
                return $query->where('keterangan', 'like', "%$search%")
                            ->orWhere('tanggapan', 'like', "%$search%");
            })
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage);

        // Ambil hanya field yang diinginkan
        $data = $keluhan->getCollection()->map(function ($item) {
            return [
                'id_keluhan'   => $item->id_keluhan,
                'id_users'     => $item->id_users,
                'nama_pelapor' => $item->users->nama ?? '-',
                'no_hp'         => $item->users->no_hp ?? '-',
                'keterangan'   => $item->keterangan,
                'status'       => $item->status,
                'foto_keluhan' => $item->foto_keluhan,
                'tanggal'      => $item->tanggal,
                'tanggapan'    => $item->tanggapan,
            ];
        });

        // Gabungkan data dengan pagination info
        $paginated = $keluhan->toArray();
        $paginated['data'] = $data;

        return response()->json([
            'success' => true,
            'message' => 'Data keluhan berhasil diambil',
            'data' => $paginated
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'keterangan' => 'required',
        ], [
            'keterangan.required' => 'Keterangan wajib diisi!',
        ]);

        try {
            $keluhan = new Keluhan();
            $keluhan->id_users = auth()->id();
            $keluhan->keterangan = $request->keterangan;
            $keluhan->status = $request->status ?? 'Terkirim';
            $keluhan->tanggal = now();
            $keluhan->foto_keluhan = null;
            $keluhan->save();

            if ($request->hasFile('foto_keluhan')) {
                $file = $request->file('foto_keluhan');
                $fileName = 'foto_keluhan/' . $keluhan->id_keluhan . '/' . time() . '_' . $file->getClientOriginalName();

                $storage = new StorageClient([
                    'keyFilePath' => base_path('app/firebase/dafaq-542a5-firebase-adminsdk-nezyi-2e2d42888b.json'),
                ]);

                $bucketName = env('FIREBASE_STORAGE_BUCKET', 'dafaq-542a5.appspot.com');
                $bucket = $storage->bucket($bucketName);

                $bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $fileName,
                    ]
                );

                $object = $bucket->object($fileName);
                $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);
                $fotoUrl = 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;

                $keluhan->foto_keluhan = $fotoUrl;
                $keluhan->save();
            }

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
                $keluhan->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data ke Firebase',
                ], 500);
            }
            
            // Kirim notifikasi WhatsApp ke admin menggunakan Fonnte
            $this->sendWhatsAppNotification($keluhan);

            return response()->json([
                'success' => true,
                'message' => 'Keluhan berhasil disimpan',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
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

}
