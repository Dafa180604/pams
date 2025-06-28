@extends('layouts.master')
@section('title', 'Biaya Denda')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('BiayaDenda.index') }}">Data Master Biaya Denda
                        Air</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>
        <!-- pasti -->
        <div class="row">
            <div class="col-md-12 stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title fs-4 mb-4">Form Data Tambah Biaya Denda</h4>
                        <form action="{{ route('BiayaDenda.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Telat</label>
                                        <input type="text" name="jumlah_telat" class="form-control"
                                            placeholder="Masukkan Jumlah Telat" value="{{ old('jumlah_telat') }}">
                                        @error('jumlah_telat')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Biaya Telat (Rp)</label>
                                        <div class="input-group">
                                            <input type="text" name="biaya_telat" id="biaya_telat" class="form-control"
                                                placeholder="Masukkan Biaya Telat (Rp)" value="{{ old('biaya_telat') }}">
                                            <div class="input-group-text">
                                                <input class="form-check-input mt-0" type="checkbox" id="is_max" name="is_max" value="1" {{ old('is_max') ? 'checked' : '' }}>
                                                <label class="form-check-label ms-2" for="is_max">
                                                    <small><strong>Denda Maks</strong></small>
                                                </label>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Centang "Denda Maks" jika ini adalah tingkat denda tertinggi. Setelah ditetapkan, tidak dapat menambah data baru lagi.
                                        </small>
                                        @error('biaya_telat')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div><!-- Col --><!-- Col -->
                            </div>

                            <a href="{{ route('BiayaDenda.index') }}" type="reset" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Tambahkan</button>
                        </form>
                        <div class="mt-3">
                            <small class="text-muted d-block">
                                <strong>NB:</strong> Jumlah telat (hari) dan biaya telat (Rp) tidak boleh lebih kecil dari
                                data sebelumnya atau data dengan nilai terbesar yang sudah ada.
                            </small>
                            <small class="text-muted d-block">
                                <strong>Data Saat Ini:</strong> Jumlah Telat Maksimal:
                                <strong>{{ $maxJumlahTelat ?? 0 }}</strong> hari, Biaya Telat Maksimal:
                                <strong>Rp.{{ number_format($maxBiayaTelat ?? 0, 0, ',', '.') }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isMaxCheckbox = document.getElementById('is_max');
            const biayaTelatInput = document.getElementById('biaya_telat');
            let originalValue = biayaTelatInput.value;

            isMaxCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // Simpan nilai asli jika ada
                    if (biayaTelatInput.value !== '') {
                        originalValue = biayaTelatInput.value;
                    }
                    // Kosongkan field dan disable input
                    biayaTelatInput.value = '';
                    biayaTelatInput.setAttribute('readonly', true);
                    biayaTelatInput.style.backgroundColor = '#f8f9fa';
                } else {
                    // Restore nilai asli
                    biayaTelatInput.value = originalValue;
                    biayaTelatInput.removeAttribute('readonly');
                    biayaTelatInput.style.backgroundColor = '';
                }
            });

            // Jika checkbox sudah checked saat load (old input)
            if (isMaxCheckbox.checked) {
                biayaTelatInput.value = '';
                biayaTelatInput.setAttribute('readonly', true);
                biayaTelatInput.style.backgroundColor = '#f8f9fa';
            }
        });
    </script>
@endsection