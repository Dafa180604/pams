@extends('layouts.master')
@section('title', 'Biaya Golongan Berbayar')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Data Master Biaya Golongan Berbayar</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                        <div>
                            <h4 class="mb-3 mb-md-2">Data Biaya Golongan Berbayar</h4>
                        </div>
                        <div class="d-flex align-items-center flex-wrap text-nowrap">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>NO.</th>
                                    <th>TARIF</th>
                                    <th>KETERANGAN</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataBiayagolonganberbayar as $data)
                                    <tr>

                                        <td>{{$loop->iteration}}</td>
                                        <td>Rp {{ number_format($data->tarif, 0, ',', '.') }}</td>
                                        <td>{{ $data->keterangan }}</td>
                                        <td>
                                            <a href="{{route('biayagolongan.edit', $data->id_biaya_golongan)}}" class="btn btn-warning">Edit</a>
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