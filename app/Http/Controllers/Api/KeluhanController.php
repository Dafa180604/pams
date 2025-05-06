<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keluhan;

class KeluhanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $keluhan = Keluhan::with('users')
            ->when($search, function ($query, $search) {
                return $query->where('keterangan', 'like', "%$search%")
                            ->orWhere('tanggapan', 'like', "%$search%");
            })
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage);

        // Ambil hanya field yang diinginkan
        $data = $keluhan->getCollection()->map(function ($item) {
            return [
                'id_keluhan'   => $item->id_keluhan,
                'id_users'     => $item->id_users,
                'nama_pelapor' => $item->users->nama ?? '-',
                'no_hp'         => $item->users->no_hp ?? '-',
                'keterangan'   => $item->keterangan,
                'status'       => $item->status,
                'foto_keluhan' => $item->foto_keluhan,
                'tanggal'      => $item->tanggal,
                'tanggapan'    => $item->tanggapan,
            ];
        });

        // Gabungkan data dengan pagination info
        $paginated = $keluhan->toArray();
        $paginated['data'] = $data;

        return response()->json([
            'success' => true,
            'message' => 'Data keluhan berhasil diambil',
            'data' => $paginated
        ]);
    }

}
