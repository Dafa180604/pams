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
                                <div class="col-md-3 align-self-end">
                                    <button type="button" class="btn btn-success" onclick="printTable()">
                                        <i data-feather="printer"></i> Print
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive" id="report-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>KETERANGAN</th>
                                        <th>TANGGAL</th>
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
                                        <td><strong>Saldo Awal</strong></td>
                                        <td>{{ $startDate }}</td>
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
                                            <td>{{ $data->tanggal }}</td>
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
        // Print only the table - fixed version
        function printTable() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');

            // Get the current date filters
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            // Create content for the print window
            let printContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Laporan Keuangan</title>
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
                        <h3>Laporan Keuangan</h3>
                        <p>Periode: ${startDate} - ${endDate}</p>
                        ${document.getElementById('report-table').innerHTML}
                    </body>
                    </html>
                `;

            // Write to the new window and trigger print
            printWindow.document.write(printContent);
            printWindow.document.close();

            // Add event listener for when content is loaded
            printWindow.onload = function () {
                printWindow.print();
                // Close the window after printing (with a delay)
                setTimeout(function () {
                    printWindow.close();
                }, 1000);
            };
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