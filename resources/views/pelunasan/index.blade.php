@extends('layouts.master')
@section('title', 'Data Master Transaksi')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Transaksi</li>
            </ol>
        </nav>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Filter Data</h5>
                            <div class="d-flex gap-2">
                                {{-- <button id="pelunasanSelected" class="btn btn-success d-flex align-items-center">
                                    <i class="btn-icon-prepend" data-feather="check-circle"></i>
                                    <span class="ms-2">Pelunasan Terpilih</span>
                                </button> --}}
                                <button id="printSelectedQr" class="btn btn-primary d-flex align-items-center">
                                    <i class="btn-icon-prepend" data-feather="printer"></i>
                                    <span class="ms-2">Cetak Struk</span>
                                </button>
                            </div>
                        </div>

                        <form method="GET" action="{{ url('/pelunasan') }}">
                            <div class="row">
                                <div class="col-md-3 col-12">
                                    <label for="end_date" class="form-label">Tanggal Bulan</label>
                                    <input type="month" name="end_date" id="end_date" class="form-control"
                                        value="{{ request('end_date', date('Y-m')) }}">
                                </div>
                                {{-- <div class="col-md-3 col-12">
                                    <label class="form-label">Periode Pencatatan</label>
                                    <select name="periode" class="form-control">
                                        <option value="">Semua Periode</option>
                                        @if(isset($jadwalCatat) && $jadwalCatat->isNotEmpty())
                                            @foreach($jadwalCatat as $jadwal)
                                                <option value="{{ $jadwal->id_jadwal_catat }}" {{ request('periode') == $jadwal->id_jadwal_catat ? 'selected' : '' }}>
                                                    Periode {{ $jadwal->id_jadwal_catat }} (Tanggal {{ $jadwal->tanggal_awal }}-{{ $jadwal->tanggal_akhir }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="1" {{ request('periode') == '1' ? 'selected' : '' }}>
                                                Periode 1 (Tanggal 1-5)
                                            </option>
                                            <option value="2" {{ request('periode') == '2' ? 'selected' : '' }}>
                                                Periode 2 (Tanggal 15-20)
                                            </option>
                                        @endif
                                    </select>
                                </div> --}}
                                <div class="col-md-3 col-12">
                                    <label class="form-label">Petugas</label>
                                    <select name="petugas" class="form-control">
                                        <option value="">Semua Petugas</option>
                                        @foreach ($petugasUsers as $petugas)
                                            <option value="{{ $petugas->id_users }}"
                                                {{ request('petugas') == $petugas->id_users ? 'selected' : '' }}>
                                                {{ $petugas->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="col-md-3 col-12">
                                    <label class="form-label">Total Terpilih</label>
                                    <input type="text" class="form-control" id="totalTerpilih" value="Rp 0" readonly>
                                </div> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Data Pemakaian Belum Lunas</h4>
                            <small class="text-muted">Total: {{ $dataTransaksi->count() }} transaksi</small>
                        </div>

                        <div class="table-responsive">
                            <table id="dataTableExample" class="table table-striped">
                                <thead>
                                    <tr>
                                        {{-- <th width="50">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllTable">
                                            </div>
                                        </th> --}}
                                        <th>NO.</th>
                                        <th>ID PEMAKAIAN</th>
                                        <th>NAMA PELANGGAN</th>
                                        <th>METER AWAL</th>
                                        <th>METER AKHIR</th>
                                        <th>PEMAKAIAN</th>
                                        <th>JUMLAH RP</th>
                                        <th>TGL PENCATATAN</th>
                                        <th>PETUGAS</th>
                                        <th>Telat Hari</th>
                                        <th>AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($dataTransaksi as $data)
                                    @php
                                    // Hitung selisih hari antara hari ini dengan waktu_catat
                                    $waktuCatat = \Carbon\Carbon::parse($data->pemakaian->waktu_catat);
                                    $hariSekarang = \Carbon\Carbon::now();
                                    $telatHari = floor($waktuCatat->diffInDays($hariSekarang));
                                @endphp
                                        <tr>
                                            {{-- <td>
                                                <div class="form-check">
                                                    <input class="form-check-input pelanggan-checkbox" type="checkbox"
                                                        value="{{ $data->id_pemakaian }}"
                                                        data-jumlah="{{ $data->jumlah_rp }}"
                                                        data-nama="{{ $data->pemakaian->users->nama ?? 'N/A' }}">
                                                </div>
                                            </td> --}}
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->id_pemakaian }}</td>
                                            <td>{{ $data->pemakaian->users->nama ?? 'N/A' }}</td>
                                            <td>{{ $data->pemakaian->meter_awal }}</td>
                                            <td>{{ $data->pemakaian->meter_akhir }}</td>
                                            <td>{{ number_format($data->pemakaian->jumlah_pemakaian) }}</td>
                                            <td>Rp {{ number_format($data->jumlah_rp, 0, ',', '.') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($data->pemakaian->waktu_catat)->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                @if ($data->pemakaian->petugas)
                                                    @php
                                                        $petugasIds = explode(',', $data->pemakaian->petugas);
                                                        $petugasNames = [];
                                                        foreach ($petugasIds as $petugasId) {
                                                            $petugasId = trim($petugasId);
                                                            $petugas = $petugasUsers->firstWhere(
                                                                'id_users',
                                                                $petugasId,
                                                            );
                                                            if ($petugas) {
                                                                $petugasNames[] = $petugas->nama;
                                                            }
                                                        }
                                                        echo implode(', ', $petugasNames) ?: '-';
                                                    @endphp
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($telatHari > 0)
                                                    <span class="text-danger fw-bold">{{ $telatHari }} hari terlambat</span>
                                                @else
                                                    <span class="text-success fw-bold">Tepat waktu</span>
                                                @endif
                                            </td>
                                            <td> 
                                                <a href="{{ route('belumlunas.edit', $data->id_transaksi) }}"
                                                    class="btn btn-success btn-sm">Bayar</a>
                                            </td>
                                        </tr>
                                        </tr>
                                        
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">Tidak ada data yang ditemukan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pelunasan -->
    <div class="modal fade" id="modalPelunasan" tabindex="-1" aria-labelledby="modalPelunasanLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalPelunasanLabel">
                        <i data-feather="check-circle" class="me-2"></i>
                        Pelunasan Transaksi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Detail Pelunasan:</h6>
                        <p class="mb-1"><strong>Jumlah Transaksi:</strong> <span id="jumlahTransaksi">0</span></p>
                        <p class="mb-3"><strong>Total Pembayaran:</strong> <span id="totalPembayaran"
                                class="text-success fw-bold">Rp 0</span></p>
                    </div>

                    <form id="formPelunasan">
                        <div class="mb-3">
                            <label for="uangMasuk" class="form-label">Uang Masuk</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="uangMasuk" min="0"
                                    placeholder="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="kembalian" class="form-label">Kembalian</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control bg-light" id="kembalian" readonly>
                            </div>
                        </div>

                        <div id="alertKurang" class="alert alert-warning d-none" role="alert">
                            <i data-feather="alert-triangle" class="me-2"></i>
                            Uang masuk kurang dari total pembayaran!
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnKonfirmasiPelunasan" disabled>
                        <i data-feather="check" class="me-2"></i>
                        Konfirmasi Pelunasan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form saat filter diubah (TANPA PERIODE)
    const filterInputs = document.querySelectorAll(
        'input[name="end_date"], select[name="petugas"]'); // Hapus select periode
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // --- FUNGSI CHECKBOX ---
    const selectAllCheckbox = document.getElementById('selectAllTable');
    const checkboxes = document.querySelectorAll('.pelanggan-checkbox');
    const totalInput = document.getElementById('totalTerpilih');
    const pelunasanButton = document.getElementById('pelunasanSelected');

    function updateTotal() {
        const checked = document.querySelectorAll('.pelanggan-checkbox:checked');
        let total = 0;
        checked.forEach(cb => {
            total += parseInt(cb.dataset.jumlah) || 0;
        });
        
        if (totalInput) {
            totalInput.value = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Update status button pelunasan
        if (pelunasanButton) {
            if (checked.length > 0) {
                pelunasanButton.disabled = false;
                pelunasanButton.classList.remove('disabled');
            } else {
                pelunasanButton.disabled = true;
                pelunasanButton.classList.add('disabled');
            }
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateTotal();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (selectAllCheckbox) {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                const anyChecked = Array.from(checkboxes).some(c => c.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = anyChecked && !allChecked;
            }
            updateTotal();
        });
    });

    // --- FUNGSI BUTTON PELUNASAN ---
    if (pelunasanButton) {
        pelunasanButton.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.pelanggan-checkbox:checked');

            if (checkedBoxes.length === 0) {
                alert('Silakan pilih minimal satu transaksi untuk dilunasi.');
                return;
            }

            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);
            const totalAmount = Array.from(checkedBoxes).reduce((sum, cb) => sum + parseInt(cb.dataset.jumlah), 0);

            // Set data ke modal
            document.getElementById('jumlahTransaksi').textContent = checkedBoxes.length;
            document.getElementById('totalPembayaran').textContent = 'Rp ' + totalAmount.toLocaleString('id-ID');

            // Reset form
            document.getElementById('uangMasuk').value = '';
            document.getElementById('kembalian').value = '';
            document.getElementById('btnKonfirmasiPelunasan').disabled = true;
            document.getElementById('alertKurang').classList.add('d-none');

            // Simpan data untuk proses pelunasan
            window.pelunasanData = {
                ids: selectedIds,
                total: totalAmount
            };

            // Tampilkan modal
            new bootstrap.Modal(document.getElementById('modalPelunasan')).show();
        });

        // Hitung kembalian otomatis
        document.getElementById('uangMasuk').addEventListener('input', function() {
            const uangMasuk = parseInt(this.value) || 0;
            const totalPembayaran = window.pelunasanData.total;
            const kembalian = uangMasuk - totalPembayaran;

            const kembalianInput = document.getElementById('kembalian');
            const btnKonfirmasi = document.getElementById('btnKonfirmasiPelunasan');
            const alertKurang = document.getElementById('alertKurang');

            if (uangMasuk >= totalPembayaran && uangMasuk > 0) {
                kembalianInput.value = kembalian.toLocaleString('id-ID');
                btnKonfirmasi.disabled = false;
                alertKurang.classList.add('d-none');
            } else {
                kembalianInput.value = '';
                btnKonfirmasi.disabled = true;
                if (uangMasuk > 0) {
                    alertKurang.classList.remove('d-none');
                }
            }
        });

        // Proses pelunasan
        document.getElementById('btnKonfirmasiPelunasan').addEventListener('click', function() {
            const uangMasuk = parseInt(document.getElementById('uangMasuk').value);
            const kembalian = uangMasuk - window.pelunasanData.total;

            // Buat form dinamis untuk POST request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/proses-pelunasan';

            // CSRF Token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            // Transaction IDs
            window.pelunasanData.ids.forEach(function(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'transaction_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            // Data lainnya
            const totalInput = document.createElement('input');
            totalInput.type = 'hidden';
            totalInput.name = 'total_amount';
            totalInput.value = window.pelunasanData.total;
            form.appendChild(totalInput);

            const paidInput = document.createElement('input');
            paidInput.type = 'hidden';
            paidInput.name = 'amount_paid';
            paidInput.value = uangMasuk;
            form.appendChild(paidInput);

            const changeInput = document.createElement('input');
            changeInput.type = 'hidden';
            changeInput.name = 'change_amount';
            changeInput.value = kembalian;
            form.appendChild(changeInput);

            // Close modal before submitting
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalPelunasan'));
            if (modal) {
                modal.hide();
            }

            // Submit form
            document.body.appendChild(form);
            form.submit();
        });
    }

    // --- FUNGSI CETAK STRUK (TANPA PERIODE) ---
    const printButton = document.getElementById('printSelectedQr');
    if (printButton) {
        printButton.addEventListener('click', function() {
            // Ambil parameter filter dari URL halaman saat ini
            const currentUrl = new URL(window.location);
            const params = currentUrl.searchParams;
            
            // Buat URL cetak
            const cetakUrl = new URL('/cetak-struk', window.location.origin);
            
            // Hanya salin parameter yang bukan periode
            params.forEach((value, key) => {
                if (key !== 'periode') { // Skip parameter periode
                    cetakUrl.searchParams.set(key, value);
                }
            });
            
            // Jika tidak ada parameter, ambil dari form (kecuali periode)
            if (cetakUrl.searchParams.toString() === '') {
                const endDate = document.querySelector('input[name="end_date"]');
                const petugas = document.querySelector('select[name="petugas"]');
                
                if (endDate && endDate.value) cetakUrl.searchParams.set('end_date', endDate.value);
                if (petugas && petugas.value) cetakUrl.searchParams.set('petugas', petugas.value);
            }
            
            // Debug log
            console.log('URL Cetak:', cetakUrl.toString());
            
            // Buka di tab baru
            window.open(cetakUrl.toString(), '_blank');
        });
    }

    // Inisialisasi total saat halaman dimuat
    updateTotal();
});
    </script>
@endsection
