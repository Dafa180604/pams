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

                        <!-- Filter Form - tanpa tombol filter -->
                        <form action="{{ route('laporan.index') }}" method="GET" class="mb-4 no-print" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Tanggal Awal</label>
                                    <input type="month" name="start_date" id="start_date" class="form-control"
                                        value="{{ request('start_date', date('Y-m')) }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                                    <input type="month" name="end_date" id="end_date" class="form-control"
                                        value="{{ request('end_date', date('Y-m')) }}">
                                </div>
                                <div class="col-md-6 align-self-end">
                                    <div class="btn-group">
                                        <!-- <button type="button" class="btn btn-success me-2" onclick="printTable()">
                                            <i data-feather="printer"></i> Print
                                        </button> -->
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
                                        <!-- <th>TANGGAL</th> -->
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
                                            <!-- <td>{{ $startDate }}</td> -->
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
                                        @endphp
                                        <tr>
                                            <td>{{ $counter++ }}</td>
                                            <td>{{ $data->keterangan }}</td>
                                            <!-- <td>{{ $data->tanggal }}</td> -->
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

        // Print only the table - fixed version
        function printTable() {
            const printWindow = window.open('', '_blank');
            const periodeText = getPeriodeText();

            let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>LAPORAN PERTANGGUNG JAWABAN PKS-PAMS DESA TENGGERLOR</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    h3 { text-align: center; margin-bottom: 10px; }
                    p { text-align: center; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .text-end { text-align: right; }
                    .table-secondary { background-color: #e2e3e5; }
                </style>
            </head>
            <body>
                <div style="text-align: center; line-height: 1.2;">
                    <h3 style="margin: 0;">LAPORAN PERTANGGUNG JAWABAN</h3>
                    <h3 style="margin: 0;">KPS-PAMS DESA TENGGERLOR</h3>
                </div>
                <p>Periode: ${periodeText}</p>
                ${document.getElementById('report-table').innerHTML}
            </body>
            </html>
            `;

            printWindow.document.write(printContent);
            printWindow.document.close();

            printWindow.onload = function () {
                printWindow.print();
                setTimeout(() => printWindow.close(), 1000);
            };
        }

        // Export to Word function
        function exportToWord() {
            const periodeText = getPeriodeText();
            
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
                        margin: 2cm;
                    }
                    body { font-family: 'Arial', sans-serif; }
                    h3 { text-align: center; margin-bottom: 10px; }
                    p { text-align: center; margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { border: 1px solid #000; padding: 8px; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    .table-secondary { background-color: #e2e3e5; }
                </style>
                <!--[if gte mso 9]>
                <xml>
                    <w:WordDocument>
                        <w:View>Print</w:View>
                        <w:Zoom>100</w:Zoom>
                    </w:WordDocument>
                </xml>
                <![endif]-->
            </head>
            <body>
                <div style="text-align: center; line-height: 1.2;">
                    <h3 style="margin: 0;">LAPORAN PERTANGGUNG JAWABAN</h3>
                    <h3 style="margin: 0;">KPS-PAMS DESA TENGGERLOR</h3>
                </div>
                <p>Periode: ${periodeText}</p>
                ${document.getElementById('report-table').innerHTML}
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

        // Auto-submit form when input values change
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil semua elemen input date dalam form filter
            const filterInputs = document.querySelectorAll('#start_date, #end_date');

            // Tambahkan event listener untuk setiap elemen
            filterInputs.forEach(input => {
                input.addEventListener('change', function () {
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
@endsection