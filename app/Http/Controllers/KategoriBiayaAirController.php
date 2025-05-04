<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriBiayaAir;

class KategoriBiayaAirController extends Controller
{
    public function index()
    {
        $dataKategoriBiayaAir = KategoriBiayaAir::all();
        return view('KategoriBiayaAir.index', ['dataKategoriBiayaAir' => $dataKategoriBiayaAir]);
    }

    public function create()
    {
        // Get the last meter reading
        $last_reading = KategoriBiayaAir::latest()->first();
        $last_meter_akhir = $last_reading ? $last_reading->batas_atas + 1 : 0;

        return view('KategoriBiayaAir.create', compact('last_meter_akhir'));
    }

    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'batas_bawah' => 'required|numeric',
            'batas_atas' => 'required|numeric|gt:batas_bawah',
            'tarif' => 'required|numeric',
        ], [
            'batas_bawah.required' => 'Batas Bawah Wajib Diisi!',
            'batas_bawah.numeric' => 'Batas Bawah Harus Menggunakan Angka!',
            'batas_atas.required' => 'Batas Atas Wajib Diisi!',
            'batas_atas.numeric' => 'Batas Atas Harus Menggunakan Angka!',
            'batas_atas.gt' => 'Batas atas harus lebih besar dari batas bawah.',
            'tarif.required' => 'Tarif Wajib Diisi!',
            'tarif.numeric' => 'Tarif Harus Menggunakan Angka!',
        ]);

        try {
            // Logika untuk menentukan id yang kosong
            $id = 1;    
            $existingIds = KategoriBiayaAir::withTrashed()->pluck('id_kategori_biaya')->sort()->values();

            foreach ($existingIds as $existingId) {
                if ($id != $existingId) {
                    break;
                }
                $id++;
            }

            // Membuat data kategori biaya air baru di database lokal
            $data = new KategoriBiayaAir();
            $data->id_kategori_biaya = $id;
            $data->batas_bawah = $request->batas_bawah;
            $data->batas_atas = $request->batas_atas;
            $data->tarif = $request->tarif;
            $data->save();

            return redirect('/kategoribiayaair')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(string $id_kategori_biaya)
    {
        $data = KategoriBiayaAir::find($id_kategori_biaya);
        return view('KategoriBiayaAir.edit', compact('data'));
    }

    public function update(Request $request, string $id_kategori_biaya)
    {
        // Validasi data input
        $request->validate([
            'batas_bawah' => 'required|numeric',
            'batas_atas' => 'required|numeric|gt:batas_bawah',
            'tarif' => 'required|numeric',
        ], [
            'batas_bawah.required' => 'Batas Bawah Wajib Diisi!',
            'batas_bawah.numeric' => 'Batas Bawah Harus Menggunakan Angka!',
            'batas_atas.required' => 'Batas Atas Wajib Diisi!',
            'batas_atas.numeric' => 'Batas Atas Harus Menggunakan Angka!',
            'batas_atas.gt' => 'Batas atas harus lebih besar dari batas bawah.',
            'tarif.required' => 'Tarif Wajib Diisi!',
            'tarif.numeric' => 'Tarif Harus Menggunakan Angka!',
        ]);

        try {
            // Update database lokal
            $data = KategoriBiayaAir::findOrFail($id_kategori_biaya);
            $data->batas_bawah = $request->batas_bawah;
            $data->batas_atas = $request->batas_atas;
            $data->tarif = $request->tarif;
            $data->update();

            return redirect('/kategoribiayaair')->with('update', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            // Hapus data dari database lokal
            $data = KategoriBiayaAir::find($id);
            if ($data) {
                $data->delete();

                return redirect('/kategoribiayaair')->with('delete', 'Data berhasil dihapus.');
            }

            return redirect('/kategoribiayaair')->with('error', 'Data tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
