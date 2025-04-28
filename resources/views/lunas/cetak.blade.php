<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Transaksi - PAMSIMAS</title>
    <style>
        @page {
            size: 58mm;
            margin: 2mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 5px;
            width: 100%;
            max-width: 220px;
            color: #000;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1.4;
        }

        .header,
        .footer {
            text-align: center;
        }

        .logo {
            font-size: 40px;
            margin-bottom: 5px;
        }

        h1 {
            font-size: 14pt;
            font-weight: 700;
            margin: 3px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 9pt;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            padding: 3px 0;
            font-size: 10pt;
            vertical-align: top;
        }

        th {
            text-align: left;
            width: 50%;
            color: #000;
            font-weight: 700;
        }

        td {
            text-align: right;
            color: #000;
            font-weight: 700;
        }

        .dotted-line {
            border-top: 2px dotted #000;
            margin: 8px 0;
        }

        .total {
            font-weight: 700;
            font-size: 12pt;
        }

        .subtable {
            margin: 5px 0;
            font-size: 10pt;
        }

        .subtable th {
            font-weight: 700;
            width: 70%;
        }

        .subtable td {
            text-align: right;
            width: 30%;
            font-weight: 700;
        }

        .footer {
            font-size: 9pt;
            margin-top: 10px;
            border-top: 2px dashed #000;
            padding-top: 8px;
        }

        .footer-text {
            font-weight: 700;
            font-size: 10pt;
        }

        .payment-details {
            margin-top: 8px;
        }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 2mm;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">💧</div>
        <h1>PAMSIMAS</h1>
        <p>Dsn.Watuduwur Ds.Tenggerlor</p>
        <p>Kec.Kunjang Kab.Kediri</p>
        <p>Telp: +62 857-8599-5219</p>
    </div>

    <div class="dotted-line"></div>

    <table>
        <tr>
            <th>ID Transaksi</th>
            <td>{{ $dataTransaksi->id_transaksi }}</td>
        </tr>
        <tr>
            <th>Nama</th>
            <td>{{ optional($dataTransaksi->pemakaian->users()->withTrashed()->first())->nama ?? $dataTransaksi->pemakaian->users->nama ?? 'Pengguna dihapus' }}
            </td>
        </tr>
        <tr>
            <th>Meter Awal</th>
            <td>{{ $dataTransaksi->pemakaian->meter_awal }} m³</td>
        </tr>
        <tr>
            <th>Meter Akhir</th>
            <td>{{ $dataTransaksi->pemakaian->meter_akhir }} m³</td>
        </tr>
        <tr>
            <th>Jumlah Pemakaian</th>
            <td>{{ $dataTransaksi->pemakaian->jumlah_pemakaian }} m³</td>
        </tr>
    </table>

    <div class="dotted-line"></div>

    <div>
        <p style="margin: 3px 0; font-weight: 900; font-size: 11pt;">Rincian Biaya:</p>
        <table class="subtable">
            @php
                // Mengambil dan decode data detail_biaya
                $detailBiaya = json_decode($dataTransaksi->detail_biaya ?? '{}', true);
                $beban = $detailBiaya['beban'] ?? ['tarif' => 0];
                $kategoriList = $detailBiaya['kategori'] ?? [];
            @endphp

            <!-- Tampilkan biaya beban -->
            <tr>
                <th>Beban</th>
                <td>Rp{{ number_format($beban['tarif'], 0, ',', '.') }}</td>
            </tr>

            <!-- Tampilkan semua kategori yang digunakan dari data snapshot -->
            @foreach($kategoriList as $kategori)
                <tr>
                    <th class="text-muted">{{ $kategori['volume'] }} m³ × Rp
                        {{ number_format($kategori['tarif'], 0, ',', '.') }}
                    </th>
                    <td class="text-end">Rp {{ number_format($kategori['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <th class="text-muted">Denda</th>
                <td class="text-end">
                    @php
                        $denda = $detailBiaya['denda'] ?? null;
                        $dendaAmount = $denda ? $denda['rp_denda'] : 0;
                    @endphp
                    Rp {{ number_format($dendaAmount, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="dotted-line"></div>

    <table>
        <tr class="total">
            <th>Total Tagihan</th>
            <td>Rp {{ number_format($dataTransaksi->jumlah_rp, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="payment-details">
        <table>
            <tr>
                <th>Uang Bayar</th>
                <td>Rp {{ number_format($dataTransaksi->uang_bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Kembalian</th>
                <td>Rp {{ number_format($dataTransaksi->kembalian, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $dataTransaksi->status_pembayaran }}</td>
            </tr>
            <tr>
                <th>Tanggal Bayar</th>
                <td>{{ $dataTransaksi->tgl_pembayaran ? date('d/m/Y H:i', strtotime($dataTransaksi->tgl_pembayaran)) : '-' }}
                </td>
            </tr>
            <tr>
                <th>Petugas</th>
                <td>{{ $dataTransaksi->pemakaian->petugas }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p class="footer-text">Terima Kasih Telah Membayar Tagihan Anda</p>
        <p>Semoga Pelayanan Kami Memuaskan</p>
    </div>

    <script>
        // Auto-print when document loads
        window.onload = function () {
            window.print();

            // Listen for print dialog closing
            window.addEventListener('afterprint', function () {
                // Optional: You can close the window after printing
                // window.close();
            });
        };
    </script>
</body>

</html>