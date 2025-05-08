@extends('layouts.master')
@section('title', 'Biaya Denda')
@section('content')
    <div class="page-content">

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('BiayaDenda.index') }}">Data Master Kategori Biaya
                        Air</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <!-- pasti -->
        <div class="row">
            <div class="col-md-12 stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title fs-4 mb-4">Form Edit Data Biaya Denda
                            Air</h4>
                        <form action="{{ route('BiayaDenda.update', $data->id_biaya_denda) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Telat</label>
                                        <input type="text" name="jumlah_telat" class="form-control"
                                        value="{{ old('jumlah_telat', $data->jumlah_telat) }}"
                                            placeholder="Masukkan Jumlah Telat">
                                        @error('jumlah_telat')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Biaya Telat (Rp)</label>
                                        <input type="text" name="biaya_telat" class="form-control"
                                        value="{{ old('biaya_telat', $data->biaya_telat) }}"
                                            placeholder="Masukkan Biaya Telat (%)">
                                        @error('biaya_telat')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div><!-- Col --><!-- Col -->
                            </div>
                            <a href="{{ route('BiayaDenda.index') }}" type="reset" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Perbarui</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection