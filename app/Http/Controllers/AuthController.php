<?php
namespace App\Http\Controllers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login()
    {
        if (Auth::check()) {
            // Periksa peran pengguna
            if (Auth::user()->role === 'admin') {
                $successMessage = 'Anda berhasil masuk sebagai <br>' . Auth::user()->nama;
                return redirect()->route('DashboardAdmin.index')->with('successlogin', $successMessage);
            } elseif (Auth::user()->role === 'petugas') {
                $successMessage = 'Anda berhasil masuk sebagai <br>' . Auth::user()->nama;
                return redirect()->route('belumlunas.index')->with('successlogin', $successMessage);
            }
        }
        return view('auth.login');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function loginsukses(Request $request)
    {
        // Menyimpan data username ke sesi (untuk mengingat username meskipun login gagal)
        Session::flash('username', $request->username);
        // Validasi input
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username Wajib Diisi!',
            'password.required' => 'Password Wajib Diisi!'
        ]);
        // Mencari user berdasarkan username
        $user = Users::where('username', $request->username)->first();
        // Jika username tidak ditemukan
        if (!$user) {
            return redirect()->route('login')->with('errorusername', 'Username tidak terdaftar!');
        }
        // Jika username ditemukan, coba login dengan username dan password
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            // Periksa peran pengguna
            if ($user->role === 'admin') {
                $successMessage = 'Anda berhasil masuk sebagai <br>' . Auth::user()->nama;
                return redirect()->route('DashboardAdmin.index')->with('successlogin', $successMessage);
            } elseif ($user->role === 'petugas') {
                $successMessage = 'Anda berhasil masuk sebagai <br>' . Auth::user()->nama;
                return redirect()->route('belumlunas.index')->with('successlogin', $successMessage);
            } else {
                // Jika peran tidak sesuai, logout dan kembalikan ke halaman login
                Auth::logout();
                return redirect()->route('login')->with('errorlogin', 'Peran tidak dikenali!');
            }
        } else {
            // Jika password salah
            return redirect()->route('login')->with('errorpassword', 'Password Salah!');
        }
    }
    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('berhasillogout', 'logout');
    }
    public function lupaPassword()
    {
        return view('auth.lupa-password'); // Pastikan file blade ini ada
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
                    'message' => 'Nomor WhatsApp tidak terdaftar di sistem. Silakan hubungi admin untuk registrasi.'
                ], 404);
            }

            // Generate password baru (8 karakter, mudah diingat)
            $newPassword = $this->generatePassword();

            // Update password di database (hash)
            $user->password = Hash::make($newPassword);
            $user->save();

            Log::info("Password updated for user: {$user->username} (ID: {$user->id})");

            // Kirim username dan password ke WhatsApp
            $waResult = $this->sendCredentialsToWhatsApp($user, $newPassword, $inputPhone);

            if ($waResult['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Username dan password baru telah dikirim ke WhatsApp Anda. Silakan cek pesan masuk.'
                ]);
            } else {
                // Jika gagal kirim WA, rollback password (opsional)
                // Atau biarkan password sudah terupdate tapi beri pesan berbeda
                return response()->json([
                    'success' => true,
                    'message' => 'Password berhasil direset, namun gagal mengirim ke WhatsApp. Silakan hubungi admin untuk mendapatkan password baru.'
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error in forgotPassword: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi admin.'
            ], 500);
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

    /**
     * Alternative method: Generate password dengan pola yang lebih mudah diingat
     */
    private function generateFriendlyPassword()
    {
        $adjectives = ['Smart', 'Quick', 'Happy', 'Lucky', 'Bright'];
        $numbers = rand(10, 99);

        $adjective = $adjectives[array_rand($adjectives)];
        return strtolower($adjective) . $numbers;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}