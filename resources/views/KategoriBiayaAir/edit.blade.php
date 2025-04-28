@extends('layouts.master')
@section('title', 'Kategori Biaya Air')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('kategoribiayaair.index') }}">Data Master Kategori Biaya
                    Air</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <!-- pasti -->
    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fs-4 mb-4">Form Edit Data Kategori Biaya
                        Air</h4>
                    <form action="{{ route('kategoribiayaair.update', $data->id_kategori_biaya) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Batas Bawah</label>
                                    <input type="text" name="batas_bawah" class="form-control"
                                        value="{{ old('batas_bawah', $data->batas_bawah) }}"
                                        placeholder="Masukkan Batas Bawah">
                                    @error('batas_bawah')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Tarif</label>
                                    <input type="text" name="tarif" class="form-control"
                                        value="{{ old('tarif', $data->tarif) }}" placeholder="Masukkan Tarif">
                                    @error('tarif')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Batas Atas</label>
                                    <input type="text" name="batas_atas" class="form-control"
                                        value="{{ old('batas_atas', $data->batas_atas) }}"
                                        placeholder="Masukkan Batas Atas">
                                    @error('batas_atas')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
                        </div>

                        <a href="{{ route('kategoribiayaair.index') }}" type="reset" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Perbarui</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection