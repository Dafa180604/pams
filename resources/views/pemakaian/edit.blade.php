<!-- Pemakaian.edit -->
@extends('layouts.master')
@section('title', 'Catat Meter Pelanggan')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pemakaian.index') }}">Data Pelanggan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Catat Meter</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Catat Meter Pelanggan</h4>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Informasi Pelanggan</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="140">ID Pelanggan</td>
                                                <td>: {{ $data->id_users }}</td>
                                            </tr>
                                            <tr>
                                                <td>Nama</td>
                                                <td>: {{ $data->nama }}</td>
                                            </tr>
                                            <tr>
                                                <td>Alamat</td>
                                                <td>: {{ $data->alamat }}, RT: {{ $data->rt }}, RW: {{ $data->rw }}</td>
                                            </tr>
                                            <tr>
                                                <td>No. Telepon</td>
                                                <td>: {{ $data->no_hp }}</td>
                                            </tr>
                                            <tr>
                                                <td>Golongan</td>
                                                <td>: {{ $data->golongan }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form id="pemakaianForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="users" value="{{ $data->id_users }}">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Meter Awal</label>
                                        <input type="text" name="meter_awal" id="meter_awal" class="form-control"
                                            placeholder="Meter Awal" readonly>
                                        @error('meter_awal')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="meter_akhir" class="form-label">Meter Akhir</label>
                                        <input type="number" class="form-control @error('meter_akhir') is-invalid @enderror"
                                            id="meter_akhir" name="meter_akhir" required>
                                        @error('meter_akhir')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="foto_meteran" class="form-label">foto_meteran Bukti Meter</label>
                                        <input type="file" class="form-control @error('foto_meteran') is-invalid @enderror"
                                            id="foto_meteran" name="foto_meteran" accept="image/*" required>
                                        @error('foto_meteran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="preview" class="form-label">Preview foto_meteran</label>
                                        <div id="preview" class="mt-2 border rounded p-2 text-center">
                                            <p class="text-muted">Gambar akan ditampilkan di sini</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('pemakaian.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="button" class="btn btn-primary" onclick="submitForm('store')">Simpan Data</button>
                                <button type="button" class="btn btn-success" onclick="submitForm('bayar')">Lanjut Bayar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get the user ID from the hidden input field
            const idusers = {{ $data->id_users }};

            // Fetch the last meter reading
            fetch('/pemakaian/' + idusers + '/meter-akhir')
                .then(response => response.json())
                .then(data => {
                    // Populate the meter_awal field with the response
                    document.getElementById('meter_awal').value = data.meter_akhir;
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Default to 0 if there's an error
                    document.getElementById('meter_awal').value = 0;
                });

            // Preview the uploaded image
            const foto_meteranInput = document.getElementById('foto_meteran');
            if (foto_meteranInput) {
                foto_meteranInput.addEventListener('change', function () {
                    const preview = document.getElementById('preview');
                    const file = this.files[0];

                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid" style="max-height: 200px;">';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
        function submitForm(action) {
        var form = document.getElementById('pemakaianForm');
        
        if (action === 'store') {
            form.action = "{{ route('pemakaian.store') }}";
        } else if (action === 'bayar') {
            form.action = "{{ route('pemakaian.bayar') }}";
        }
        
        form.submit();
    }
    </script>
@endsection