@extends('layouts.master')
@section('title', 'Kategori Biaya Air')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Kategori Biaya Air</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Kategori Biaya Air</h4>
                            </div>
                            <div class="d-flex align-items-center flex-wrap text-nowrap">
                                <a href="{{ route('kategoribiayaair.create') }}"
                                    class="btn btn-primary d-flex align-items-center">
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
                                        <th>BATAS BAWAH - ATAS PENGGUNAAN AIR/M³</th>
                                        <th>TARIF</th>
                                        <th>AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataKategoriBiayaAir as $data)
                                        <tr>

                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->batas_bawah }} - {{ $data->batas_atas }} /M³</td>
                                            <td>Rp{{ number_format($data->tarif, 0, ',', '.') }}</td>
                                            <td>
                                                <!-- <a href="{{route('kategoribiayaair.edit', $data->id_kategori_biaya)}}" class="btn btn-warning">Edit</a> -->
                                                @if ($loop->last)
                                                    <form action="{{ route('kategoribiayaair.destroy', $data->id_kategori_biaya) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-danger delete-kategoribiayaair-button">Hapus</button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary" disabled>Hapus Paling Bawah</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>NB:</strong> Data dengan ID terakhir memiliki batas atas yang berarti nilai tersebut berlaku untuk pemakaian sampai seterusnya.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection