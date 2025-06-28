@extends('layouts.master')
@section('title', 'Biaya Denda')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Biaya Denda</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Biaya Denda</h4>
                            </div>
                            <div class="d-flex align-items-center flex-wrap text-nowrap">
                                @if(!$hasMaxDenda)
                                    <a href="{{ route('BiayaDenda.create') }}"
                                        class="btn btn-primary d-flex align-items-center">
                                        <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                        <span class="ms-2">Tambah Data</span>
                                    </a>
                                @else
                                    <button class="btn btn-secondary d-flex align-items-center" disabled title="Denda maksimal sudah ditetapkan. Hapus data paling bawah terlebih dahulu untuk menambah data baru.">
                                        <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                        <span class="ms-2">Tambah Data</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>Jumlah Telat Hari</th>
                                        <th>Biaya Telat (Rp)</th>
                                        <th>AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataBiayaDenda as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->jumlah_telat }} Hari</td>
                                            <td>
                                                @if(session('denda_maksimal_id') == $data->id_biaya_denda)
                                                    Cabut Water Meter
                                                @else
                                                    Rp.{{ number_format($data->biaya_telat, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td>
                                                <!-- <a href="{{route('BiayaDenda.edit', $data->id_biaya_denda)}}" class="btn btn-warning">Edit</a> -->
                                                @if ($loop->last)
                                                    <form action="{{route('BiayaDenda.destroy', $data->id_biaya_denda)}}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-danger delete-BiayaDenda-button">Hapus</button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary" disabled>Hapus Paling Bawah</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($hasMaxDenda)
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Catatan:</strong> Tidak dapat menambah data baru karena telah ditetapkan tingkat denda maksimal. Hapus data terakhir untuk menambah data baru.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection