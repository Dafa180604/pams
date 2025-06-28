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
        // Mengirimkan data lengkap dari controller Laravel ke JavaScript
        const dataLaporanFromServer = @json($dataLaporan);
        const previousSaldoFromServer = {{ $previousSaldo ?? 0 }};
    </script>
    <script>
        // Format tanggal untuk judul laporan
        // Format tanggal untuk judul laporan
// Fungsi untuk format tanggal Indonesia
function formatDateToIndonesian(dateStr) {
    const bulan = [
        'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
        'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
    ];
    const date = new Date(dateStr + '-01');
    return {
        monthYear: `${bulan[date.getMonth()]} ${date.getFullYear()}`,
        month: date.getMonth(),
        year: date.getFullYear(),
        monthName: bulan[date.getMonth()]
    };
}

// Fungsi untuk mendapatkan data laporan yang dikelompokkan per bulan
// Fungsi untuk mendapatkan data laporan yang dikelompokkan per bulan (VERSI BARU YANG LEBIH BAIK)
function getDataPerBulan() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const dataPerBulan = {};
    let saldoAwalBulan = previousSaldoFromServer; // Gunakan saldo dari server

    // 1. Inisialisasi semua bulan dalam rentang yang dipilih
    const startMonth = new Date(startDate + '-01');
    const endMonth = new Date(endDate + '-01');
    let currentMonth = new Date(startMonth);

    while (currentMonth <= endMonth) {
        const monthKey = currentMonth.toISOString().slice(0, 7); // Format: YYYY-MM
        dataPerBulan[monthKey] = {
            data: [],
            totalDebit: 0,
            totalKredit: 0,
            saldoAwal: 0, // Akan diisi nanti
            saldoAkhir: 0
        };
        currentMonth.setMonth(currentMonth.getMonth() + 1);
    }

    // 2. Proses data dari server dan kelompokkan ke bulan yang sesuai
    let runningSaldo = previousSaldoFromServer;

    dataLaporanFromServer.forEach(item => {
        // Asumsi kolom tanggal bernama 'tanggal'. Jika namanya lain (misal: 'created_at'), sesuaikan di sini.
        const transaksiTanggal = new Date(item.tanggal);
        const bulanKey = transaksiTanggal.toISOString().slice(0, 7); // Format: YYYY-MM

        // Pastikan transaksi berada dalam rentang bulan yang dipilih
        if (dataPerBulan[bulanKey]) {
            runningSaldo += (item.uang_masuk || 0) - (item.uang_keluar || 0);

            dataPerBulan[bulanKey].data.push({
                no: dataPerBulan[bulanKey].data.length + 1,
                // PERBAIKAN: Sertakan tanggal di sini
                tanggal: item.tanggal, 
                keterangan: item.keterangan,
                debit: item.uang_masuk || 0,
                kredit: item.uang_keluar || 0,
                saldo: runningSaldo
            });

            dataPerBulan[bulanKey].totalDebit += (item.uang_masuk || 0);
            dataPerBulan[bulanKey].totalKredit += (item.uang_keluar || 0);
        }
    });

    // 3. Hitung saldo awal dan akhir untuk setiap bulan
    const sortedMonths = Object.keys(dataPerBulan).sort();

    sortedMonths.forEach((bulanKey, index) => {
        if (index === 0) {
            // Bulan pertama menggunakan saldo awal dari server
            dataPerBulan[bulanKey].saldoAwal = previousSaldoFromServer;
        } else {
            // Bulan berikutnya menggunakan saldo akhir dari bulan sebelumnya
            const prevMonthKey = sortedMonths[index - 1];
            dataPerBulan[bulanKey].saldoAwal = dataPerBulan[prevMonthKey].saldoAkhir;
        }

        // Saldo akhir adalah saldo awal ditambah total debit dikurangi total kredit bulan ini
        const saldoAkhirBulan = dataPerBulan[bulanKey].saldoAwal + dataPerBulan[bulanKey].totalDebit - dataPerBulan[bulanKey].totalKredit;
        dataPerBulan[bulanKey].saldoAkhir = saldoAkhirBulan;
    });

    return dataPerBulan;
}

