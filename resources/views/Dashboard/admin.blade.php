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
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-12 stretch-card">
                <div class="row flex-grow-1">
                    <div class="col-md-4 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <h6 class="card-title mb-0">Pelanggan</h6>
                                    <div class="dropdown mb-2">
                                        <a type="button">
                                            <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 col-md-12 col-xl-5">
                                        <h3 class="mb-2">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <h6 class="card-title mb-0">Petugas</h6>
                                    <div class="dropdown mb-2">
                                        <a type="button">
                                            <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 col-md-12 col-xl-5">
                                        <h3 class="mb-2">0</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <h6 class="card-title mb-0">Transaksi</h6>
                                    <div class="dropdown mb-2">
                                        <a type="button">
                                            <i class="icon-lg text-muted pb-3px" data-feather="more-horizontal"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 col-md-12 col-xl-5">
                                        <h3 class="mb-2">0</h3>
                                        <div class="d-flex align-items-baseline">
                                            <p class="text-success">
                                                <span>0</span>
                                                <i data-feather="arrow-up" class="icon-sm mb-1"></i>
                                            </p>
                                            <p class="text-danger">
                                                <span>-0</span>
                                                <i data-feather="arrow-down" class="icon-sm mb-1"></i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- row -->

        <!-- Add the new grouped bar chart -->
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <h6 class="card-title mb-0">GROUPED BAR CHART</h6>
                            <div class="d-flex align-items-center">
                                <label for="yearFilter" class="me-2 mb-0">Filter Tahun:</label>
                                <select id="yearFilter" class="form-select form-select-sm" style="width: 100px;">
                                    <option value="2022">2022</option>
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025" selected>2025</option>
                                </select>
                            </div>
                        </div>
                        <div style="height: 400px;">
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

    <!-- Script for the grouped bar chart -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('groupedBarChart').getContext('2d');

            // Data for the grouped bar chart
            const data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: [100, 150, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650],
                        backgroundColor: '#5D7DF9',
                    },
                    {
                        label: 'Pengeluaran',
                        data: [400, 450, 500, 550, 600, 650, 600, 550, 500, 450, 400, 350],
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
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                    },
                }
            };

            // Create the chart
            new Chart(ctx, config);
        });
    </script>
@endsection