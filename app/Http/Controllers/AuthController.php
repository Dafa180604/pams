<?php
namespace App\Http\Controllers;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
                return redirect()->route('petugas.index')->with('successlogin', $successMessage);
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
                return redirect()->route('petugas.index')->with('successlogin', $successMessage);
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