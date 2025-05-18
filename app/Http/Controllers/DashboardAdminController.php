<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentYear = Carbon::now()->year;
        $years = range($currentYear - 3, $currentYear);
        
        // Count customers (users with role 'pelanggan')
        $customerCount = DB::table('users')
            ->where('role', 'pelanggan')
            ->whereNull('deleted_at')
            ->count();
            
        // Count staff (users with role 'petugas')
        $staffCount = DB::table('users')
            ->where('role', 'petugas')
            ->whereNull('deleted_at')
            ->count();
            
        // Count all transactions
        $transactionCount = DB::table('transaksi')
            ->count();
            
        // Count paid transactions
        $paidTransactions = DB::table('transaksi')
            ->where('status_pembayaran', 'Lunas')
            ->count();
            
        // Count unpaid transactions
        $unpaidTransactions = DB::table('transaksi')
            ->where('status_pembayaran', 'Belum Bayar')
            ->count();

        return view('Dashboard.admin', compact('years', 'customerCount', 'staffCount', 'transactionCount', 'paidTransactions', 'unpaidTransactions'));
    }

    public function getLaporanData(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        // Initialize arrays for all months
        $pemasukan = array_fill(0, 12, 0);
        $pengeluaran = array_fill(0, 12, 0);

        // Get data from database
        $laporanData = DB::table('laporan')
            ->whereYear('tanggal', $year)
            ->select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('SUM(IFNULL(uang_masuk, 0)) as total_masuk'),
                DB::raw('SUM(IFNULL(uang_keluar, 0)) as total_keluar')
            )
            ->groupBy('month')
            ->get();

        // Fill data arrays
        foreach ($laporanData as $data) {
            $monthIndex = $data->month - 1; // Convert to 0-based index
            $pemasukan[$monthIndex] = (int) $data->total_masuk;
            $pengeluaran[$monthIndex] = (int) $data->total_keluar;
        }

        // Calculate totals
        $totalPemasukan = array_sum($pemasukan);
        $totalPengeluaran = array_sum($pengeluaran);
        $balance = $totalPemasukan - $totalPengeluaran;

        return response()->json([
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'balance' => $balance
        ]);
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