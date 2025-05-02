<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;

class TransaksiController extends Controller
{

    public function index(Request $request)
    { 
        $petugasId = Auth::user()->id_users;
        $search = $request->input('status_pembayaran');
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $transaksi = Transaksi::with(['pemakaian.users'])
            ->whereHas('pemakaian', function ($query) use ($petugasId) {
                $query->where('petugas', $petugasId);
            })
            ->when($search, function ($query, $search) {
                return $query->where('status_pembayaran', 'like', "%$search%");
            })
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Ambil hanya field yang diinginkan
        $data = $transaksi->getCollection()->map(function ($item) {
            return [
                'id_transaksi'       => $item->id_transaksi,
                'id_pemakaian'       => $item->pemakaian->id_pemakaian ?? null,
                'id_pelanggan'       => $item->pemakaian->users->id_users ?? null,
                'nama_pelanggan'     => $item->pemakaian->users->nama ?? '-',
                'alamat_pelanggan' => $item->pemakaian && $item->pemakaian->users
                ? trim("{$item->pemakaian->users->alamat}, RT {$item->pemakaian->users->rt} RW {$item->pemakaian->users->rw}")
                : '-',

                'tanggal_pencatatan' => $item->pemakaian->waktu_catat ?? null,
                'tanggal_pembayaran' => $item->tgl_pembayaran,
                'meter_awal'         => $item->pemakaian->meter_awal ?? null,
                'meter_akhir'        => $item->pemakaian->meter_akhir ?? null,
                'jumlah_pemakaian'   => $item->pemakaian->jumlah_pemakaian ?? null,
                'denda'              => $item->rp_denda,
                'total_tagihan'      => $item->jumlah_rp,
                'foto_meteran'      => $item->meteran->foto_meteran??null,  
            ];
        });

        // Gabungkan dengan informasi paginasi
        $paginated = $transaksi->toArray();
        $paginated['data'] = $data;

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diambil',
            'data' => $paginated
        ]);
    }
}
