@extends('layouts.master')
@section('title', 'Lihat Detail Keluhan')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('keluhan.index') }}">Data Keluhan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Keluhan</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Detail Keluhan</h4>

                        <!-- Informasi Keluhan Card -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title text-primary mb-0">Informasi Keluhan</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="140" class="fw-bold">ID Keluhan</td>
                                                <td>: {{ $data->id_keluhan }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Nama Pelapor</td>
                                                <td>: {{ $data->users->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Alamat</td>
                                                <td>: {{ $data->users->alamat }}, RT: {{ $data->users->rt }}, RW:
                                                    {{ $data->users->rw }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">No. Telepon</td>
                                                <td>: {{ $data->users->no_hp }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Foto Bukti Card -->
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title text-primary mb-0">Foto Bukti Keluhan</h5>
                                    </div>
                                    <div class="card-body text-center">
                                    @if(isset($data->foto_keluhan) && !empty($data->foto_keluhan))
    @if(Str::startsWith($data->foto_keluhan, 'https://'))
        <!-- Handle external URLs (like Google Cloud Storage) -->
        <img src="{{ $data->foto_keluhan }}" alt="Foto Keluhan"
            class="img-fluid rounded" style="max-height: 250px;">
    @else
        <!-- Handle local storage files -->
        <img src="{{ asset('storage/' . $data->foto_keluhan) }}" alt="Foto Keluhan"
            class="img-fluid rounded" style="max-height: 250px;">
    @endif
@else
    <div class="alert alert-info">
        Tidak ada foto yang diunggah
    </div>
@endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Card -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title text-primary mb-0">Keterangan Keluhan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="keterangan-container p-3 bg-light rounded border">
                                            <p class="mb-0">{{ $data->keterangan }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tanggapan Card -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title text-primary mb-0">Berikan Tanggapan</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="keluhanForm" method="POST"
                                            action="{{ route('keluhan.update', $data->id_keluhan) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="users" value="{{ $data->users->id_users }}">

                                            <div class="form-group">
                                                <label for="tanggapan" class="form-label">Tanggapan</label>
                                                <textarea class="form-control @error('tanggapan') is-invalid @enderror"
                                                    id="tanggapan" name="tanggapan" rows="4" required
                                                    placeholder="Masukkan tanggapan untuk keluhan ini...">{{ old('tanggapan', $data->tanggapan ?? '') }}</textarea>
                                                @error('tanggapan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mt-4 text-end">
                                                <a href="{{ route('keluhan.index') }}" class="btn btn-secondary me-2">
                                                    <i class="feather icon-arrow-left me-1"></i> Kembali
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="feather icon-save me-1"></i> Simpan Tanggapan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection