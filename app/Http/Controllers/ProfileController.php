<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Google\Cloud\Storage\StorageClient;
class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil data pengguna yang sedang login
        $data = Auth::user();

        return view('auth.profile', compact('data'));
    }

    public function edit(string $username)
    {
        $data = Users::where('username', $username)->firstOrFail();
        // Memeriksa apakah pengguna yang login adalah yang memiliki username yang akan diubah
        if ($data->username !== Auth::user()->username) {
            // Redirect jika username tidak cocok
            return redirect()->route('profile.index')->with('error', 'Anda tidak memiliki izin untuk mengedit profil ini.');
        }

        return view('auth.edit-profile', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $username)
    {
        // Find user by username
        $user = Users::where('username', $username)->firstOrFail();
        $id_users = $user->id_users;

        // Validasi data input
        $validationRules = [
            'nama' => 'required',
            'alamat' => 'required',
            'rt' => 'required|numeric',
            'rw' => 'required|numeric',
            'no_hp' => 'required|numeric',
            'username' => 'required|unique:users,username,' . $id_users . ',id_users',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg',
        ];

        $validationMessages = [
            'nama.required' => 'Nama Wajib Diisi!',
            'alamat.required' => 'Alamat Wajib Diisi!',
            'rt.required' => 'RT Wajib Diisi!',
            'rt.numeric' => 'RT Wajib Angka!',
            'rw.required' => 'RW Wajib Diisi!',
            'rw.numeric' => 'RW Wajib Angka!',
            'no_hp.required' => 'Nomor no_hp Wajib Diisi!',
            'no_hp.numeric' => 'Nomor no_hp Harus Berupa Angka!',
            'username.required' => 'Username Wajib Diisi!',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'foto_profile.image' => 'File harus berupa gambar!',
            'foto_profile.mimes' => 'Format gambar harus jpeg, png, atau jpg!',
        ];

        $request->validate($validationRules, $validationMessages);

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

            // Upload foto profile jika ada
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

            return redirect('/profile')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Update error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function editPassword(string $username)
    {
        $data = Users::where('username', operator: $username)->firstOrFail();
        // Memeriksa apakah pengguna yang login adalah yang memiliki username yang akan diubah
        if ($data->username !== Auth::user()->username) {
            // Redirect jika username tidak cocok
            return redirect()->route('profile.index')->with('error', 'Anda tidak memiliki izin untuk mengedit profil ini.');
        }

        return view('auth.edit-password', compact('data'));
    }
    public function updatePassword(Request $request, string $username)
    {
        // Validasi data input
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required',
            'password_baru_confirmation' => 'required|same:password_baru',
        ], [
            'password_lama.required' => 'Password lama wajib diisi!',
            'password_baru.required' => 'Password baru wajib diisi!',
            'password_baru_confirmation.required' => 'Ulangi password baru wajib diisi!',
            'password_baru_confirmation.same' => 'Konfirmasi password baru tidak sesuai!',
        ]);

        $user = Users::where('username', $username)->firstOrFail();

        if (!Hash::check($request->password_lama, $user->password)) {
            return redirect()->back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return redirect()->route('profile.index')->with('successPassword', 'Password berhasil diperbarui.');
    }
}
