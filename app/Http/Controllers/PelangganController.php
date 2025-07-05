<?php

namespace App\Http\Controllers;

use App\Models\BiayaGolonganBerbayar;
use App\Models\Laporan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use App\Models\Users;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Hash;

class PelangganController extends Controller
{
    public function index()
    {
        $dataPelanggan = Users::where('role', 'pelanggan')->get();
        return view('pelanggan.index', ['dataPelanggan' => $dataPelanggan]);
    }


    public function show($id_users)
    {
        $dataTransaksi = Transaksi::with(['pemakaian.users'])
            ->whereHas('pemakaian', function ($query) use ($id_users) {
                $query->where('id_users', $id_users);
            })
            ->orderBy('created_at', 'desc') // urutkan dari terbaru
            ->get();

        // Ambil data pelanggan secara langsung
        $pelanggan = Users::find($id_users);

        // Kumpulkan semua ID petugas dengan penanganan nilai terpisah koma
        $petugasIds = collect();
        foreach ($dataTransaksi as $transaksi) {
            if ($transaksi->pemakaian && $transaksi->pemakaian->petugas) {
                // Handle comma-separated IDs
                $ids = explode(',', $transaksi->pemakaian->petugas);
                foreach ($ids as $id) {
                    $petugasIds->push(trim($id));
                }
            }
        }
        $petugasIds = $petugasIds->unique()->filter();

        // Ambil data petugas dalam satu query
        $petugasUsers = [];
        if ($petugasIds->isNotEmpty()) {
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }

        return view('pelanggan.detail', [
            'dataTransaksi' => $dataTransaksi,
            'pelanggan' => $pelanggan,
            'petugasUsers' => $petugasUsers,
        ]);
    }



    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'rt' => 'required|numeric',
            'rw' => 'required|numeric',
            'no_hp' => 'required|numeric|unique:users',
            'username' => 'required|unique:users',
            'golongan' => 'required',
            'jumlah_air' => 'required|numeric',
        ], [
            'nama.required' => 'Nama Wajib Diisi!',
            'alamat.required' => 'Alamat Wajib Diisi!',
            'rt.required' => 'RT Wajib Diisi!',
            'rt.numeric' => 'RT Wajib Angka!',
            'rw.required' => 'RW Wajib Diisi!',
            'rw.numeric' => 'RW Wajib Angka!',
            'no_hp.required' => 'Nomor HP Wajib Diisi!',
            'no_hp.numeric' => 'Nomor HP Harus Berupa Angka!',
            'no_hp.digits_between' => 'Nomor HP Harus Berjumlah 10 hingga 13 Digit!',
            'no_hp.unique' => 'Nomor sudah digunakan, silakan pilih yang lain.',
            'username.required' => 'Username Wajib Diisi!',
            'golongan.required' => 'Golongan Wajib Diisi!',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'jumlah_air.required' => 'Jumlah Air Wajib Diisi!',
            'jumlah_air' => 'Jumlah Air Harus Berupa Angka!',
        ]);

        //Buat Password
        // Menyusun password yang akan disimpan
        $defaultPassword = 'PelangganPams';
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
            $data->role = 'pelanggan';
            $data->golongan = $request->golongan;
            $data->status = 'Aktif';
            $data->jumlah_air = $request->jumlah_air;
            $data->save();

            // Update akses petugas untuk pelanggan baru
            $petugas = Users::where('role', 'petugas')
                ->where('alamat', $request->alamat)
                ->where('rt', $request->rt)
                ->where('rw', $request->rw)
                ->first();

            if ($petugas) {
                $akses = json_decode($petugas->akses_pelanggan, true) ?: [];
                // Tambah id_users pelanggan baru ke array akses jika belum ada
                if (!in_array($idUsers, $akses)) {
                    $akses[] = $idUsers;
                    $petugas->akses_pelanggan = json_encode($akses);
                    $petugas->update();
                }
            }

            // Jika golongan 'Berbayar', tambahkan entry ke tabel laporan
            if ($request->golongan == 'Berbayar') {
                // Ambil tarif dari tabel biaya_golongan_berbayar
                $tarif = BiayaGolonganBerbayar::first()->tarif;

                // Buat laporan baru
                $laporan = new Laporan();
                $laporan->tanggal = now();
                $laporan->uang_masuk = $tarif;
                $laporan->keterangan = "Biaya pasang {$request->nama}";
                $laporan->save();
            }

            return redirect('/pelanggan')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(string $id_users)
    {
        $data = Users::find($id_users);
        return view('pelanggan.edit', compact('data'));
    }

    public function update(Request $request, string $id_users)
    {
        $users = Users::findOrFail($id_users);
        // Validasi data input
        $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'rt' => 'required|numeric',
            'rw' => 'required|numeric',
            'no_hp' => 'required|numeric|unique:users,no_hp,' . $users->id_users . ',id_users',
            'username' => 'required|unique:users,username,' . $users->id_users . ',id_users',
            'jumlah_air' => 'required|numeric',
        ], [
            'nama.required' => 'Nama Wajib Diisi!',
            'alamat.required' => 'Alamat Wajib Diisi!',
            'rt.required' => 'RT Wajib Diisi!',
            'rt.numeric' => 'RT Wajib Angka!',
            'rw.required' => 'RW Wajib Diisi!',
            'rw.numeric' => 'RW Wajib Angka!',
            'no_hp.required' => 'Nomor HP Wajib Diisi!',
            'no_hp.numeric' => 'Nomor HP Harus Berupa Angka!',
            'no_hp.digits_between' => 'Nomor HP Harus Berjumlah 10 hingga 13 Digit!',
            'no_hp.unique' => 'Nomor sudah digunakan, silakan pilih yang lain.',
            'username.required' => 'Username Wajib Diisi!',
            'username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'jumlah_air.required' => 'Jumlah Air Wajib Diisi!',
            'jumlah_air' => 'Jumlah Air Harus Berupa Angka!',
        ]);

        try {
            // Simpan data lama untuk cek perubahan
            $oldAlamat = $users->alamat;
            $oldRt = $users->rt;
            $oldRw = $users->rw;
            $oldGolongan = $users->golongan;
            $newAlamat = $request->alamat;
            $newRt = $request->rt;
            $newRw = $request->rw;
            $newGolongan = $request->golongan;

            // Update database lokal
            $data = Users::findOrFail($id_users);
            $data->nama = $request->nama;
            $data->alamat = $request->alamat;
            $data->rw = $request->rw;
            $data->rt = $request->rt;
            $data->no_hp = $request->no_hp;
            $data->username = $request->username;
            if (!empty($request->password)) {
                $data->password = Hash::make($request->password);
            }
            $data->role = 'pelanggan';
            $data->golongan = $newGolongan;
            $data->jumlah_air = $request->jumlah_air;
            $data->update();

            // Update akses petugas jika alamat, RT, atau RW berubah
            if ($oldAlamat != $newAlamat || $oldRt != $newRt || $oldRw != $newRw) {
                // Hapus akses pelanggan dari petugas wilayah lama (jika ada)
                if ($oldAlamat && $oldRt && $oldRw) {
                    $petugasLama = Users::where('role', 'petugas')
                        ->where('alamat', $oldAlamat)
                        ->where('rt', $oldRt)
                        ->where('rw', $oldRw)
                        ->first();

                    if ($petugasLama && $petugasLama->akses_pelanggan) {
                        $aksesLama = json_decode($petugasLama->akses_pelanggan, true) ?: [];
                        // Hapus id_users dari array akses
                        $aksesLama = array_values(array_filter($aksesLama, function ($id) use ($id_users) {
                            return $id != $id_users;
                        }));
                        $petugasLama->akses_pelanggan = json_encode($aksesLama);
                        $petugasLama->update();
                    }
                }

                // Tambah akses pelanggan ke petugas wilayah baru (jika ada)
                $petugasBaru = Users::where('role', 'petugas')
                    ->where('alamat', $newAlamat)
                    ->where('rt', $newRt)
                    ->where('rw', $newRw)
                    ->first();

                if ($petugasBaru) {
                    $aksesBaru = json_decode($petugasBaru->akses_pelanggan, true) ?: [];
                    // Tambah id_users ke array akses jika belum ada
                    if (!in_array($id_users, $aksesBaru)) {
                        $aksesBaru[] = $id_users;
                        $petugasBaru->akses_pelanggan = json_encode($aksesBaru);
                        $petugasBaru->update();
                    }
                }
            }

            // Cek perubahan golongan
            if ($oldGolongan != $newGolongan) {
                // Jika perubahan dari Bantuan ke Berbayar
                if ($oldGolongan == 'Bantuan' && $newGolongan == 'Berbayar') {
                    // Ambil tarif dari tabel biaya_golongan_berbayar
                    $tarif = BiayaGolonganBerbayar::first()->tarif;

                    // Buat laporan baru
                    $laporan = new Laporan();
                    $laporan->tanggal = now();
                    $laporan->uang_masuk = $tarif;
                    $laporan->keterangan = "Biaya pasang {$request->nama}";
                    $laporan->save();
                }
                // Jika perubahan dari Berbayar ke Bantuan
                else if ($oldGolongan == 'Berbayar' && $newGolongan == 'Bantuan') {
                    // Cari dan hapus laporan terkait pengguna ini
                    // Catatan: ini akan menghapus laporan pasang terbaru untuk pengguna ini
                    // dengan asumsi format keterangan konsisten
                    Laporan::where('keterangan', 'like', "Biaya pasang {$request->nama}%")
                        ->orderBy('created_at', 'desc')
                        ->first()
                            ?->delete();
                }
            }

            return redirect('/pelanggan')->with('update', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id_users)
    {
        try {
            // Hapus data dari database lokal
            $data = Users::find($id_users);
            if ($data) {
                $data->delete();

                return redirect('/pelanggan')->with('delete', 'Data berhasil dihapus.');
            }

            return redirect('/pelanggan')->with('error', 'Data tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function cetakSemuaQr(Request $request)
    {
        $ids = json_decode($request->ids);
        $dataPelanggan = Users::whereIn('id_users', $ids)->get();

        foreach ($dataPelanggan as $users) {
            $users->qrCode = QrCode::size(150)->generate($users->id_users);
        }

        return view('pelanggan.cetakSemua_qr', compact('dataPelanggan'));
    }


}
