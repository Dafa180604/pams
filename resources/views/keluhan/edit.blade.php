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

                        @php
                            use App\Models\Users;
                            $user = $data->users ?? Users::withTrashed()->find($data->id_users ?? $data->id_users);
                        @endphp

                        <!-- Informasi Keluhan -->
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
                                                <td>: {{ $user->nama ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Alamat</td>
                                                <td>: {{ $user->alamat ?? '-' }}, RT: {{ $user->rt ?? '-' }}, RW:
                                                    {{ $user->rw ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">No. Telepon</td>
                                                <td>: {{ $user->no_hp ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Status</td>
                                                <td>
                                                    @php
                                                        $badgeClass = match ($data->status) {
                                                            'Terkirim' => 'badge bg-danger',
                                                            'Dibaca' => 'badge bg-warning text-dark',
                                                            'Diproses' => 'badge bg-success',
                                                            default => 'badge bg-secondary',
                                                        };
                                                    @endphp
                                                    :<span class="{{ $badgeClass }}">{{ $data->status }}</span>
                                                </td>
                                            </tr>

                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Foto Bukti -->
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title text-primary mb-0">Foto Bukti Keluhan</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        @if(!empty($data->foto_keluhan))
                                            @if(Str::startsWith($data->foto_keluhan, 'https://'))
                                                <img src="{{ $data->foto_keluhan }}" alt="Foto Keluhan" class="img-fluid rounded"
                                                    style="max-height: 250px; cursor: pointer;"
                                                    onclick="openImage('{{ $data->foto_keluhan }}')">
                                            @else
                                                <img src="{{ asset('storage/' . $data->foto_keluhan) }}" alt="Foto Keluhan"
                                                    class="img-fluid rounded" style="max-height: 250px; cursor: pointer;"
                                                    onclick="openImage('{{ asset('storage/' . $data->foto_keluhan) }}')">
                                            @endif
                                        @else
                                            <div class="alert alert-info">Tidak ada foto yang diunggah</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Keluhan -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title text-primary mb-0">Keterangan Keluhan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="p-3 bg-light rounded border">
                                            <p class="mb-0">{{ $data->keterangan }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Tanggapan -->
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
                                            <input type="hidden" name="users" value="{{ $user->id_users ?? '' }}">

                                            <div class="form-group">
                                                <label for="tanggapan" class="form-label">Tanggapan</label>
                                                <textarea class="form-control @error('tanggapan') is-invalid @enderror"
                                                    id="tanggapan" name="tanggapan" rows="4" required
                                                    placeholder="Masukkan tanggapan untuk keluhan ini...">{{ old('tanggapan', $data->tanggapan ?? '') }}</textarea>
                                                @error('tanggapan')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mt-4 d-flex flex-wrap justify-content-between gap-2">
                                                <a href="{{ route('keluhan.index') }}" class="btn btn-secondary">
                                                    <i class="feather icon-arrow-left me-1"></i> Kembali
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="feather icon-save me-1"></i> Tanggapi
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
    <div id="imagePopup" class="popup-overlay" style="display: none;">
        <span class="close-btn" onclick="closeImage()">×</span>
        <img id="largeImage" src="" alt="Large Image">
    </div>

    <style>
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #largeImage {
            max-width: 90%;
            max-height: 90%;
            border: 2px solid white;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            color: white;
            cursor: pointer;
        }
    </style>

    <!-- JavaScript untuk membuka dan menutup gambar -->
    <script>
        function openImage(src) {
            // Menampilkan gambar besar
            document.getElementById('largeImage').src = src;
            document.getElementById('imagePopup').style.display = 'flex';
        }

        function closeImage() {
            // Menutup popup gambar
            document.getElementById('imagePopup').style.display = 'none';
        }
    </script>
@endsection