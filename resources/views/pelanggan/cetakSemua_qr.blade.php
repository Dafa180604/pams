<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Print styles untuk mengoptimalkan print */
        @media print {
            body {
                margin: 0;
                padding: 5px;
                font-size: 10px;
            }
            
            .container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .qr-item {
                page-break-inside: avoid;
                margin-bottom: 5px !important;
                padding: 5px !important;
            }
            
            .row {
                margin: 0 !important;
            }
            
            .col-md-2, .col-sm-3, .col-4 {
                padding: 2px !important;
            }
        }
        
        .qr-item {
            text-align: center;
            border: 0.5px solid #666;
            padding: 4px;
            border-radius: 3px;
            margin-bottom: 5px;
            background-color: white;
            box-sizing: border-box;
        }
        
        /* Ini bagian untuk mengatur ukuran QR Code */
        .qr-item .qr-code {
            margin: 5px 0;
        }
        
        .qr-item .qr-code svg {
            width: 85px !important;  /* Ukuran QR code diperbesar */
            height: 85px !important; /* Ukuran QR code diperbesar */
        }
        
        .qr-item .name {
            font-size: 11px;
            font-weight: bold;
            margin: 2px 0;
            line-height: 1.1;
        }
        
        .qr-item .id {
            font-size: 10px;
            color: #666;
            margin: 1px 0;
        }
        
        .qr-item .address {
            font-size: 7px;
            color: #888;
            margin: 1px 0;
            line-height: 1.0;
        }
        
        /* Responsive untuk berbagai ukuran layar */
        @media (min-width: 576px) {
            .qr-item .qr-code svg {
                width: 75px !important;
                height: 75px !important;
            }
        }
        
        @media (min-width: 768px) {
            .qr-item .qr-code svg {
                width: 80px !important;
                height: 80px !important;
            }
        }
        
        @media (min-width: 992px) {
            .qr-item .qr-code svg {
                width: 85px !important;
                height: 85px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-2 mb-2">
        <h2 class="text-center mb-3" style="font-size: 18px;">Daftar QR Code Pelanggan</h2>
        <div class="row g-1">
            @foreach($dataPelanggan as $pelanggan)
            <div class="col-md-2 col-sm-3 col-4">
                <div class="qr-item">
                    <div class="qr-code">
                        {!! $pelanggan->qrCode !!}
                    </div>
                    <div class="id">ID: {{ $pelanggan->id_users }}</div>
                    <div class="name">{{ $pelanggan->nama }}</div>
                    <div class="address">RW {{ $pelanggan->rw }}/RT {{ $pelanggan->rt }}</div>
                    <div class="address">{{ $pelanggan->alamat }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Wait for the page to fully load before triggering print
        window.onload = function() {
            // Small delay to ensure all content is properly rendered
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>