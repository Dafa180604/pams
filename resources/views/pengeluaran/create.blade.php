@extends('layouts.master')
@section('title', 'Pengeluaran')
@section('content')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('pengeluaran.index') }}">Data Master Pengeluaran</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
        </ol>
    </nav>
    <!-- pasti -->
    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fs-4 mb-4">Form Tambah Pengeluaran</h4>
                    <form action="{{ route('pengeluaran.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Tarif</label>
                                    <input type="text" name="uang_keluar" class="form-control" placeholder="Masukkan Tarif"value="{{ old('uang_keluar') }}">
                                    @error('uang_keluar')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea type="text" name="keterangan" class="form-control"
                                        placeholder="Masukkan Keterangan">{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div><!-- Col -->
                        </div>

                        <a href="{{ route('pengeluaran.index') }}" type="reset" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Tambahkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection