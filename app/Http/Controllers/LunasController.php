<?php
namespace App\Http\Controllers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use App\Models\Users;  // Adjust namespace if needed
class LunasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataTransaksi = Transaksi::with('pemakaian')
            ->where('status_pembayaran', '!=', 'Belum Bayar')
            ->orderBy('created_at', 'desc')
            ->get();
        // Collect all petugas IDs
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
        // Get all petugas users in one query
        $petugasUsers = [];
        if ($petugasIds->isNotEmpty()) {
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }
        return view('lunas.index', [
            'dataTransaksi' => $dataTransaksi,
            'petugasUsers' => $petugasUsers
        ]);
    }
    
    public function cetak(string $id_transaksi)
    {
        $dataTransaksi = Transaksi::with('pemakaian')->find($id_transaksi);
        // Get petugas users data
        $petugasUsers = collect();
        if ($dataTransaksi && $dataTransaksi->pemakaian && $dataTransaksi->pemakaian->petugas) {
            $petugasIds = explode(',', $dataTransaksi->pemakaian->petugas);
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }
        return view('lunas.cetak', compact('dataTransaksi', 'petugasUsers'));
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
        $dataTransaksi = Transaksi::with('pemakaian')->find($id_transaksi);
        // Get petugas users data
        $petugasUsers = collect();
        if ($dataTransaksi && $dataTransaksi->pemakaian && $dataTransaksi->pemakaian->petugas) {
            $petugasIds = explode(',', $dataTransaksi->pemakaian->petugas);
            $petugasUsers = Users::whereIn('id_users', $petugasIds)->get()->keyBy('id_users');
        }
        return view('lunas.detail', compact('dataTransaksi', 'petugasUsers'));
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