<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
    
        // Cari user berdasarkan username
        $user = Users::where('username', $request->username)->first();
    
        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak terdaftar!',
            ], 401);
        }
    
        // Coba login
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
    
            // Generate token menggunakan Laravel Sanctum
            $token = $user->createToken('LaravelApp')->plainTextToken;
    
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil sebagai ' . $user->role,
                'data' => [
                    'id' => $user->id_users,
                    'nama' => $user->nama,
                    'alamat' => $user->alamat,
                    'no_hp' => $user->no_hp,
                    'username' => $user->username,
                    'role' => $user->role,
                    'foto_profile' => $user->foto_profile,
                ],
                'token' => $token // Menambahkan token ke respons
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Password salah!',
            ], 401);
        }
    }

    public function ubahPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        try {
            // Validasi input nomor HP
            $request->validate([
                'no_hp' => 'required|string|min:10|max:15'
            ], [
                'no_hp.required' => 'Nomor WhatsApp harus diisi',
                'no_hp.min' => 'Nomor WhatsApp minimal 10 digit',
                'no_hp.max' => 'Nomor WhatsApp maksimal 15 digit'
            ]);

            $inputPhone = $request->no_hp;

            // Format nomor HP untuk pencarian
            $searchPhones = $this->generatePhoneVariations($inputPhone);

            // Cari user berdasarkan nomor HP
            $user = Users::whereIn('no_hp', $searchPhones)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'Nomor WhatsApp tidak terdaftar di sistem. Silakan datang ke kantor Pamsimas.',
                    'data' => null
                ]);
            }

            // Generate password baru
            $newPassword = $this->generatePassword();

            // Update password
            $user->password = Hash::make($newPassword);
            $user->save();

            Log::info("Password updated for user: {$user->username} (ID: {$user->id})");

            // Kirim ke WhatsApp
            $waResult = $this->sendCredentialsToWhatsApp($user, $newPassword, $inputPhone);

            if ($waResult['success']) {
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'message' => 'Username dan password baru telah dikirim ke WhatsApp Anda. Silakan cek pesan masuk.',
                    'data' => [
                        'username' => $user->username
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'message' => 'Password berhasil direset, namun gagal mengirim ke WhatsApp. Silakan hubungi admin untuk mendapatkan password baru.',
                    'data' => [
                        'username' => $user->username
                    ]
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => $e->validator->errors()->first(),
                'data' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Error in forgotPassword: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi admin.',
                'data' => null
            ]);
        }
    }

    /**
     * Generate variasi format nomor HP untuk pencarian
     */
    private function generatePhoneVariations($phone)
    {
        $variations = [];

        // Bersihkan nomor dari karakter non-digit
        $cleanPhone = preg_replace('/\D/', '', $phone);

        // Tambahkan variasi format
        $variations[] = $cleanPhone; // Original

        if (substr($cleanPhone, 0, 2) === '62') {
            // Jika dimulai dengan 62, tambah variasi dengan 0
            $variations[] = '0' . substr($cleanPhone, 2);
        } elseif (substr($cleanPhone, 0, 1) === '0') {
            // Jika dimulai dengan 0, tambah variasi dengan 62
            $variations[] = '62' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 1) === '8') {
            // Jika dimulai dengan 8, tambah variasi dengan 62 dan 0
            $variations[] = '62' . $cleanPhone;
            $variations[] = '0' . $cleanPhone;
        }

        // Hapus duplikat
        return array_unique($variations);
    }

    /**
     * Generate password baru yang mudah diingat
     */
    private function generatePassword($length = 8)
    {
        // Kombinasi huruf dan angka yang mudah dibaca
        $characters = 'abcdefghijkmnpqrstuvwxyz23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Pastikan ada minimal 1 huruf dan 1 angka
        if (!preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return $this->generatePassword($length); // Recursive jika tidak memenuhi kriteria
        }

        return $password;
    }

    /**
     * Format nomor HP untuk WhatsApp API
     */
    private function formatPhoneForWhatsApp($phone)
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);

        if (substr($cleanPhone, 0, 1) === '0') {
            return '62' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 2) !== '62') {
            return '62' . $cleanPhone;
        }

        return $cleanPhone;
    }

    /**
     * Kirim username dan password ke WhatsApp
     */
    private function sendCredentialsToWhatsApp($user, $newPassword, $inputPhone)
    {
        try {
            $targetPhone = $this->formatPhoneForWhatsApp($inputPhone);

            // Siapkan pesan WhatsApp
            $message = "🔐 *RESET PASSWORD BERHASIL* 🔐\n\n";
            $message .= "*Hai {$user->nama}!*\n\n";
            $message .= "Password Anda telah berhasil direset.\n";
            $message .= "Berikut adalah informasi login terbaru:\n\n";
            $message .= "*📱 Username:* {$user->username}\n";
            $message .= "*🔑 Password Baru:* {$newPassword}\n\n";
            $message .= "⚠️ *PENTING:*\n";
            $message .= "• Segera login dan ganti password Anda\n";
            $message .= "• Jangan bagikan informasi ini kepada siapapun\n";
            $message .= "• Simpan password dengan aman\n\n";
            $message .= "*🌐 Link Login:*\n";
            $message .= "https://dev.airtenggerlor.biz.id/login\n\n";
            $message .= "_Pesan otomatis dari KPSPAMS DS.TENGGERLOR_";

            // Kirim ke WhatsApp API
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
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
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (curl_errno($curl)) {
                $error = curl_error($curl);
                curl_close($curl);

                Log::error("WhatsApp API cURL Error: {$error}");
                return ['success' => false, 'message' => 'Gagal menghubungi layanan WhatsApp'];
            }

            curl_close($curl);

            // Log response untuk debugging
            Log::info("WhatsApp credentials sent to {$targetPhone}. Response: {$response}");

            // Parse response jika diperlukan
            $responseData = json_decode($response, true);

            if ($httpCode === 200) {
                return ['success' => true, 'message' => 'Berhasil mengirim ke WhatsApp'];
            } else {
                Log::warning("WhatsApp API returned HTTP {$httpCode}: {$response}");
                return ['success' => false, 'message' => 'Gagal mengirim ke WhatsApp'];
            }

        } catch (\Exception $e) {
            Log::error('Error sending credentials to WhatsApp: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengirim ke WhatsApp'];
        }
    }


}
