<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi; 
use App\Models\Pemakaian;
use App\Models\Users;
use Carbon\Carbon;

class TransaksiController extends Controller
{

    public function index(Request $request)
    { 
        $petugasId = Auth::user()->id_users;
        $search = $request->input('search');  // Ambil input search secara umum
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $transaksi = Transaksi::with(['pemakaian.users'])
            ->whereHas('pemakaian', function ($query) use ($petugasId) {
                $query->where('petugas', $petugasId);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->orWhere('status_pembayaran', 'like', "%$search%")
                    ->orWhereHas('pemakaian.users', function ($q) use ($search) {
                        $q->where('nama', 'like', "%$search%");
                    })
                    ->orWhereHas('pemakaian.users', function ($q) use ($search) {
                        $q->where('alamat', 'like', "%$search%");
                    })
                    ->orWhereDate('tgl_pembayaran', 'like', "%$search%")
                    ->orWhere('jumlah_rp', 'like', "%$search%");
                });
            })
            ->latest() 
            ->paginate(10);  // <-- Tambah paginate di sini

        // Ubah data menjadi format yang diinginkan
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
                'foto_meteran'       => $item->pemakaian->foto_meteran ?? null,  
                'status_pembayaran'  => $item->tgl_pembayaran ? 'Lunas' : null,
                'detail_biaya'       => json_decode($item->detail_biaya),
            ];
        });

        // Gabungkan lagi dengan paginasi
        $paginated = $transaksi->toArray();
        $paginated['data'] = $data;

        // Response JSON
        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diambil',
            'data' => $paginated
        ]);
    }

    public function show($id)
    {
        $transaksi = Transaksi::with(['pemakaian.users'])->find($id);

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diambil.',
            'data' => [
                'id_transaksi' => $transaksi->id_transaksi,
                'nama_petugas' => Auth::user()->nama ?? 'Unknown',
                'nama_pelanggan' => $transaksi->pemakaian->users->nama ?? null,
                'alamat_pelanggan' => $transaksi->pemakaian && $transaksi->pemakaian->users
                    ? trim("{$transaksi->pemakaian->users->alamat}, RT {$transaksi->pemakaian->users->rt} RW {$transaksi->pemakaian->users->rw}")
                    : '-',
                'tanggal_pencatatan' => $transaksi->pemakaian->waktu_catat ?? null,
                'tanggal_pembayaran' => $transaksi->tgl_pembayaran 
                    ? Carbon::parse($transaksi->tgl_pembayaran)->format('Y-m-d H:i:s')
                    : null,
                'meter_awal' => $transaksi->pemakaian->meter_awal ?? null,
                'meter_akhir' => $transaksi->pemakaian->meter_akhir ?? null,
                'jumlah_pemakaian' => $transaksi->pemakaian->jumlah_pemakaian ?? null,
                'denda' => $transaksi->rp_denda ?? 0,
                'detail_biaya' => json_decode($transaksi->detail_biaya, true),
                'total_tagihan' => $transaksi->jumlah_rp,
                'jumlah_bayar' => $transaksi->uang_bayar,
                'kembalian' => $transaksi->kembalian
            ]
        ], 200);
    }
}
