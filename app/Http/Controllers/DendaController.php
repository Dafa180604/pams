<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BiayaDenda;

class DendaController extends Controller
{
    public function index()
    {
        $dataBiayaDenda = BiayaDenda::orderBy('jumlah_telat', 'asc')->get();
        
        // Cek apakah sudah ada data yang ditandai maksimal melalui session/cache
        $hasMaxDenda = session('denda_maksimal_set', false);
        
        return view('BiayaDenda.index', compact('dataBiayaDenda', 'hasMaxDenda'));
    }

    public function create()
    {
        // Cek apakah sudah ditandai sebagai maksimal
        $hasMaxDenda = session('denda_maksimal_set', false);
        
        if ($hasMaxDenda) {
            return redirect('/BiayaDenda')->with('error', 'Data denda maksimal sudah ada. Hapus data paling bawah terlebih dahulu jika ingin menambah data baru.');
        }

        $maxJumlahTelat = BiayaDenda::max('jumlah_telat');
        $maxBiayaTelat = BiayaDenda::max('biaya_telat');

        return view('BiayaDenda.create', compact('maxJumlahTelat', 'maxBiayaTelat'));
    }

    public function store(Request $request)
{
    // Cek apakah sudah ditandai sebagai maksimal
    $hasMaxDenda = session('denda_maksimal_set', false);
    
    if ($hasMaxDenda) {
        return redirect('/BiayaDenda')->with('error', 'Data denda maksimal sudah ada. Hapus data paling bawah terlebih dahulu jika ingin menambah data baru.');
    }

    // Ambil jumlah_telat terbanyak dan biaya_telat terbanyak dari database
    $maxJumlahTelat = BiayaDenda::max('jumlah_telat');
    $maxBiayaTelat = BiayaDenda::max('biaya_telat');
    
    // Jika checkbox denda maksimal dicentang dan biaya telat kosong, set ke 1000000
    if ($request->has('is_max') && empty($request->biaya_telat)) {
        $request->merge(['biaya_telat' => 1000000]);
    }
    
    // Validasi data input
    $request->validate([
        'jumlah_telat' => [
            'required',
            'numeric',
            function ($attribute, $value, $fail) use ($maxJumlahTelat) {
                if ($value <= $maxJumlahTelat) {
                    $fail('Jumlah telat tidak boleh lebih kecil atau sama dengan jumlah telat terbanyak ('.$maxJumlahTelat.').');
                }
            }
        ],
        'biaya_telat' => [
            'required',
            'numeric',
            function ($attribute, $value, $fail) use ($maxBiayaTelat, $request) {
                // Skip validasi jika ini adalah denda maksimal
                if (!$request->has('is_max') && $value <= $maxBiayaTelat) {
                    $fail('Biaya telat tidak boleh lebih kecil atau sama dengan biaya telat tertinggi ('.$maxBiayaTelat.').');
                }
            }
        ],
        'is_max' => 'sometimes|boolean',
    ], [
        'jumlah_telat.required' => 'Jumlah telat Wajib Diisi!',
        'jumlah_telat.numeric' => 'Jumlah telat Harus Menggunakan Angka!',
        'biaya_telat.required' => 'Biaya telat Wajib Diisi!',
        'biaya_telat.numeric' => 'Biaya telat Harus Menggunakan Angka!',
    ]);

    try {
        // Logika untuk menentukan id yang kosong
        $id = 1;
        $existingIds = BiayaDenda::withTrashed()->pluck('id_biaya_denda')->sort()->values();

        foreach ($existingIds as $existingId) {
            if ($id != $existingId) {
                break;
            }
            $id++;
        }

        // Membuat data kategori biaya air baru di database lokal
        $data = new BiayaDenda();
        $data->id_biaya_denda = $id;
        $data->jumlah_telat = $request->jumlah_telat;
        $data->biaya_telat = $request->biaya_telat;
        $data->save();

        $message = 'Data berhasil ditambahkan.';
        
        // Jika admin mencentang sebagai maksimal, simpan ke session
        if ($request->has('is_max')) {
            session(['denda_maksimal_set' => true]);
            session(['denda_maksimal_id' => $id]);
            $message .= ' Data ini telah ditetapkan sebagai denda maksimal.';
        }

        return redirect('/BiayaDenda')->with('success', $message);
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    public function edit(string $id_biaya_denda)
    {
        $data = BiayaDenda::find($id_biaya_denda);
        return view('BiayaDenda.edit', compact('data'));
    }

    public function update(Request $request, string $id_biaya_denda)
    {
        // Validasi data input
        $request->validate([
            'jumlah_telat' => 'required|numeric',
            'biaya_telat' => 'required|numeric',
        ], [
            'jumlah_telat.required' => 'Batas Bawah Wajib Diisi!',
            'jumlah_telat.numeric' => 'Batas Bawah Harus Menggunakan Angka!',
            'biaya_telat.required' => 'Batas Atas Wajib Diisi!',
            'biaya_telat.numeric' => 'Batas Atas Harus Menggunakan Angka!',
        ]);

        try {
            // Update database lokal
            $data = BiayaDenda::findOrFail($id_biaya_denda);
            $data->jumlah_telat = $request->jumlah_telat;
            $data->biaya_telat = $request->biaya_telat;
            $data->update();

            return redirect('/BiayaDenda')->with('update', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            // Hapus data dari database lokal
            $data = BiayaDenda::find($id);
            if ($data) {
                // Jika data yang dihapus adalah data maksimal, hapus juga session
                if (session('denda_maksimal_id') == $id) {
                    session()->forget(['denda_maksimal_set', 'denda_maksimal_id']);
                }
                
                $data->delete();

                return redirect('/BiayaDenda')->with('delete', 'Data berhasil dihapus.');
            }

            return redirect('/BiayaDenda')->with('error', 'Data tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}