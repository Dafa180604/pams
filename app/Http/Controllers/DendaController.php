<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BiayaDenda;

class DendaController extends Controller
{
    public function index()
    {
        $dataBiayaDenda = BiayaDenda::all();
        return view('BiayaDenda.index', ['dataBiayaDenda' => $dataBiayaDenda]);
    }

    public function create()
{
    $maxJumlahTelat = BiayaDenda::max('jumlah_telat');
    $maxBiayaTelat = BiayaDenda::max('biaya_telat');

    return view('BiayaDenda.create', compact('maxJumlahTelat', 'maxBiayaTelat'));
}


    public function store(Request $request)
{
    // Ambil jumlah_telat terbanyak dan biaya_telat terbanyak dari database
    $maxJumlahTelat = BiayaDenda::max('jumlah_telat');
    $maxBiayaTelat = BiayaDenda::max('biaya_telat');
    
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
            function ($attribute, $value, $fail) use ($maxBiayaTelat) {
                if ($value <= $maxBiayaTelat) {
                    $fail('Biaya telat tidak boleh lebih kecil atau sama dengan biaya telat tertinggi ('.$maxBiayaTelat.').');
                }
            }
        ],
    ], [
        'jumlah_telat.required' => 'Batas Bawah Wajib Diisi!',
        'jumlah_telat.numeric' => 'Batas Bawah Harus Menggunakan Angka!',
        'biaya_telat.required' => 'Batas Atas Wajib Diisi!',
        'biaya_telat.numeric' => 'Batas Atas Harus Menggunakan Angka!',
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

        return redirect('/BiayaDenda')->with('success', 'Data berhasil ditambahkan.');
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
                $data->delete();

                return redirect('/BiayaDenda')->with('delete', 'Data berhasil dihapus.');
            }

            return redirect('/BiayaDenda')->with('error', 'Data tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
