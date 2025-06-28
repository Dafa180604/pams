<!-- belumlunas.edit -->
@extends('layouts.master')
@section('title', 'Pembayaran')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('belumlunas.index') }}">Data Master Transaksi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title fs-4 mb-4 fw-bold black">PEMBAYARAN</h4>

                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-indigo-600 text-white" style="background-color: #6366f1;">
                                <h5 class="mb-0 fw-semibold"><i class="mdi mdi-account me-2"></i>Informasi Pelanggan</h5>
                            </div>
                            <div class="card-body bg-white">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2 flex">
                                            <span class="text-muted w-32">ID Pelanggan:</span>
                                            <span
                                                class="fw-bold">{{ optional($data->pemakaian->users()->withTrashed()->first())->id_users ?? 'Tidak tersedia' }}</span>
                                        </div>
                                        <div class="mb-2 flex">
                                            <span class="text-muted w-32">Nama Pelanggan:</span>
                                            <span
                                                class="fw-bold">{{ optional($data->pemakaian->users()->withTrashed()->first())->nama ?? 'Tidak tersedia' }}</span>
                                        </div>
                                        <div class="mb-2 flex">
                                            <span class="text-muted w-32">Petugas:</span>
                                            <span class="fw-bold">
                                                @if($petugasUser)
                                                    {{ $petugasUser->nama }}
                                                @else
                                                    @php
                                                        $petugasId = $data->pemakaian->petugas;
                                                        $user = DB::table('users')->where('id_users', $petugasId)
                                                            ->orWhere('id_users', $petugasId)
                                                            ->first();
                                                        echo $user ? $user->nama : $petugasId;
                                                    @endphp
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2 flex">
                                            <span class="text-muted w-32">Meter Awal:</span>
                                            <span class="fw-bold">{{ $data->pemakaian->meter_awal }} m³</span>
                                        </div>
                                        <div class="mb-2 flex">
                                            <span class="text-muted w-32">Meter Akhir:</span>
                                            <span class="fw-bold">{{ $data->pemakaian->meter_akhir }} m³</span>
                                        </div>
                                        <div class="mb-2 flex">
                                            <span class="text-muted w-32">Jumlah Pemakaian:</span>
                                            <span class="fw-bold">{{ $data->pemakaian->jumlah_pemakaian }} m³</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Biaya Section -->
                        <div class="container-fluid mb-4 px-0">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-indigo-600 text-white" style="background-color: #6366f1;">
                                    <h5 class="mb-0 fw-semibold"><i class="mdi mdi-file-document me-2"></i>Rincian Biaya
                                    </h5>
                                </div>
                                <div class="card-body bg-white">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <tbody>
                                                @php
                                                    // Mengambil dan decode data detail_biaya
                                                    $detailBiaya = json_decode($data->detail_biaya ?? '{}', true);
                                                    $beban = $detailBiaya['beban'] ?? ['tarif' => 0];
                                                    $kategoriList = $detailBiaya['kategori'] ?? [];
                                                    $totalTagihan = $data->jumlah_rp ?? 0;
                                                @endphp

                                                <!-- Tampilkan biaya beban -->
                                                <tr>
                                                    <th class="text-muted">Beban</th>
                                                    <td class="text-end">
                                                        Rp {{ number_format($beban['tarif'], 0, ',', '.') }}
                                                    </td>
                                                </tr>

                                                <!-- Tampilkan semua kategori yang digunakan -->
                                                @foreach($kategoriList as $kategori)
                                                    <tr>
                                                        <th class="text-muted">{{ $kategori['volume'] }} m³ × Rp
                                                            {{ number_format($kategori['tarif'], 0, ',', '.') }}
                                                        </th>
                                                        <td class="text-end">Rp
                                                            {{ number_format($kategori['subtotal'], 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr id="denda-row">
                                                    <th class="text-muted">Denda</th>
                                                    <td class="text-end" id="denda-amount">
                                                        @php
                                                            $denda = $detailBiaya['denda'] ?? null;
                                                            $dendaAmount = $denda ? $denda['rp_denda'] : 0;
                                                        @endphp
                                                        Rp {{ number_format($dendaAmount, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <!-- Tampilkan total tagihan -->
                                                <tr class="bg-light">
                                                    <th class="fw-bold fs-5">TOTAL TAGIHAN</th>
                                                    <td class="text-end fw-bold fs-5 text-black" id="total-tagihan">Rp
                                                        {{ number_format($totalTagihan, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($detailBiaya['denda']) && $detailBiaya['denda']['rp_denda'] > 0)
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pemulihan_denda" name="pemulihan_denda"
                                        value="1" style="transform: scale(1.2);">
                                    <label class="form-check-label fw-semibold text-success ms-2" for="pemulihan_denda">
                                        <i class="mdi mdi-gift me-1"></i>Berikan Pemulihan/Pengampunan Denda
                                        <small class="text-muted d-block">Denda sebesar Rp {{ number_format($detailBiaya['denda']['rp_denda'], 0, ',', '.') }} akan diampuni</small>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('belumlunas.update', $data->id_transaksi) }}" method="POST" class="mt-4"
                            id="paymentForm">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="pencatatan" value="">
                            <input type="hidden" id="jumlah_rp" name="jumlah_rp" value="{{ $data->jumlah_rp }}">
                            <input type="hidden" id="pemulihan_denda_input" name="pemulihan_denda" value="0">

                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-indigo-600 text-white" style="background-color: #6366f1;">
                                    <h5 class="mb-0 fw-semibold"><i class="mdi mdi-cash-multiple me-2"></i>Informasi
                                        Pembayaran</h5>
                                </div>
                                <div class="card-body bg-white">
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Uang Bayar (Rp)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-indigo-600 text-white"
                                                        style="background-color: #6366f1;"><i
                                                            class="mdi mdi-cash-plus"></i></span>
                                                    <input type="number" id="uang_bayar" name="uang_bayar"
                                                        class="form-control @error('uang_bayar') is-invalid @enderror"
                                                        placeholder="Masukkan jumlah uang" required>
                                                    @error('uang_bayar')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label text-muted">Kembalian (Rp)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-indigo-600 text-white"
                                                        style="background-color: #6366f1;"><i
                                                            class="mdi mdi-cash-return"></i></span>
                                                    <input type="text" id="kembalian" name="kembalian" class="form-control"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="{{ route('belumlunas.index') }}" class="btn btn-secondary me-2"
                                        style="background-color: #6c757d;">
                                        <i class="mdi mdi-arrow-left me-1"></i> Batal
                                    </a>
                                    <button type="submit" id="btnSubmit" class="btn text-white"
                                        style="background-color: #6366f1;" disabled>
                                        <i class="mdi mdi-check-circle me-1"></i> BAYAR
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const uangBayarInput = document.getElementById('uang_bayar');
            const jumlahRpInput = document.getElementById('jumlah_rp');
            const kembalianInput = document.getElementById('kembalian');
            const btnSubmit = document.getElementById('btnSubmit');
            const pemulihanCheckbox = document.getElementById('pemulihan_denda');
            const pemulihanInput = document.getElementById('pemulihan_denda_input');
            const totalTagihanElement = document.getElementById('total-tagihan');
            const dendaRowElement = document.getElementById('denda-row');
            const dendaAmountElement = document.getElementById('denda-amount');

            // Format number as currency
            function formatCurrency(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            // Ambil nilai asli dari server
            let originalTotal = parseFloat(jumlahRpInput.value) || 0;
            let dendaAmount = 0;
            let currentTotal = originalTotal;

            // Ambil nilai denda dari detail biaya jika ada
            @if(isset($detailBiaya['denda']) && $detailBiaya['denda']['rp_denda'] > 0)
                dendaAmount = {{ $detailBiaya['denda']['rp_denda'] }};
            @endif

            // Handle pemulihan denda checkbox
            if (pemulihanCheckbox) {
                pemulihanCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        // PENGAMPUNAN: Hitung total tanpa denda (pastikan tidak negatif)
                        currentTotal = Math.max(0, originalTotal - dendaAmount);
                        
                        // Update input hidden
                        jumlahRpInput.value = currentTotal;
                        pemulihanInput.value = '1';

                        // Update tampilan denda menjadi Rp 0 dengan strikethrough
                        dendaAmountElement.innerHTML = 
                            '<span style="text-decoration: line-through; color: #dc3545;">Rp ' + formatCurrency(dendaAmount) + '</span> ' +
                            '<span class="text-success fw-bold">Rp 0 (Diampuni)</span>';

                        // Update tampilan total tagihan
                        totalTagihanElement.innerHTML = 
                            'Rp ' + formatCurrency(currentTotal) + 
                            ' <small class="text-success fw-bold d-block"><i class="mdi mdi-gift me-1"></i>Denda Rp ' + 
                            formatCurrency(dendaAmount) + ' diampuni</small>';

                        // Tambahkan class untuk highlight
                        dendaRowElement.classList.add('table-success');

                    } else {
                        // BATALKAN PENGAMPUNAN: Kembalikan ke total asli
                        currentTotal = originalTotal;
                        
                        // Update input hidden
                        jumlahRpInput.value = currentTotal;
                        pemulihanInput.value = '0';

                        // Kembalikan tampilan denda normal
                        dendaAmountElement.innerHTML = 'Rp ' + formatCurrency(dendaAmount);

                        // Kembalikan tampilan total tagihan normal
                        totalTagihanElement.innerHTML = 'Rp ' + formatCurrency(currentTotal);

                        // Hapus class highlight
                        dendaRowElement.classList.remove('table-success');
                    }

                    // Reset input uang bayar untuk kalkulasi ulang
                    uangBayarInput.value = '';
                    kembalianInput.value = '';
                    btnSubmit.disabled = true;
                });
            }

            // Calculate and display kembalian when uang_bayar changes
            uangBayarInput.addEventListener('input', function () {
                const uangBayar = parseFloat(this.value) || 0;
                const jumlahRp = parseFloat(jumlahRpInput.value) || 0;
                const kembalian = uangBayar - jumlahRp;

                if (uangBayar >= jumlahRp && uangBayar > 0) {
                    kembalianInput.value = 'Rp ' + formatCurrency(kembalian);
                    kembalianInput.classList.remove('text-danger');
                    kembalianInput.classList.add('text-success');
                    btnSubmit.disabled = false;
                } else if (uangBayar > 0 && uangBayar < jumlahRp) {
                    const kurang = jumlahRp - uangBayar;
                    kembalianInput.value = 'Kurang Rp ' + formatCurrency(kurang);
                    kembalianInput.classList.remove('text-success');
                    kembalianInput.classList.add('text-danger');
                    btnSubmit.disabled = true;
                } else {
                    kembalianInput.value = '';
                    kembalianInput.classList.remove('text-success', 'text-danger');
                    btnSubmit.disabled = true;
                }
            });

            // Validasi form sebelum submit
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                const uangBayar = parseFloat(uangBayarInput.value) || 0;
                const jumlahRp = parseFloat(jumlahRpInput.value) || 0;

                if (uangBayar < jumlahRp) {
                    e.preventDefault();
                    alert('Uang bayar tidak mencukupi!');
                    return false;
                }
            });
        });
    </script>
@endsection