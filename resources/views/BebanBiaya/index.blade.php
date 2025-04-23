@extends('layouts.master')
@section('title', 'Beban Biaya')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Data Master Beban Biaya</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                        <div>
                            <h4 class="mb-3 mb-md-2">Data Beban Biaya</h4>
                        </div>
                        <div class="d-flex align-items-center flex-wrap text-nowrap">
                            <!-- <a href="{{ route('bebanbiaya.create') }}" class="btn btn-primary d-flex align-items-center">
                                <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                <span class="ms-2">Tambah Data</span>
                            </a> -->
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
                                @foreach($dataBebanBiaya as $data)
                                    <tr>

                                        <td>{{$loop->iteration}}</td>
                                        <td>Rp {{ number_format($data->tarif, 0, ',', '.') }}</td>
                                        <td>{{ $data->keterangan }}</td>
                                        <td>
                                            <a href="{{route('bebanbiaya.edit', $data->id_beban_biaya)}}" class="btn btn-warning">Edit</a>
                                            <!-- <form action="{{route('bebanbiaya.destroy', $data->id_beban_biaya)}}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                            </form> -->
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