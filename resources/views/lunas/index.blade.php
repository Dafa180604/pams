@extends('layouts.master')

@section('title', 'Data Transaksi')

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Transaksi</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <h4 class="mb-3 mb-md-2">Data Transaksi</h4>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>ID Transaksi</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Meter Awal</th>
                                        <th>Meter Akhir</th>
                                        <th>Jumlah Pemakaian</th>
                                        <th>Jumlah RP</th>
                                        <th>Tanggal Pembayaran</th>
                                        <th>Petugas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTransaksi as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->id_transaksi }}</td>
                                            <td>{{ optional($data->pemakaian->users()->withTrashed()->first())->nama ?? 'Pengguna dihapus' }}</td>
                                            <td>{{ $data->pemakaian->meter_awal }}</td>
                                            <td>{{ $data->pemakaian->meter_akhir }}</td>
                                            <td>{{ $data->pemakaian->jumlah_pemakaian }}</td>
                                            <td>{{ $data->jumlah_rp }}</td>
                                            <td>{{ $data->tgl_pembayaran }}</td>
                                            <td>{{ $data->pemakaian->petugas }}</td>
                                            <td><a href="{{ route('lunas.show', $data->id_transaksi) }}"
                                                class="btn btn-info btn-sm me-2">Detail</a></td>
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
    @if(session('cetakUrl'))
    <script>
        window.open("{{ session('cetakUrl') }}", '_blank');
    </script>
@endif
@endsection