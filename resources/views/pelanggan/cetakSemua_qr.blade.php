<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .qr-item {
            text-align: center;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .qr-item .qr-code {
            margin: 10px 0;
        }
        .qr-item .name {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-4">
        <h1 class="text-center mb-4">Daftar QR Code Pelanggan</h1>
        <div class="row">
            @foreach($dataPelanggan as $pelanggan)
            <div class="col-md-4 col-sm-6">
                <div class="qr-item">
                    <div class="qr-code">
                        {!! $pelanggan->qrCode !!}
                    </div>
                    <div class="name">ID: {{ $pelanggan->id_users    }}</div>
                    <div class="name">{{ $pelanggan->nama }}</div>
                    <div class="name">RW {{ $pelanggan->rw }}/RT {{ $pelanggan->rt }},{{ $pelanggan->alamat }}</div>
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