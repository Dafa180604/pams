<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $datapengeluaran = Laporan::whereNotNull('uang_keluar')->orderBy('created_at', 'desc')
        ->get();
        return view('pengeluaran.index', ['datapengeluaran' => $datapengeluaran]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pengeluaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'uang_keluar' => 'required|numeric',
            'keterangan' => 'required',
        ], [
            'uang_keluar.required' => 'Tarif Wajib Diisi!',
            'uang_keluar.numeric' => 'Tarif Wajib di Isi Angka!',
            'keterangan.required' => 'Keterangan Wajib Diisi!',
        ]);

        try {
            // Logika untuk menentukan id yang kosong
            $id = 1;
            $existingIds = Laporan::pluck('id_laporan')->sort()->toArray();

            foreach ($existingIds as $existingId) {
                if ($id != $existingId) {
                    break;
                }
                $id++;
            }

            // Membuat data kategori biaya air baru di database lokal
            $data = new Laporan();
            $data->id_laporan = $id;
            $data->keterangan = $request->keterangan;
            $data->uang_keluar = $request->uang_keluar;
            $data->tanggal = now();
            $data->save();

            return redirect('/pengeluaran')->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
    public function edit(string $id_laporan)
    {
        $data = Laporan::find($id_laporan);
        return view('pengeluaran.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id_laporan)
    {
        // Validasi data input
        $request->validate([
            'uang_keluar' => 'required|numeric',
            'keterangan' => 'required',
        ], [
            'uang_keluar.required' => 'Tarif Wajib Diisi!',
            'uang_keluar.numeric' => 'Tarif Wajib di Isi Angka!',
            'keterangan.required' => 'Keterangan Wajib Diisi!',
        ]);

        try {
            // Update database lokal
            $data = Laporan::findOrFail($id_laporan);
            $data->uang_keluar = $request->uang_keluar;
            $data->keterangan = $request->keterangan;
            $data->tanggal = now();
            $data->update();

            return redirect('/pengeluaran')->with('update', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Hapus data dari database lokal
            $data = Laporan::find($id);
            if ($data) {
                $data->delete();

                return redirect('/pengeluaran')->with('delete', 'Data berhasil dihapus.');
            }

            return redirect('/pengeluaran')->with('error', 'Data tidak ditemukan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
