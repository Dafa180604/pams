@extends('layouts.master')

@section('title', 'Riwayat Pemulihan')

@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="#">Pemulihan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Pemulihan</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <h4 class="mb-3 mb-md-2">Riwayat Pemulihan</h4>
                        </div>
                        
                        <!-- Informasi Total RP Pengampunan -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <i class="icon-md" data-feather="info"></i>
                                        <div class="ms-3">
                                            <h6 class="alert-heading mb-1">Total RP Pengampunan</h6>
                                            <p class="mb-0">
                                                <strong class="fs-4 text-primary">
                                                    Rp {{ number_format($totalRpPengampunan, 0, ',', '.') }}
                                                </strong>
                                                <span class="text-muted ms-2">({{ $dataTransaksi->count() }} transaksi)</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>ID Transaksi</th>
                                        <th>Nama Pelanggan</th>
                                        {{-- <th>Meter Awal</th>
                                        <th>Meter Akhir</th>
                                        <th>Jumlah Pemakaian</th> --}}
                                        <th>Jumlah RP</th>
                                        <th>RP Pengampunan</th>
                                        <th>Tanggal Pencatatan</th>
                                        <th>Tanggal Pembayaran</th>
                                        <th>Telat</th>
                                        <th>Yang Melunasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTransaksi as $data)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $data->id_transaksi }}</td>
                                            <td>{{ optional($data->pemakaian->users()->withTrashed()->first())->nama ?? 'Pengguna dihapus' }}</td>
                                            {{-- <td>{{ $data->pemakaian->meter_awal }}</td>
                                            <td>{{ $data->pemakaian->meter_akhir }}</td>
                                            <td>{{ $data->pemakaian->jumlah_pemakaian }}</td> --}}
                                            <td>Rp {{ number_format($data->jumlah_rp, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($data->rp_pengampunan ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $data->pemakaian->waktu_catat }}</td>
                                            <td>{{ $data->tgl_pembayaran }}</td>
<td>
    @php
        $waktuCatat = \Carbon\Carbon::parse($data->pemakaian->waktu_catat);
        $tglPembayaran = \Carbon\Carbon::parse($data->tgl_pembayaran);
        $selisihHari = round($tglPembayaran->diffInDays($waktuCatat, false));
        echo $selisihHari;
    @endphp
</td>
                                            <td>
                                                @if($data->pemakaian->petugas)
                                                    @php
                                                        $petugasIdArray = explode(',', $data->pemakaian->petugas);
                                                        $petugasNames = [];
                                                        foreach($petugasIdArray as $petugasId) {
                                                            $petugasId = trim($petugasId);
                                                            if(isset($petugasUsers[$petugasId])) {
                                                                $petugasNames[] = $petugasUsers[$petugasId]->nama;
                                                            } else {
                                                                // Mencari user di database meskipun sudah di-soft delete
                                                                $user = DB::table('users')->where('id_users', $petugasId)
                                                                                       ->first();
                                                                if($user) {
                                                                    $petugasNames[] = $user->nama;
                                                                } else {
                                                                    $petugasNames[] = 'ID: ' . $petugasId;
                                                                }
                                                            }
                                                        }
                                                        echo implode(', ', $petugasNames);
                                                    @endphp
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('lunas.show', $data->id_transaksi) }}" 
                                                   class="btn btn-info btn-sm me-2">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    {{-- <tr class="table-active">
                                        <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                                        <td class="fw-bold text-primary">
                                            Rp {{ number_format($totalRpPengampunan, 0, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr> --}}
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection