<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Carbon\Carbon;
use App\Models\Users;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m'));
        $endDate = $request->input('end_date', date('Y-m'));
       
        // Format dates for queries
        $startFormatted = $startDate . '-01';
        $lastDay = Carbon::createFromFormat('Y-m', $endDate)->endOfMonth()->format('d');
        $endFormatted = $endDate . '-' . $lastDay;
       
        // Calculate previous balance (saldo awal) before the start date
        $previousTransactions = Laporan::where('tanggal', '<', $startFormatted)
            ->orderBy('tanggal', 'asc')
            ->get();
           
        $previousSaldo = 0;
        foreach ($previousTransactions as $prev) {
            $previousSaldo += $prev->uang_masuk - $prev->uang_keluar;
        }
       
        // Get filtered data
        $dataLaporan = Laporan::whereBetween('tanggal', [$startFormatted, $endFormatted])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_laporan', 'asc') // Add secondary ordering to ensure consistent results
            ->get();
        
        // Get all users to replace petugas IDs with names
        // $users = Users::pluck('nama', 'id_users')->toArray();
        $users = Users::withTrashed()->pluck('nama', 'id_users')->toArray();

        // Process keterangan field to replace petugas IDs with names
        foreach ($dataLaporan as $data) {
            // For "Terima bayar [Month] [Year] oleh petugas [ID]"
            $data->keterangan = preg_replace_callback('/oleh petugas (\d+)/', function($matches) use ($users) {
                $petugasId = $matches[1];
                $petugasName = isset($users[$petugasId]) ? $users[$petugasId] : $petugasId;
                return "oleh petugas " . $petugasName;
            }, $data->keterangan);
            
            // For "Terima bayar [Month] [Year] oleh admin [ID]"
            $data->keterangan = preg_replace_callback('/oleh admin (\d+)/', function($matches) use ($users) {
                $adminId = $matches[1];
                $adminName = isset($users[$adminId]) ? $users[$adminId] : $adminId;
                return "oleh admin " . $adminName;
            }, $data->keterangan);
        }
       
        return view('laporan.index', compact('dataLaporan', 'previousSaldo', 'startDate'));
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