// SOLUSI ALTERNATIF: Fungsi yang menggunakan data dari server
function getDataPerBulanFromServer() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Ambil data dari server dengan AJAX
    return fetch(`/laporan/get-data-per-bulan?start_date=${startDate}&end_date=${endDate}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        return data;
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        // Fallback ke metode lama jika gagal
        return getDataPerBulan();
    });
}


// Fungsi untuk membuat tabel HTML per bulan
function createMonthTable(bulanKey, data, bulanInfo) {
    let tableHTML = `
    <div style="margin-bottom: 40px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 5px;">
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #e9ecef;">
                    BULAN ${bulanInfo.monthName} ${bulanInfo.year}
                </td>
            </tr>
        </table>
        
        <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 0;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 8%;">Tgl</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 5%;">No</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 42%;">U R A I A N</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">DEBET</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">KREDIT</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">SALDO</td>
                </tr>
            </thead>
        </table>
        
        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            <tbody>`;
    
    // Saldo bulan lalu jika ada yang perlu ditampilkan
    if (data.saldoAwal !== 0) {
        tableHTML += `
                <tr style="background-color: #f8f9fa;">
                    <td style="border: 1px solid #000; padding: 6px; text-align: center; width: 8%;">-</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: center; width: 5%;">-</td>
                    <td style="border: 1px solid #000; padding: 6px; width: 42%;"><strong>SALDO BULAN LALU</strong></td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: right; width: 15%;">-</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: right; width: 15%;">-</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; width: 15%;">${new Intl.NumberFormat('id-ID').format(data.saldoAwal)}</td>
                </tr>`;
    }
    
    // Data transaksi
    data.data.forEach((item) => {
        // Bersihkan keterangan dari status penerimaan
        let cleanKeterangan = item.keterangan.replace(/, (diterima|belum diterima)/gi, '').replace(/\s*(Diterima|Belum Diterima)\s*/gi, '');
        
        // --- INILAH PERBAIKAN UTAMANYA ---
        // Ambil hanya angka hari dari string tanggal (misal: dari "2024-05-15" menjadi "15")
        const tanggalHari = item.tanggal ? new Date(item.tanggal).getDate() : '-';
        
        tableHTML += `
                <tr>
                    <td style="border: 1px solid #000; padding: 6px; text-align: center; width: 8%;">${tanggalHari}</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: center; width: 5%;">${item.no}</td>
                    <td style="border: 1px solid #000; padding: 6px; width: 42%;">${cleanKeterangan}</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: right; width: 15%;">${item.debit > 0 ? new Intl.NumberFormat('id-ID').format(item.debit) : '-'}</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: right; width: 15%;">${item.kredit > 0 ? new Intl.NumberFormat('id-ID').format(item.kredit) : '-'}</td>
                    <td style="border: 1px solid #000; padding: 6px; text-align: right; width: 15%;">${new Intl.NumberFormat('id-ID').format(item.saldo)}</td>
                </tr>`;
    });
    
    tableHTML += `
            </tbody>
        </table>
        
        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="3" style="border: 1px solid #000; padding: 8px; text-align: center; width: 55%;"><strong>J U M L A H</strong></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; width: 15%;"><strong>${new Intl.NumberFormat('id-ID').format(data.totalDebit)}</strong></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; width: 15%;"><strong>${new Intl.NumberFormat('id-ID').format(data.totalKredit)}</strong></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; width: 15%;"><strong>${new Intl.NumberFormat('id-ID').format(data.saldoAkhir)}</strong></td>
            </tr>
        </table>
    </div>`;
    
    return tableHTML;
}
function countPasangBaru(dataLaporan) {
    let count = 0;
    const pasangRegex = /^biaya pasang/i; // Hanya yang diawali dengan "biaya pasang"
    
    dataLaporan.forEach(item => {
        if (pasangRegex.test(item.keterangan)) {
            count++;
        }
    });
    return count;
}

// Fungsi export to Word yang diperbaiki
function exportToWord() {
    const jumlahPasangBaru = countPasangBaru(dataLaporanFromServer);
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    const start = formatDateToIndonesian(startDate);
    const end = formatDateToIndonesian(endDate);
    
    // Tentukan periode text
    const periodeText = (start.month === end.month && start.year === end.year)
        ? `${start.monthYear}`
        : `${start.monthYear} - ${end.monthYear}`;
    
    // Dapatkan data per bulan
    const dataPerBulan = getDataPerBulan();
    const sortedBulan = Object.keys(dataPerBulan).sort();
    
    // Buat tabel untuk setiap bulan
    let tablesHTML = '';
    sortedBulan.forEach(bulanKey => {
        const bulanInfo = formatDateToIndonesian(bulanKey);
        tablesHTML += createMonthTable(bulanKey, dataPerBulan[bulanKey], bulanInfo);
    });
    
    // Hitung saldo akhir untuk catatan
    const lastMonth = sortedBulan[sortedBulan.length - 1];
    const saldoAkhir = dataPerBulan[lastMonth]?.saldoAkhir || 0;
    
    // Template Word yang diperbaiki
    const wordTemplate = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:w="urn:schemas-microsoft-com:office:word"
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta charset="utf-8">
        <title>LAPORAN PERTANGGUNG JAWABAN KPS-PAMS DESA TENGGERLOR</title>
        <style>
            @page {
                size: 21cm 29.7cm;
                margin: 2cm 1.5cm;
            }
            body { 
                font-family: 'Arial', sans-serif; 
                font-size: 11px;
                margin: 0;
                padding: 0;
                line-height: 1.2;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .header h3 { 
                margin: 2px 0;
                font-size: 14px;
                font-weight: bold;
            }
            .periode {
                text-align: center;
                margin: 15px 0 20px 0;
                font-size: 12px;
                font-weight: bold;
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
            .notes {
                margin-top: 20px;
                font-size: 10px;
                line-height: 1.4;
            }
            .signature-table {
                margin-top: 40px;
                width: 100%;
                border-collapse: collapse;
            }
            .signature-table td {
                border: 1px solid #fff; /* Border putih/transparan */
                padding: 15px;
                text-align: center;
                vertical-align: top;
                width: 50%;
            }
            .signature-box {
                margin-top: 15px;
                height: 80px;
                margin-bottom: 10px;
            }
            .signature-name {
                font-weight: bold;
                text-decoration: underline;
                margin-top: 5px;
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
        <div class="header">
            <h3>LAPORAN PERTANGGUNG JAWABAN</h3>
            <h3>KPS-PAMS DESA TENGGERLOR</h3>
        </div>
        
        <div class="periode">Periode: ${periodeText}</div>
        
        ${tablesHTML}
        
        <div class="notes">
            <p><strong>NB:</strong></p>
            <p>1. Saldo akhir ${end.monthName} adalah profit bersih di tahun ${end.year}</p>
            <p>2. Profit bersih tersebut telah diserahkan pada Bendahara Bumdes secara <strong>TUNAI (${new Intl.NumberFormat('id-ID').format(saldoAkhir)})</strong></p>
            <p>3. Jumlah pasang baru periode ini: ${jumlahPasangBaru} pelanggan</p>
        </div>
        
        <table class="signature-table">
            <tr>
                <td>
                </td>
                <td><p>Tenggerlor,___${end.monthName} ${end.year}</p>
                </td>
            </tr>
            <tr>
                <td>
                    <p>Ketua KPS PAMS</p>
                    <div class="signature-box"></div>
                    <br><br>
                    <p class="signature-name">KASTUBI</p>
                </td>
                <td>                    
                    <p>Bendahara KPS PAMS</p>
                    <div class="signature-box"></div>
                    <br><br>
                    <p class="signature-name">ABDUL SHOLI</p>
                </td>
            </tr>
        </table>
    </body>
    </html>
    `;

    // Buat Blob dan download
    const blob = new Blob([wordTemplate], { type: 'application/msword' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Laporan_KPS_PAMS_${periodeText.replace(/\s/g, '_').replace(/-/g, '_')}.doc`;
    
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