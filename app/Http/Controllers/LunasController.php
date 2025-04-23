<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class LunasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataTransaksi = Transaksi::where('status_pembayaran', '!=', 'Belum Bayar')->get();
        return view('lunas.index', ['dataTransaksi' => $dataTransaksi]);
 
    }
    public function cetak(string $id_transaksi)
    {
        $dataTransaksi = Transaksi::find($id_transaksi);
        return view('lunas.cetak', compact('dataTransaksi'));
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
    public function show(string $id_transaksi)
    {
        $dataTransaksi = Transaksi::find($id_transaksi);
        return view('lunas.detail', compact('dataTransaksi'));
   
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
