@extends('layouts.master')
@section('title', 'Edit Pencatatan Meter')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pemakaian.index') }}">Data Transaksi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Pencatatan Meter</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title">Edit Pencatatan Meter Pelanggan</h4>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-indigo-600 text-white">
                                        <h5 class="mb-0 fw-semibold"><i class="mdi mdi-account-outline me-2"></i>Informasi
                                            Pelanggan</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="140">ID Pelanggan</td>
                                                <td>: {{ $user->id_users }}</td>
                                            </tr>
                                            <tr>
                                                <td>Nama</td>
                                                <td>: {{ $user->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td>Alamat</td>
                                                <td>: {{ $user->alamat }}, RT: {{ $user->rt }}, RW: {{ $user->rw }}</td>
                                            </tr>
                                            <tr>
                                                <td>No. Telepon</td>
                                                <td>: {{ $user->no_hp }}</td>
                                            </tr>
                                            <tr>
                                                <td>Golongan</td>
                                                <td>: {{ $user->golongan }}</td>
                                            </tr>
                                            <tr>
                                                <td>Petugas</td>
                                                <td>: {{ $pemakaian->petugas }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-indigo-600 text-white">
                                        <h5 class="mb-0 fw-semibold"><i class="mdi mdi-cash-multiple me-2"></i>Informasi
                                            Transaksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <span class="text-muted">ID Transaksi:</span>
                                                    <span class="fw-bold">{{ $transaksi->id_transaksi }}</span>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="text-muted">Status Pembayaran:</span>
                                                    <span class="fw-bold">
                                                        @if($transaksi->status_pembayaran == 'Lunas')
                                                            <span
                                                                class="badge bg-success">{{ $transaksi->status_pembayaran }}</span>
                                                        @else
                                                            <span
                                                                class="badge bg-warning">{{ $transaksi->status_pembayaran }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <span class="text-muted">Jumlah Tagihan:</span>
                                                    <span class="fw-bold">Rp
                                                        {{ number_format($transaksi->jumlah_rp, 0, ',', '.') }}</span>
                                                </div>
                                                @if($transaksi->status_pembayaran == 'Lunas')
                                                    <div class="mb-2">
                                                        <span class="text-muted">Tanggal Bayar:</span>
                                                        <span
                                                            class="fw-bold">{{ \Carbon\Carbon::parse($transaksi->tgl_pembayaran)->format('d/m/Y') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mt-3">
                                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                                            <strong>Perhatian!</strong> Mengubah pencatatan meter akan memperbarui semua
                                            data terkait termasuk tagihan dan laporan keuangan.
                                        </div>
                                    </div>
                                </div>
                                @if($pemakaian->foto_meteran)
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Foto Meteran Saat Ini</label>
                                                <div class="border rounded p-2 text-center">
                                                    <img src="{{ $pemakaian->foto_meteran }}" alt="Foto Meteran"
                                                        class="img-fluid rounded" style="max-height: 150px; cursor: zoom-in;"
                                                        onclick="openImage('{{ $pemakaian->foto_meteran }}')">
                                                    <div class="mt-2 text-muted small">Klik gambar untuk memperbesar</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('EditSalahCatat.update', $pemakaian->id_pemakaian) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="card mb-4">
                                <div class="card-header bg-indigo-600 text-white">
                                    <h5 class="mb-0 fw-semibold"><i class="mdi mdi-pencil-box-outline me-2"></i>Data
                                        Pencatatan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="meter_awal" class="form-label">Meter Awal (m³)</label>
                                                <input type="number" step="0.01"
                                                    class="form-control @error('meter_awal') is-invalid @enderror"
                                                    id="meter_awal" name="meter_awal"
                                                    value="{{ old('meter_awal', $pemakaian->meter_awal) }}" required>
                                                @error('meter_awal')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="meter_akhir" class="form-label">Meter Akhir (m³)</label>
                                                <input type="number" step="0.01"
                                                    class="form-control @error('meter_akhir') is-invalid @enderror"
                                                    id="meter_akhir" name="meter_akhir"
                                                    value="{{ old('meter_akhir', $pemakaian->meter_akhir) }}" required>
                                                @error('meter_akhir')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Pemakaian (m³)</label>
                                                <input type="text" id="pemakaian_preview" class="form-control"
                                                    value="{{ $pemakaian->jumlah_pemakaian }} m³" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">
                                        <i class="mdi mdi-close me-1"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
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

        // Close popup when clicking outside the image
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('imagePopup').addEventListener('click', function(event) {
                if (event.target === this) {
                    closeImage();
                }
            });
        });
    </script>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const meterAwalInput = document.getElementById('meter_awal');
            const meterAkhirInput = document.getElementById('meter_akhir');
            const pemakaianPreview = document.getElementById('pemakaian_preview');

            // Calculate and display pemakaian when inputs change
            function calculatePemakaian() {
                const meterAwal = parseFloat(meterAwalInput.value) || 0;
                const meterAkhir = parseFloat(meterAkhirInput.value) || 0;
                const pemakaian = meterAkhir - meterAwal;

                pemakaianPreview.value = pemakaian >= 0 ? pemakaian + ' m³' : 'Error: Meter akhir lebih kecil';

                // Highlight error
                if (pemakaian < 0) {
                    pemakaianPreview.classList.add('is-invalid');
                    pemakaianPreview.classList.add('text-danger');
                } else {
                    pemakaianPreview.classList.remove('is-invalid');
                    pemakaianPreview.classList.remove('text-danger');
                }
            }

            meterAwalInput.addEventListener('input', calculatePemakaian);
            meterAkhirInput.addEventListener('input', calculatePemakaian);

            // Form validation before submit
            document.querySelector('form').addEventListener('submit', function (event) {
                const meterAwal = parseFloat(meterAwalInput.value) || 0;
                const meterAkhir = parseFloat(meterAkhirInput.value) || 0;

                if (meterAkhir < meterAwal) {
                    event.preventDefault();
                    alert('Meter akhir tidak boleh lebih kecil dari meter awal!');
                    return false;
                }
            });
        });
    </script>
@endpush