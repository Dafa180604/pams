<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BiayaGolonganBerbayar;

class GolonganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataBiayagolonganberbayar = BiayaGolonganBerbayar::all();
        return view('BiayaGolonganBerbayar.index', ['dataBiayagolonganberbayar' => $dataBiayagolonganberbayar]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function edit(string $id_biaya_golongan)
    {
        $data = BiayaGolonganBerbayar::find($id_biaya_golongan);
        return view('BiayaGolonganBerbayar.edit', compact('data'));
    }

    public function update(Request $request, string $id_biaya_golongan)
    {
        // Validasi data input
        $request->validate([
            'tarif' => 'required|numeric',
            'keterangan' => 'required',
        ], [
            'tarif.required' => 'Tarif Wajib Diisi!',
            'tarif.numeric' => 'Tarif Wajib di Isi Angka!',
            'keterangan.required' => 'Keterangan Wajib Diisi!',
        ]);

        try {
            // Update database lokal
            $data = BiayaGolonganBerbayar::findOrFail($id_biaya_golongan);
            $data->tarif = $request->tarif;
            $data->keterangan = $request->keterangan;
            $data->update();

            return redirect('/biayagolongan')->with('update', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
