@extends('layouts.master')
@section('title', 'Beban Biaya')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('bebanbiaya.index') }}">Data Master Beban Biaya</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <!-- pasti -->
    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fs-4 mb-4">Form Edit Data Beban Biaya</h4>
                    <form action="{{ route('bebanbiaya.update', $data->id_beban_biaya) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Tarif</label>
                                    <input type="text" name="tarif" class="form-control"
                                        value="{{ old('tarif', $data->tarif) }}" placeholder="Masukkan Tarif">
                                    @error('tarif')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea type="text" name="keterangan" class="form-control"
                                        placeholder="Masukkan Keterangan">{{ old('keterangan', $data->keterangan) }}</textarea>
                                    @error('keterangan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
                        </div>

                        <a href="{{ route('bebanbiaya.index') }}" type="reset" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Perbarui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection