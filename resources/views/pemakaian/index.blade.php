@extends('layouts.master')
@section('title', 'Pelanggan')
@section('content')
    <div class="page-content">

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Pelanggan</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Pelanggan</h4>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>id_users</th>
                                        <th>NAMA</th>
                                        <th>ALAMAT</th>
                                        <th>TELEPON</th>
                                        <th>GOLONGAN</th>
                                        <th>JUMLAH METERAN</th>
                                        <th>AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataPelanggan as $pelanggan)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{ $pelanggan->id_users }}</td>
                                            <td>{{ $pelanggan->nama }}</td>
                                            <td>
                                                <div>{{ $pelanggan->alamat }}</div>
                                                <div style="font-size: 0.9em; color: #666;">
                                                    <span>RT: {{ $pelanggan->rt }}</span> | <span>RW:
                                                        {{ $pelanggan->rw }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $pelanggan->no_hp }}</td>
                                            <td>{{ $pelanggan->golongan }}</td>
                                            <td>{{ $pelanggan->meter_akhir }}</td>
                                            <td>
                                                <a href="{{ route('pemakaian.edit', $pelanggan->id_users) }}" class="btn btn-primary">catat</a>
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