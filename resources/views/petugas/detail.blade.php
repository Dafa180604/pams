@extends('layouts.master')
@section('title', 'Petugas')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('petugas.index') }}">Data Master Petugas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <h4 class="mb-3 mb-md-2">Detail Data Petugas {{ $data->nama }}</h4>
                        </div>

                        <div class="flex items-center space-x-4 mb-4">
                            <img id="foto_profile" class="w-16 h-16 rounded-full border-2 border-gray-300"
                                src="{{ strpos($data->foto_profile, 'storage.googleapis.com') !== false ? $data->foto_profile : asset('storage/' . $data->foto_profile) }}"
                                alt="Foto Profile"
                                onclick="openImage('{{strpos($data->foto_profile, 'storage.googleapis.com') !== false ? $data->foto_profile : asset('storage/' . $data->foto_profile) }}')">
                            <div>
                                <p><strong>Nama:</strong> {{ $data->nama }}</p>
                                <p><strong>Role:</strong> {{ $data->role }}</p>
                                <p><strong>Alamat:</strong> {{ $data->alamat }}</p>
                                <p><strong>Telepon:</strong> {{ $data->no_hp }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Filter Data</h5>
                                        <form action="{{ route('petugas.show', $data->id_users) }}" method="GET"
                                            class="mb-4">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label for="end_date" class="form-label">Tanggal Bulan</label>
                                                    <input type="month" name="end_date" id="end_date" class="form-control"
                                                        value="{{ request('end_date', date('Y-m')) }}">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-auto">
                            <table class="table-auto w-full text-left bg-white shadow-md rounded-lg">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="p-3 border-b">No</th>
                                        <th class="p-3 border-b">Nama Pelanggan</th>
                                        <th class="p-3 border-b">Meter Awal</th>
                                        <th class="p-3 border-b">Meter Akhir</th>
                                        <th class="p-3 border-b">Jumlah Pemakaian</th>
                                        <th class="p-3 border-b">Waktu Catat</th>
                                        <th class="p-3 border-b">Foto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pencatatan as $record)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-3 border-b">{{ $record->id_pemakaian}}</td>
                                            <td class="p-3 border-b">{{ $record->users->nama ?? '-' }}</td>
                                            <td class="p-3 border-b">{{ $record->meter_awal ?? '-' }}</td>
                                            <td class="p-3 border-b">{{ $record->meter_akhir ?? '-' }}</td>
                                            <td class="p-3 border-b">{{ $record->jumlah_pemakaian ?? '-' }}</td>
                                            <td class="p-3 border-b">{{ $record->waktu_catat ?? '-' }}</td>
                                            <td class="p-3 border-b">
                                                <img id="foto_meteran" class="w-16 h-16 rounded-full border-2 border-gray-300"
                                                    src="{{ strpos($record->foto_meteran, 'storage.googleapis.com') !== false ? $record->foto_meteran : asset('storage/' . $record->foto_meteran) }}"
                                                    alt="Foto Meteran"
                                                    onclick="openImage('{{strpos($record->foto_meteran, 'storage.googleapis.com') !== false ? $record->foto_meteran : asset('storage/' . $record->foto_meteran) }}')">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="p-3 text-center text-gray-500">Belum ada data pencatatan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            {{-- Pagination Links --}}
                            <div class="mt-4">
                                {{ $pencatatan->appends(request()->input())->links() }}
                            </div>
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
    <!-- Existing Image Popup Script and Style Remain the Same -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterInputs = document.querySelectorAll('input[name="end_date"]');

            filterInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection