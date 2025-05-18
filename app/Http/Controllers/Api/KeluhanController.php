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
            // Dapatkan data user yang mengajukan keluhan
            $user = \App\Models\Users::find($keluhan->id_users);
            $userName = $user?->nama ?? 'Pelanggan';
            $userPhone = $user?->no_hp ?? '-';
    
            // Format nomor user
            $formattedUserPhone = $userPhone;
            if ($userPhone !== '-') {
                if (substr($userPhone, 0, 1) === '0') {
                    $formattedUserPhone = '62' . substr($userPhone, 1);
                } elseif (substr($userPhone, 0, 2) !== '62') {
                    $formattedUserPhone = '62' . $userPhone;
                }
            }
    
            // Siapkan pesan WhatsApp
            $message = "🔔 *NOTIFIKASI KELUHAN BARU* 🔔\n\n";
            $message .= "*Detail Keluhan:*\n";
            $message .= "-----------------------------------\n";
            $message .= "*ID Keluhan:* {$keluhan->id_keluhan}\n";
            $message .= "*Pelanggan:* {$userName}\n";
            $message .= "*No. HP:* {$userPhone}\n";
            $message .= "*Isi Keluhan:*\n{$keluhan->keterangan}\n\n";
            $message .= "*Lihat Detail & Tanggapi:*\n";
            $message .= "https://dev.airtenggerlor.biz.id/keluhan/{$keluhan->id_keluhan}/edit\n\n";
            $message .= "Silakan klik link di atas untuk menanggapi keluhan.";
    
            // Ambil semua nomor admin
            $adminUsers = \App\Models\Users::where('role', 'admin')->get();
    
            if ($adminUsers->isEmpty()) {
                \Log::warning('Tidak ditemukan user dengan role admin.');
                return;
            }
    
            $adminPhones = [];
    
            foreach ($adminUsers as $admin) {
                $phone = $admin->no_hp;
    
                if ($phone) {
                    if (substr($phone, 0, 1) === '0') {
                        $phone = '62' . substr($phone, 1);
                    } elseif (substr($phone, 0, 2) !== '62') {
                        $phone = '62' . $phone;
                    }
    
                    $adminPhones[] = $phone;
                }
            }
    
            if (empty($adminPhones)) {
                \Log::warning('Tidak ada nomor HP admin yang valid.');
                return;
            }
    
            // Kirim pesan ke semua nomor admin
            foreach ($adminPhones as $targetPhone) {
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
                        'target' => $targetPhone,
                        'message' => $message,
                        'countryCode' => '62',
                        'device' => '6287769491493', // Ganti sesuai Device ID Anda
                        'typing' => true,
                        'delay' => 2,
                    ),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: y2GQtBubUi9fsNNcJfN6', // Ganti dengan token API Anda
                    ),
                ));
    
                $response = curl_exec($curl);
    
                if (curl_errno($curl)) {
                    \Log::error('cURL Error: ' . curl_error($curl));
                }
    
                curl_close($curl);
    
                // Log response untuk debugging
                \Log::info("WhatsApp notification sent to {$targetPhone}: " . $response);
            }
    
        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp notification: ' . $e->getMessage());
        }
    }


}
