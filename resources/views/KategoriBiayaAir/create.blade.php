@extends('layouts.master')
@section('title', 'Kategori Biaya Air')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('kategoribiayaair.index') }}">Data Master Kategori Biaya
                    Air</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
        </ol>
    </nav>
    <!-- pasti -->
    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fs-4 mb-4">Form Data Tambah Kategori Biaya Air</h4>
                    <form action="{{ route('kategoribiayaair.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Batas Bawah</label>
                                    <input type="text" name="batas_bawah" class="form-control"
                                        placeholder="Masukkan Batas Bawah" value="{{ $last_meter_akhir ?? 0 }}" readonly>
                                    @error('batas_bawah')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                <label class="form-label">Tarif</label>
                                    <input type="text" name="tarif" class="form-control"
                                        placeholder="Masukkan Tarif">
                                    @error('tarif')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col --><!-- Col -->
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Batas Atas</label>
                                    <input type="text" name="batas_atas" class="form-control"
                                        placeholder="Masukkan Batas Atas">
                                    @error('batas_atas')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
                        </div>

                        <a href="{{ route('kategoribiayaair.index') }}" type="reset" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Tambahkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection