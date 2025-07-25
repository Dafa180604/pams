@extends('layouts.master')
@section('title', 'Pelanggan')
@section('content')
    <div class="page-content">

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Transaksi</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Belum Lunas</h4>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>ID PEMAKAIAN</th>
                                        <th>NAMA PELANGGAN</th>
                                        <th>METER AWAL</th>
                                        <th>METER AKHIR</th>
                                        <th>JUMLAH PEMAKAIAN</th>
                                        <th>JUMLAH RP</th>
                                        <th>TANGGAL PENCATATAN</th>
                                        <th>TELAT PEMBAYARAN</th>
                                        <th>PETUGAS</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTransaksi as $data)
                                        @php
                                            // Hitung selisih hari antara hari ini dengan waktu_catat
                                            $waktuCatat = \Carbon\Carbon::parse($data->pemakaian->waktu_catat);
                                            $hariSekarang = \Carbon\Carbon::now();
                                            $telatHari = floor($waktuCatat->diffInDays($hariSekarang));
                                        @endphp
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{ $data->id_pemakaian}}</td>
                                            <td>{{ optional($data->pemakaian->users()->withTrashed()->first())->nama ?? 'Pengguna dihapus' }}
                                            </td>
                                            <td>{{ $data->pemakaian->meter_awal}}</td>
                                            <td>{{ $data->pemakaian->meter_akhir}}</td>
                                            <td>{{ $data->pemakaian->jumlah_pemakaian}}</td>
                                            <td>{{ number_format($data->jumlah_rp, 0, ',', '.') }}</td>
                                            <td>{{ $data->pemakaian->waktu_catat}}</td>
                                            <td>
                                                @if($telatHari > 0)
                                                    <span class="text-danger fw-bold">{{ $telatHari }} hari terlambat</span>
                                                @else
                                                    <span class="text-success fw-bold">Tepat waktu</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($petugasUsers[$data->pemakaian->petugas]))
                                                    {{ $petugasUsers[$data->pemakaian->petugas]->nama }}
                                                @else
                                                    @php
                                                        $petugasId = $data->pemakaian->petugas;
                                                        $user = DB::table('users')->where('id_users', $petugasId)
                                                            ->orWhere('id_users', $petugasId)
                                                            ->first();
                                                        echo $user ? $user->nama : $petugasId;
                                                    @endphp
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('belumlunas.edit', $data->id_transaksi) }}"
                                                    class="btn btn-success btn-sm">Bayar</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection