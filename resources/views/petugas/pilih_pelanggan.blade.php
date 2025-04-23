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
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                        <h4 class="mb-3 mb-md-2">Pilih Pelanggan untuk Petugas: {{ $petugas->nama }}</h4>
                    </div>
                    {{-- Filter Form --}}
                    <form method="GET" action="" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <select name="alamat" id="alamat" class="form-control">
                                    <option value="">Semua Alamat</option>
                                    @foreach($alamatList as $alamat)
                                        <option value="{{ $alamat }}" {{ $filterAlamat == $alamat ? 'selected' : '' }}>
                                            {{ $alamat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="rw" class="form-label">RW</label>
                                <select name="rw" id="rw" class="form-control">
                                    <option value="">Semua RW</option>
                                    @foreach($rwList as $rw)
                                        <option value="{{ $rw }}" {{ $filterRW == $rw ? 'selected' : '' }}>
                                            {{ $rw }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="rt" class="form-label">RT</label>
                                <select name="rt" id="rt" class="form-control">
                                    <option value="">Semua RT</option>
                                    @foreach($rtList as $rt)
                                        <option value="{{ $rt }}" {{ $filterRT == $rt ? 'selected' : '' }}>
                                            {{ $rt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    {{-- Table Pelanggan --}}
                    <form action="{{ route('petugas.updateAkses', $petugas->id_users) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table">
                            <table id="dataTableExample" class="table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>ID Pelanggan</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>RW</th>
                                        <th>RT</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataPelanggan as $index => $p)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $p->id_users }}</td>
                                            <td>{{ $p->nama }}</td>
                                            <td>{{ $p->alamat }}</td>
                                            <td>{{ $p->rw }}</td>
                                            <td>{{ $p->rt }}</td>
                                            <td>
                                                <input type="checkbox" name="pelanggan_ids[]" value="{{ $p->id_users }}"
                                                    {{ in_array($p->id_users, $aksesPelanggan) ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Data tidak ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Akses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ambil semua elemen input dan select yang ada dalam form filter
        const filterInputs = document.querySelectorAll('select[name="alamat"], select[name="rw"], select[name="rt"]');

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
    });

</script>
@endsection