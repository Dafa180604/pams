<!-- laporan.index.php -->
@extends('layouts.master')
@section('title', 'Laporan')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Laporan</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Laporan</h4>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form action="{{ route('laporan.index') }}" method="GET" class="mb-4 no-print" id="filterForm">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label">Tanggal Awal</label>
                                    <input type="month" name="start_date" id="start_date" class="form-control"
                                        value="{{ request('start_date', date('Y-m')) }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                                    <input type="month" name="end_date" id="end_date" class="form-control"
                                        value="{{ request('end_date', date('Y-m')) }}">
                                </div>
                                <!--<div class="col-md-3">-->
                                <!--    <label for="status_penerimaan" class="form-label">Status Penerimaan</label>-->
                                <!--    <select name="status_penerimaan" id="status_penerimaan" class="form-control">-->
                                <!--        <option value="all" {{ request('status_penerimaan', 'all') == 'all' ? 'selected' : '' }}>Semua Status</option>-->
                                <!--        <option value="belum_diterima" {{ request('status_penerimaan') == 'belum_diterima' ? 'selected' : '' }}>Belum Diterima</option>-->
                                <!--        <option value="diterima" {{ request('status_penerimaan') == 'diterima' ? 'selected' : '' }}>Diterima</option>-->
                                <!--    </select>-->
                                <!--</div>-->
                                <div class="col-md-5 align-self-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary" onclick="exportToWord()">
                                            <i data-feather="file-text"></i> Export ke Word
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive" id="report-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>KETERANGAN</th>
                                        <th>DEBIT</th>
                                        <th>KREDIT</th>
                                        <th>SISA SALDO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Display saldo awal only if it's not zero -->
                                    @if($previousSaldo != 0)
                                        <tr class="table-secondary">
                                            <td>-</td>
                                            <td><strong>Saldo Bulan Lalu</strong></td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td><strong>Rp {{ number_format($previousSaldo, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    @endif

                                    @php
                                        $runningSaldo = $previousSaldo;
                                        $counter = 1;
                                    @endphp
                                    @foreach($dataLaporan as $data)
                                        @php
                                            $runningSaldo += $data->uang_masuk;
                                            $runningSaldo -= $data->uang_keluar;
                                            // Hanya transaksi "Terima bayar" oleh petugas yang bisa diubah statusnya
                                            $isTerimalBayarPetugas = strpos($data->keterangan, 'Terima bayar') !== false && 
                                                                   strpos($data->keterangan, 'oleh petugas') !== false;
                                            $sudahDiterima = strpos($data->keterangan, 'diterima') !== false && 
                                                           strpos($data->keterangan, 'belum diterima') === false;
                                            $belumDiterima = strpos($data->keterangan, 'belum diterima') !== false;
                                            
                                            // Bersihkan keterangan dari status hanya untuk Terima bayar oleh petugas
                                            $cleanKeterangan = $data->keterangan;
                                            if($isTerimalBayarPetugas) {
                                                $cleanKeterangan = preg_replace('/, (diterima|belum diterima)/', '', $cleanKeterangan);
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $counter++ }}</td>
                                            <td>
                                                @if($isTerimalBayarPetugas)
                                                    <div class="keterangan-content">
                                                        <span class="main-keterangan">{{ $cleanKeterangan }}</span>
                                                        <div class="status-section mt-2">
                                                            @if($sudahDiterima)
                                                                <span class="badge bg-success me-2">Diterima</span>
                                                            @else
                                                                <span class="badge bg-warning me-2">Belum Diterima</span>
                                                            @endif
                                                            
                                                            <!-- Action dropdown in keterangan -->
                                                            <div class="btn-group btn-group-sm no-print">
                                                                <button type="button" class="btn btn-outline-primary btn-xs dropdown-toggle" 
                                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Ubah Status
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    @if(!$belumDiterima)
                                                                        <li>
                                                                            <a class="dropdown-item" href="#" 
                                                                               onclick="updateStatus({{ $data->id_laporan }}, 'belum_diterima')">
                                                                                <i class="fas fa-clock text-warning me-2"></i>Belum Diterima
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                    @if(!$sudahDiterima)
                                                                        <li>
                                                                            <a class="dropdown-item" href="#" 
                                                                               onclick="updateStatus({{ $data->id_laporan }}, 'diterima')">
                                                                                <i class="fas fa-check text-success me-2"></i>Diterima
                                                                            </a>
                                                                        </li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{ $data->keterangan }}
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($data->uang_masuk, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($data->uang_keluar, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($runningSaldo, 0, ',', '.') }}</td>
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

    <script>
        // Format tanggal untuk judul laporan
        function formatDateToMonthYear(dateStr) {
            const bulan = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const date = new Date(dateStr);
            return {
                monthYear: `${bulan[date.getMonth()]} ${date.getFullYear()}`,
                month: date.getMonth(),
                year: date.getFullYear()
            };
        }

        // Fungsi untuk mendapatkan teks periode laporan
        function getPeriodeText() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            const start = formatDateToMonthYear(startDate);
            const end = formatDateToMonthYear(endDate);

            // Jika bulan dan tahun sama, tampilkan satu saja
            return (start.month === end.month && start.year === end.year)
                ? `${start.monthYear}`
                : `${start.monthYear} - ${end.monthYear}`;
        }

        // Export to Word function
        function exportToWord() {
            const periodeText = getPeriodeText();
            
            // Clone table and clean up for export
            const originalTable = document.getElementById('report-table');
            const clonedTable = originalTable.cloneNode(true);
            
            // Clean up keterangan cells - remove action buttons and clean status badges for export
            const rows = clonedTable.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 1) {
                    const keteranganCell = cells[1];
                    const keteranganContent = keteranganCell.querySelector('.keterangan-content');
                    
                    if (keteranganContent) {
                        // Ambil hanya teks utama keterangan tanpa status dan tombol aksi
                        const mainKeterangan = keteranganContent.querySelector('.main-keterangan');
                        if (mainKeterangan) {
                            keteranganCell.innerHTML = mainKeterangan.textContent;
                        }
                    }
                    
                    // Fallback: hapus semua badge dan button jika masih ada
                    const actionsToRemove = keteranganCell.querySelectorAll('.badge, .status-section, .btn-group, .no-print');
                    actionsToRemove.forEach(element => {
                        element.remove();
                    });
                }
            });
            
            // Buat template HTML untuk Word
            const wordTemplate = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:w="urn:schemas-microsoft-com:office:word"
                  xmlns="http://www.w3.org/TR/REC-html40">
            <head>
                <meta charset="utf-8">
                <title>LAPORAN PERTANGGUNG JAWABAN PKS-PAMS DESA TENGGERLOR</title>
                <style>
                    @page {
                        size: 21cm 29.7cm;  /* A4 */
                        margin: 1.5cm;
                    }
                    body { 
                        font-family: 'Arial', sans-serif; 
                        font-size: 11px;
                        margin: 0;
                        padding: 0;
                    }
                    h3 { 
                        text-align: center; 
                        margin: 5px 0;
                        font-size: 14px;
                        font-weight: bold;
                    }
                    p { 
                        text-align: center; 
                        margin: 10px 0 15px 0;
                        font-size: 12px;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-bottom: 20px;
                        font-size: 10px;
                    }
                    th, td { 
                        border: 1px solid #000; 
                        padding: 4px 6px; 
                        vertical-align: middle;
                    }
                    th { 
                        background-color: #f2f2f2; 
                        font-weight: bold; 
                        text-align: center;
                        font-size: 10px;
                    }
                    .table-secondary { 
                        background-color: #e2e3e5; 
                        font-weight: bold;
                    }
                    
                    /* Column widths */
                    th:nth-child(1), td:nth-child(1) { width: 35%; } /* URAIAN */
                    th:nth-child(2), td:nth-child(2) { width: 18%; } /* DEBET */
                    th:nth-child(3), td:nth-child(3) { width: 18%; } /* KREDET */
                    th:nth-child(4), td:nth-child(4) { width: 18%; } /* SALDO */
                    
                    /* Text alignment */
                    td:nth-child(1) { text-align: left; padding-left: 8px; } /* URAIAN column */
                    td:nth-child(2), td:nth-child(3), td:nth-child(4) { 
                        text-align: right; 
                        padding-right: 8px;
                        font-family: 'Courier New', monospace; /* Better for numbers */
                    }
                    
                    /* Special styling for summary rows */
                    .summary-row {
                        font-weight: bold;
                        background-color: #f8f9fa;
                    }
                    
                    /* Better number formatting */
                    .number {
                        white-space: nowrap;
                    }
                </style>
                <!--[if gte mso 9]>
                <xml>
                    <w:WordDocument>
                        <w:View>Print</w:View>
                        <w:Zoom>100</w:Zoom>
                        <w:DoNotPromptForConvert/>
                        <w:DoNotShowRevisions/>
                        <w:DoNotShowInsertionsAndDeletions/>
                        <w:DoNotShowPropertyChanges/>
                    </w:WordDocument>
                </xml>
                <![endif]-->
            </head>
            <body>
                <div style="text-align: center; line-height: 1.2; margin-bottom: 20px;">
                    <h3 style="margin: 0;">LAPORAN PERTANGGUNG JAWABAN</h3>
                    <h3 style="margin: 0;">KPS-PAMS DESA TENGGERLOR</h3>
                </div>
                <p>Periode: ${periodeText}</p>
                ${clonedTable.outerHTML}
            </body>
            </html>
            `;

            // Buat Blob dan href untuk download
            const blob = new Blob([wordTemplate], { type: 'application/msword' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `Laporan_KPS_PAMS_${periodeText.replace(/\s/g, '_')}.doc`;
            
            // Klik link secara programatis untuk memicu download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Function to update status penerimaan
        function updateStatus(id, status) {
    const statusText = status === 'diterima' ? 'Diterima' : 'Belum Diterima';
    const dropdownButton = event.target.closest('.btn-group').querySelector('.dropdown-toggle');
    const originalText = dropdownButton.innerHTML;
    
    Swal.fire({
        title: 'Konfirmasi Perubahan Status',
        html: `Apakah Anda yakin ingin mengubah status menjadi <strong>"${statusText}"</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ubah',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-light'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            dropdownButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            dropdownButton.disabled = true;
            
            fetch(`/laporan/${id}/update-status-penerimaan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status_penerimaan: status })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then(() => {
                        // Auto-refresh halaman setelah update berhasil
                        location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Gagal mengubah status');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-danger'
                    }
                });
            })
            .finally(() => {
                dropdownButton.innerHTML = originalText;
                dropdownButton.disabled = false;
            });
        }
    });
}

        // Auto-submit form when input values change
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil semua elemen input dalam form filter
            const filterInputs = document.querySelectorAll('#start_date, #end_date, #status_penerimaan');

            // Tambahkan event listener untuk setiap elemen
            filterInputs.forEach(input => {
                input.addEventListener('change', function () {
                    // Show loading overlay
                    const overlay = document.createElement('div');
                    overlay.className = 'loading-overlay';
                    overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(255,255,255,0.8);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 9999;
                    `;
                    document.body.appendChild(overlay);
                    
                    // Cari form terdekat yang berisi input ini
                    const form = this.closest('form');
                    if (form) {
                        form.submit(); // Submit form secara otomatis saat nilai berubah
                    }
                });
            });

            // Initialize feather icons if available
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
        
        .badge {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }
        
        .bg-success {
            background-color: #198754 !important;
            color: white !important;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        
        .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }
        
        .dropdown-item i {
            width: 16px;
        }
        
        .keterangan-content {
            line-height: 1.4;
        }
        
        .main-keterangan {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .status-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    </style>
    
@endsection