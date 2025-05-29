@extends('layouts.master')

@section('title', 'Data Keluhan')

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a>Data Master
                        Detail</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <h4 class="mb-3 mb-md-2">
                                Data users Keluhan
                            </h4>
                            <div class="d-flex align-items-center flex-wrap text-nowrap">
                                    <!-- <a href="{{ route('keluhan.create') }}" class="btn btn-primary d-flex align-items-center">
                                        <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                        <span class="ms-2">Tambah Data</span>
                                    </a> -->
                                </div>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pelapor</th>
                                        <!-- <th>Keterangan</th> -->
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Foto Keluhan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataKeluhan as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            @php
                                                $user = $data->users ?? \App\Models\Users::withTrashed()->find($data->id_users ?? $data->id_users);
                                            @endphp
                                            <td>{{ $user->nama ?? '-' }}</td>
                                            <!-- <td>{{ $data->keterangan }}</td> -->
                                            <td>
                                                @php
                                                    $badgeClass = match ($data->status) {
                                                        'Terkirim' => 'badge bg-danger',
                                                        'Dibaca' => 'badge bg-warning text-dark',
                                                        'Diproses' => 'badge bg-success',
                                                        default => 'badge bg-secondary',
                                                    };

                                                    // Ubah tampilan teks jika status adalah "Terkirim"
                                                    $statusText = $data->status === 'Terkirim' ? 'Pesan Masuk' : $data->status;
                                                @endphp
                                                <span class="{{ $badgeClass }}">{{ $statusText }}</span>
                                            </td>
                                            <td>{{ $data->tanggal }}</td>
                                            <td>
                                                <img id="foto_keluhan" class="w-32 h-32 rounded-full border-2 border-gray-300"
                                                    src="{{ strpos($data->foto_keluhan, 'storage.googleapis.com') !== false ? $data->foto_keluhan : asset('storage/' . $data->foto_keluhan) }}"
                                                    alt="Foto Meteran"
                                                    onclick="openImage('{{strpos($data->foto_keluhan, 'storage.googleapis.com') !== false ? $data->foto_keluhan : asset('storage/' . $data->foto_keluhan) }}')">
                                            </td>
                                            <td>
                                                <a href="{{route('keluhan.edit', $data->id_keluhan)}}"
                                                    class="btn btn-info">Detail</a>

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

    <!-- Popup Image Modal -->
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