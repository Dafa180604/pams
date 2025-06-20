<?php
namespace App\Http\Controllers;

use App\Models\Laporan;
use Carbon\Carbon;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m'));
        $endDate = $request->input('end_date', date('Y-m'));
        $statusPenerimaan = $request->input('status_penerimaan', 'all');
       
        // Alternative approach using raw SQL
        $startYear = substr($startDate, 0, 4);
        $startMonth = substr($startDate, 5, 2);
        $endYear = substr($endDate, 0, 4);
        $endMonth = substr($endDate, 5, 2);
        
        // Build query
        $query = Laporan::query();
        
        // Date filtering
        if($startDate === $endDate) {
            // Same month filter
            $query->whereYear('tanggal', $startYear)
                  ->whereMonth('tanggal', $startMonth);
        } else {
            // Different months - use date range
            $startFormatted = $startDate . '-01';
            $endFormatted = Carbon::createFromFormat('Y-m', $endDate)->endOfMonth()->format('Y-m-d');
            
            $query->whereRaw("DATE(tanggal) >= ?", [$startFormatted])
                  ->whereRaw("DATE(tanggal) <= ?", [$endFormatted]);
        }
        
        // Status penerimaan filter (hanya untuk entri "Terima bayar" oleh petugas)
        if($statusPenerimaan !== 'all') {
            if($statusPenerimaan === 'diterima') {
                $query->where(function($q) {
                    // Hanya untuk "Terima bayar" oleh petugas yang sudah diterima
                    $q->where('keterangan', 'like', '%Terima bayar%')
                      ->where('keterangan', 'like', '%oleh petugas%')
                      ->where('keterangan', 'like', '%diterima%');
                })->orWhere(function($q) {
                    // Atau semua transaksi lain yang bukan "Terima bayar" oleh petugas
                    $q->where(function($subQ) {
                        $subQ->where('keterangan', 'not like', '%Terima bayar%')
                             ->orWhere('keterangan', 'not like', '%oleh petugas%');
                    });
                });
            } elseif($statusPenerimaan === 'belum_diterima') {
                $query->where(function($q) {
                    // Hanya untuk "Terima bayar" oleh petugas yang belum diterima
                    $q->where('keterangan', 'like', '%Terima bayar%')
                      ->where('keterangan', 'like', '%oleh petugas%')
                      ->where(function($subQ) {
                          $subQ->where('keterangan', 'not like', '%diterima%')
                               ->orWhere('keterangan', 'like', '%belum diterima%');
                      });
                });
            }
        }
        
        $dataLaporan = $query->orderBy('tanggal', 'asc')
                            ->orderBy('id_laporan', 'asc')
                            ->get();
        
        // Calculate previous balance using same method
        $previousTransactions = Laporan::whereRaw("DATE(tanggal) < ?", [$startDate . '-01'])
            ->orderBy('tanggal', 'asc')
            ->get();
           
        $previousSaldo = 0;
        foreach ($previousTransactions as $prev) {
            $previousSaldo += $prev->uang_masuk - $prev->uang_keluar;
        }
        
        // Get all users to replace petugas IDs with names
        $users = Users::withTrashed()->pluck('nama', 'id_users')->toArray();
        
        // Process keterangan field to replace petugas IDs with names
        foreach ($dataLaporan as $data) {
            // Replace petugas/admin IDs with names
            $data->keterangan = preg_replace_callback('/oleh petugas (\d+)/', function($matches) use ($users) {
                $petugasId = $matches[1];
                $petugasName = isset($users[$petugasId]) ? $users[$petugasId] : $petugasId;
                return "oleh petugas " . $petugasName;
            }, $data->keterangan);
            
            $data->keterangan = preg_replace_callback('/oleh admin (\d+)/', function($matches) use ($users) {
                $adminId = $matches[1];
                $adminName = isset($users[$adminId]) ? $users[$adminId] : $adminId;
                return "oleh admin " . $adminName;
            }, $data->keterangan);
            
            // Add default status if not exists for "Terima bayar" entries by petugas only
            if (strpos($data->keterangan, 'Terima bayar') !== false && 
                strpos($data->keterangan, 'oleh petugas') !== false) {
                // Jika belum ada status, tambahkan ", belum diterima"
                if (strpos($data->keterangan, 'diterima') === false && 
                    strpos($data->keterangan, 'belum diterima') === false) {
                    $data->keterangan .= ', belum diterima';
                }
            }
        }
       
        return view('laporan.index', compact('dataLaporan', 'previousSaldo', 'startDate', 'statusPenerimaan'));
    }
    
   public function updateStatusPenerimaan(Request $request, $id)
{
    try {
        $laporan = Laporan::findOrFail($id);
        
        // Validasi bahwa ini adalah entri "Terima bayar" oleh petugas
        if (strpos($laporan->keterangan, 'Terima bayar') === false || 
            strpos($laporan->keterangan, 'oleh petugas') === false) {
            return response()->json([
                'success' => false,
                'error' => 'Status penerimaan hanya dapat diubah untuk entri Terima bayar oleh petugas'
            ], 400);
        }
        
        // Update keterangan
        $keterangan = preg_replace('/, (diterima|belum diterima)$/', '', $laporan->keterangan);
        $keterangan .= $request->status_penerimaan === 'diterima' ? ', diterima' : ', belum diterima';
        
        $laporan->keterangan = $keterangan;
        $laporan->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Status penerimaan berhasil diubah',
            'data' => [
                'id_laporan' => $laporan->id_laporan,
                'keterangan' => $keterangan
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
}