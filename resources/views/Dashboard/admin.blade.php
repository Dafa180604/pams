@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <!-- partial -->
    <div class="page-content">

        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
            <div>
                <h4 class="mb-3 mb-md-0">Selamat Datang Di Dashboard Admin</h4>
            </div>
            <div class="d-flex align-items-center flex-wrap text-nowrap">
                <div class="input-group flatpickr wd-200 me-2 mb-2 mb-md-0" id="dashboardDate">
                    <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle>
                        <i data-feather="calendar" class="text-primary"></i>
                    </span>
                    <input type="text" class="form-control bg-transparent border-primary" placeholder="Select date"
                        id="dateTimeInput" disabled>
                </div>
                <!-- <button id="refreshData" class="btn btn-primary ms-2">
                                                <i data-feather="refresh-cw" class="icon-sm"></i> Refresh Data
                                            </button> -->
            </div>
        </div>

        <div class="row">
            <!-- Customer Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Pelanggan</h6>
                                <h2 class="mb-0">{{ number_format($customerCount) }}</h2>
                            </div>
                            <div class="icon-shape bg-primary text-white rounded-circle p-3">
                                <i data-feather="users" class="feather-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Petugas</h6>
                                <h2 class="mb-0">{{ number_format($staffCount) }}</h2>
                            </div>
                            <div class="icon-shape bg-success text-white rounded-circle p-3">
                                <i data-feather="user-check" class="feather-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Transaksi</h6>
                                <h2 class="mb-0">{{ number_format($transactionCount) }}</h2>
                                <div class="row">
                                    <div class="col-6 col-md-12 col-xl-5">
                                        <div class="d-flex align-items-baseline">
                                            <p class="text-success">
                                                <span>{{ $paidTransactions }}</span>
                                                <i data-feather="arrow-up" class="icon-sm mb-1"></i>
                                            </p>
                                            <p class="text-danger">
                                                <span>-{{ $unpaidTransactions }}</span>
                                                <i data-feather="arrow-down" class="icon-sm mb-1"></i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="icon-shape bg-info text-white rounded-circle p-3">
                                <i data-feather="shopping-cart" class="feather-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- row -->

        <!-- Charts row - Fixed to be side by side -->
        <!-- Loading spinner -->
        <div id="chartLoadingSpinner"
            style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 1000; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-success-light p-3 me-3">
                            <i data-feather="arrow-up-circle" class="text-success icon-md"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Total Pemasukan</h6>
                            <h4 id="totalPemasukan" class="mb-0 fw-bold">Rp 0</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-danger-light p-3 me-3">
                            <i data-feather="arrow-down-circle" class="text-danger icon-md"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Total Pengeluaran</h6>
                            <h4 id="totalPengeluaran" class="mb-0 fw-bold">Rp 0</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-primary-light p-3 me-3">
                            <i data-feather="dollar-sign" class="text-primary icon-md"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Saldo</h6>
                            <h4 id="totalBalance" class="mb-0 fw-bold">Rp 0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
<!--             <div class="d-flex justify-content-between align-items-baseline mb-2 mt-2 px-3">
                <h5>GRAFIK LAPORAN</h5>
            </div> -->
            <!-- Bar Chart -->
            <div class="col-xl-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <h6 class="card-title mb-0">Pemasukan & Pengeluaran</h6>
                            <div class="d-flex align-items-center">
                                <select id="yearFilter" class="form-select form-select-sm me-2" style="width: 100px;">
                                    @foreach($years ?? range(date('Y') - 3, date('Y')) as $year)
                                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <select id="barChartMonthFilter" class="form-select form-select-sm" style="width: 100px;">
                                    <option value="all" selected>Semua Bulan</option>
                                    <option value="01">Januari</option>
                                    <option value="02">Februari</option>
                                    <option value="03">Maret</option>
                                    <option value="04">April</option>
                                    <option value="05">Mei</option>
                                    <option value="06">Juni</option>
                                    <option value="07">Juli</option>
                                    <option value="08">Agustus</option>
                                    <option value="09">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                        </div>
                        <div style="height: 350px;">
                            <canvas id="groupedBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- row -->
    </div> <!-- page-content -->

    <script>
        function updateTime() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var seconds = now.getSeconds().toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var year = now.getFullYear();
            var formattedDateTime = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
            document.getElementById('dateTimeInput').value = formattedDateTime;
        }

        setInterval(updateTime, 1000);
        flatpickr("#dashboardDate input", {
            enableTime: false,
            dateFormat: "d/m/Y",
        });
        updateTime();
        feather.replace();
    </script>

    <!-- Chart.js script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <!-- Enhanced chart scripts with DB integration -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    let barChart;

    // Load chart data from the server
    function fetchChartData(year, callback) {
        // Show loading indicator
        document.getElementById('chartLoadingSpinner').style.display = 'flex';
        
        // Prepare URL with parameters
        let url = `/api/laporan-data?year=${year}`;
        
        // Use AJAX to fetch data from your Laravel backend
        fetch(url)
            .then(response => response.json())
            .then(data => {
                callback(data);
                // Hide loading indicator
                document.getElementById('chartLoadingSpinner').style.display = 'none';
            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
                // Hide loading indicator even on error
                document.getElementById('chartLoadingSpinner').style.display = 'none';
            });
    }

    // Function to initialize/update bar chart
    function updateBarChart(chartData, year, monthFilter = 'all') {
        const ctx = document.getElementById('groupedBarChart').getContext('2d');

        // Filter data based on month if needed
        let labels = monthLabels;
        let pemasukanData = chartData.pemasukan;
        let pengeluaranData = chartData.pengeluaran;

        if (monthFilter !== 'all') {
            const monthIndex = parseInt(monthFilter) - 1;
            labels = [monthLabels[monthIndex]];
            pemasukanData = [chartData.pemasukan[monthIndex]];
            pengeluaranData = [chartData.pengeluaran[monthIndex]];
        }

        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: pemasukanData,
                    backgroundColor: '#5D7DF9',
                },
                {
                    label: 'Pengeluaran',
                    data: pengeluaranData,
                    backgroundColor: '#FF3366',
                }
            ]
        };

        // Configuration options
        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.1)",
                            borderDash: [3, 3]
                        },
                        ticks: {
                            // Format in millions
                            callback: function (value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + ' Juta';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + ' Ribu';
                                }
                                return value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Laporan ${year}`
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.dataset.label || '';
                                const value = context.raw || 0;
                                return `${label}: Rp ${value.toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
            }
        };

        // Destroy previous chart if it exists
        if (barChart) {
            barChart.destroy();
        }

        // Create the chart
        barChart = new Chart(ctx, config);
    }

    // Function to update both charts
    function updateCharts() {
        const selectedYear = document.getElementById('yearFilter').value;
        const barChartMonthFilter = document.getElementById('barChartMonthFilter').value;

        fetchChartData(selectedYear, function (chartData) {
            updateBarChart(chartData, selectedYear, barChartMonthFilter);

            // Update total counters
            document.getElementById('totalPemasukan').textContent = formatRupiah(chartData.total_pemasukan);
            document.getElementById('totalPengeluaran').textContent = formatRupiah(chartData.total_pengeluaran);
            document.getElementById('totalBalance').textContent = formatRupiah(chartData.balance);
        });
    }

    // Format number to Rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(number);
    }

    // Initialize charts with default values
    updateCharts();

    // Event listeners for filters
    document.getElementById('yearFilter').addEventListener('change', updateCharts);
    document.getElementById('barChartMonthFilter').addEventListener('change', updateCharts);
});  </script>
@endsection
