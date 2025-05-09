@extends('layouts.master')
@section('title', 'Pengeluaran')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Data Master Pengeluaran</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                        <div>
                            <h4 class="mb-3 mb-md-2">Data Pengeluaran</h4>
                        </div>
                        <div class="d-flex align-items-center flex-wrap text-nowrap">
                            <a href="{{ route('pengeluaran.create') }}" class="btn btn-primary d-flex align-items-center">
                                <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                <span class="ms-2">Tambah Data</span>
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>NO.</th>
                                    <th>TARIF</th>
                                    <th>KETERANGAN</th>
                                    <th>TANGGAL</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($datapengeluaran as $data)
                                    <tr>

                                        <td>{{$loop->iteration}}</td>
                                        <td>Rp {{ number_format($data->uang_keluar, 0, ',', '.') }}</td>
                                        <td>{{ $data->keterangan }}</td>
                                        <td>{{ $data->tanggal }}</td>
                                        <td>
                                            <a href="{{ route('pengeluaran.edit',$data->id_laporan) }}" class="btn btn-warning">Edit</a>
                                            <form action="{{route('pengeluaran.destroy', $data->id_laporan)}}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger delete-kategoribiayaair-button">Hapus</button>
                                            </form>
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