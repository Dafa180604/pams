<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;

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

}
