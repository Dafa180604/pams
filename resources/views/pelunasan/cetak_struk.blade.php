<!DOCTYPE html>
<html>
<head>
    <title>Struk Penggunaan Air - PAMSIMAS</title>
    <style>
        body {
            margin: 0;
            padding: 1px;
            font-family: Arial, sans-serif;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .receipt-card {
            border: 1px solid #000;
            padding: 15px;
            position: relative;
            page-break-inside: avoid;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            position: relative;
        }

        .header-title {
            font-weight: bold;
            margin: 0;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .header-subtitle {
            margin: 2px 0 0;
            font-size: 12px;
            color: #333;
        }

        .icon-container {
            position: absolute;
            left: 5px;
            top: 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .drop-icon {
            color: #4CAF50;
            font-size: 18px;
        }

        .content-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
            font-size: 12px;
        }

        .content-left,
        .content-right {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .content-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 20px;
            padding: 2px 0;
        }

        .label {
            font-weight: 500;
            color: #333;
            flex: 0 0 auto;
            min-width: 80px;
        }

        .value {
            text-align: right;
            flex: 1;
            font-weight: 600;
            color: #000;
        }

        .total-row {
            border-top: 1px solid #ccc;
            padding-top: 8px;
            margin-top: 8px;
        }

        .total-row .label {
            font-weight: bold;
            font-size: 13px;
        }

        .total-row .value {
            font-weight: bold;
            font-size: 14px;
            color: #2c5282;
        }

        .print-button {
            text-align: center;
            margin-bottom: 30px;
        }

        .print-button button {
            background: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .print-button button:hover {
            background: #0056b3;
        }

        .print-button button.close {
            background: #6c757d;
        }

        .print-button button.close:hover {
            background: #545b62;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .receipt-grid {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 10px;
            }
            
            .content-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-card {
                page-break-inside: avoid;
                box-shadow: none;
            }
            
            .print-button {
                display: none;
            }
            
            .receipt-grid {
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">🖨️ Cetak Struk</button>
        <button onclick="window.close()" class="close">✕ Tutup</button>
    </div>

    <div class="receipt-grid">
        @foreach ($dataTransaksi as $data)
        <div class="receipt-card">
            <div class="header">
                <div class="icon-container">
                    <span class="drop-icon">💧</span>
                </div>
                <p class="header-title">KPSPAMS "SUMBER SEJATI"</p>
                <p class="header-subtitle">Dsn. Watukumbur Ds. Tenggerlor</p>
                <p class="header-subtitle">Kec. Kunjang, Kab. Kediri</p>
                <p class="header-subtitle">Telp: +62 857-8599-5219</p>
            </div>

            <div class="content-container">
                <div class="content-left">
                    <div class="content-row">
                        <span class="label">ID Transaksi</span>
                        <span class="value">{{ $data->id_pemakaian }}</span>
                    </div>
                    <div class="content-row">
                        <span class="label">Nama Pelanggan</span>
                        <span class="value">{{ strtoupper($data->pemakaian->users->nama ?? 'N/A') }}</span>
                    </div>
                    <div class="content-row">
                        <span class="label">Tanggal Catat</span>
                        <span class="value">{{ \Carbon\Carbon::parse($data->pemakaian->waktu_catat)->format('d/m/Y') }}</span>
                    </div>
                    <div class="content-row">
                        <span class="label">Petugas</span>
                        <span class="value">
                            @if($data->pemakaian->petugas)
                                @php
                                    $petugasIds = explode(',', $data->pemakaian->petugas);
                                    $petugasNames = [];
                                    foreach($petugasIds as $petugasId) {
                                        $petugasId = trim($petugasId);
                                        $petugas = $petugasUsers->get($petugasId);
                                        if($petugas) {
                                            $petugasNames[] = $petugas->nama;
                                        }
                                    }
                                    echo implode(', ', $petugasNames) ?: 'Bustamil B.';
                                @endphp
                            @else
                                Bustamil B.
                            @endif
                        </span>
                    </div>
                    <div class="content-row">
                        <span class="label">Meter Awal - Akhir</span>
                        <span class="value">{{ number_format($data->pemakaian->meter_awal) }} m³ - {{ number_format($data->pemakaian->meter_akhir) }} m³</span>
                    </div>
                    <div class="content-row">
                        <span class="label">Jumlah Pemakaian</span>
                        <span class="value">{{ number_format($data->pemakaian->jumlah_pemakaian) }} m³</span>
                    </div>
                </div>

                <div class="content-right">
                    @if(isset($data->detail_biaya_parsed))
                        @php $detailBiaya = $data->detail_biaya_parsed; @endphp
                        
                        <!-- Biaya Beban -->
                        <div class="content-row">
                            <span class="label">Beban</span>
                            <span class="value">Rp{{ number_format($detailBiaya['beban']['tarif'], 0, ',', '.') }}</span>
                        </div>

                        <!-- Biaya Pemakaian per Kategori -->
                        @foreach($detailBiaya['kategori'] as $kategori)
                            <div class="content-row">
                                <span class="label">{{ $kategori['volume'] }}×Rp{{ number_format($kategori['tarif'], 0, ',', '.') }}</span>
                                <span class="value">Rp{{ number_format($kategori['subtotal'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        <!-- Denda -->
                        @if($detailBiaya['total_denda'] > 0)
                            <div class="content-row">
                                <span class="label">Denda</span>
                                <span class="value">Rp{{ number_format($detailBiaya['total_denda'], 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <!-- Total -->
                        <div class="content-row total-row">
                            <span class="label">Jumlah</span>
                            <span class="value">Rp{{ number_format($data->jumlah_rp, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="content-row total-row">
                            <span class="label">Jumlah</span>
                            <span class="value">Rp{{ number_format($data->jumlah_rp, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };

        // Handle print button
        document.querySelector('.print-button button').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>
</html>