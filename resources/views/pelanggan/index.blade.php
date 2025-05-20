@extends('layouts.master')
@section('title', 'Pelanggan')
@section('content')
    <div class="page-content">

        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Pelanggan</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Pelanggan</h4>
                            </div>
                            <div
                                class="d-flex flex-column flex-md-row align-items-center flex-wrap text-center text-md-start">
                                <a href="{{ route('pelanggan.create') }}"
                                    class="btn btn-primary d-flex align-items-center justify-content-center mb-2 mb-md-0 me-0 me-md-3">
                                    <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                    <span class="ms-2">Tambah Data</span>
                                </a>
                                <button id="printSelectedQr"
                                    class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="btn-icon-prepend" data-feather="printer"></i>
                                    <span class="ms-2">Cetak QR Terpilih</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead>
                                    <tr>
                                        <th>NO.</th>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <!-- <th>id_users</th> -->
                                        <th>NAMA</th>
                                        <th>ALAMAT</th>
                                        <th>TELEPON</th>
                                        <th>GOLONGAN</th>
                                        <th>AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataPelanggan as $pelanggan)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>
                                                <input type="checkbox" class="pelanggan-checkbox"
                                                    value="{{ $pelanggan->id_users }}">
                                            </td>
                                            <!-- <td>{{$loop->iteration}}</td> -->
                                            <!-- <td>{{ $pelanggan->id_users }}</td> -->
                                            <td>{{ $pelanggan->nama }}</td>
                                            <td>
                                                <div>{{ $pelanggan->alamat }}</div>
                                                <div style="font-size: 0.9em; color: #666;">
                                                    <span>RT: {{ $pelanggan->rt }}</span> | <span>RW:
                                                        {{ $pelanggan->rw }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $pelanggan->no_hp }}</td>
                                            <td>{{ $pelanggan->golongan }}</td>
                                            <td>
                                                <a href="{{ route('pelanggan.show', $pelanggan->id_users) }}"
                                                    class="btn btn-info">Detail</a>
                                                <a href="{{ route('pelanggan.edit', $pelanggan->id_users) }}"
                                                    class="btn btn-warning">Edit</a>
                                                <form action="{{ route('pelanggan.destroy', $pelanggan->id_users) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="delete-user-button btn btn-danger btn-sm"
                                                        data-id-users="{{ $pelanggan->id_users }}"
                                                        data-nama="{{ $pelanggan->nama }}">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Select all checkbox
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.pelanggan-checkbox');

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });

            // Print selected QR codes
            // Print selected QR codes
            const printButton = document.getElementById('printSelectedQr');
            printButton.addEventListener('click', function () {
                const selectedIds = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                if (selectedIds.length === 0) {
                    // Use SweetAlert or a custom modal
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Pilih minimal satu pelanggan untuk mencetak QR Code.'
                    });
                    return false;
                }

                const url = "{{ route('pelanggan.cetakSemuaQr') }}";
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.target = '_blank'; // This opens in a new window/tab

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const idsInput = document.createElement('input');
                idsInput.type = 'hidden';
                idsInput.name = 'ids';
                idsInput.value = JSON.stringify(selectedIds);
                form.appendChild(idsInput);

                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
@endsection