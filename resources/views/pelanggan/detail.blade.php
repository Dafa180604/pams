@extends('layouts.master')

@section('title', 'Data Detail Pelanggan')

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item" aria-current="page"><a href="{{ route('pelanggan.index') }}">Data Master
                        users</a>
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
                                Data users - {{ $pelanggan->nama ?? 'Nama Tidak Tersedia' }}
                            </h4>

                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>ID Transaksi</th>
                                        <th>ID Pencatatan</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Meter Awal</th>
                                        <th>Meter Akhir</th>
                                        <th>Jumlah Pemakaian</th>
                                        <th>Foto</th>
                                        <th>Jumlah RP</th>
                                        <th>Status</th>
                                        <th>Tanggal Pembayaran</th>
                                        <th>Petugas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTransaksi as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->id_transaksi }}</td>
                                            <td>{{ $data->id_pemakaian }}</td>
                                            <td>{{ $data->pemakaian->users->nama }}</td>
                                            <td>{{ $data->pemakaian->meter_awal }}</td>
                                            <td>{{ $data->pemakaian->meter_akhir }}</td>
                                            <td>{{ $data->pemakaian->jumlah_pemakaian }}</td>
                                            <td>
                                                <img id="foto_meteran" class="w-32 h-32 rounded-full border-2 border-gray-300"
                                                    src="{{ strpos($data->pemakaian->foto_meteran, 'storage.googleapis.com') !== false ? $data->pemakaian->foto_meteran : asset('storage/' . $data->pemakaian->foto_meteran) }}"
                                                    alt="Foto Meteran"
                                                    onclick="openImage('{{strpos($data->pemakaian->foto_meteran, 'storage.googleapis.com') !== false ? $data->pemakaian->foto_meteran : asset('storage/' . $data->pemakaian->foto_meteran) }}')">
                                            </td>
                                            <td>{{ $data->jumlah_rp }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $data->status_pembayaran == 'Lunas' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $data->status_pembayaran }}
                                                </span>
                                            </td>
                                            <td>{{ $data->tgl_pembayaran }}</td>
                                            <td>
    @if($data->pemakaian->petugas)
        @php
            $petugasIdArray = explode(',', $data->pemakaian->petugas);
            $petugasNames = [];
            foreach($petugasIdArray as $petugasId) {
                $petugasId = trim($petugasId);
                if(isset($petugasUsers[$petugasId])) {
                    $petugasNames[] = $petugasUsers[$petugasId]->nama;
                } else {
                    $petugasNames[] = $petugasId;
                }
            }
            echo implode(', ', $petugasNames);
        @endphp
    @else
        -
    @endif
</td>
                                            <td>
                                                <a href="{{ route('lunas.show', $data->id_transaksi) }}"
                                                    class="btn btn-info">Detail</a>
                                                @php
                                                    $idTerakhir = $dataTransaksi->max('id_transaksi');
                                                @endphp

                                                @if ($data->id_transaksi == $idTerakhir)
                                                    <a href="{{ route('EditSalahCatat.edit', $data->id_transaksi) }}"
                                                        class="btn btn-warning">Edit</a>
                                                @endif

                                                <!-- @if($data->status_pembayaran == 'Lunas')
                                                                                                <a href="{{ route('lunas.cetak', $data->id_transaksi) }}"
                                                                                                    class="btn btn-success" target="_blank">Cetak</a>
                                                                                            @else
                                                                                                <a href="{{ route('lunas.edit', $data->id_transaksi) }}"
                                                                                                    class="btn btn-warning">Bayar</a>
                                                                                            @endif -->
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