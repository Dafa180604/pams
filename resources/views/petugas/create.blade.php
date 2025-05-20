@extends('layouts.master')
@section('title', 'petugas')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('petugas.index') }}">Data Master petugas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title fs-4 mb-4">Form Data Tambah petugas</h4>
                        <form action="{{ route('petugas.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama</label>
                                        <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama"
                                            value="{{ old('nama') }}">
                                        @error('nama')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Alamat</label>
                                        <textarea name="alamat" class="form-control"
                                            placeholder="Masukkan Alamat">{{ old('alamat') }}</textarea>
                                        @error('alamat')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">No HP</label>
                                        <input type="text" name="no_hp" class="form-control" placeholder="Masukkan Nomor HP"
                                            value="{{ old('no_hp') }}">
                                        @error('no_hp')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label class="form-label">RT</label>
                                                <input type="text" name="rt" class="form-control" placeholder="Masukkan RT"
                                                    value="{{ old('rt') }}">
                                                @error('rt')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label class="form-label">RW</label>
                                                <input type="text" name="rw" class="form-control" placeholder="Masukkan RW"
                                                    value="{{ old('rw') }}">
                                                @error('rw')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control"
                                            placeholder="Masukkan Username" value="{{ old('username') }}">
                                        <small class="text-muted">Password Default: PetugasPams</small>
                                        @error('username')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <div class="mt-2">
                                            <img id="previewFoto" src="" alt="Preview Foto"
                                                style="display: none; max-width: 200px;">
                                        </div>
                                        <label class="form-label">Unggah Foto </label>
                                        <input type="file" name="foto_profile" class="form-control" accept="image/*"
                                            onchange="previewImage(this)">
                                        @error('foto_profile')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- <div class="col-sm-6">
                                        <div class="mb-3">
                                            <label class="form-label">Golongan</label>
                                            <select name="golongan" class="form-control">
                                                <option value="">Pilih
                                                    Golongan</option>
                                                <option value="Bantuan" {{ old('golongan') == 'Bantuan' ? 'selected' : '' }}>
                                                    Bantuan</option>
                                                <option value="Berbayar" {{ old('golongan') == 'Berbayar' ? 'selected' : '' }}>
                                                    Berbayar</option>
                                            </select>
                                            @error('golongan')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div> -->

                            </div>

                            <!-- <div class="row"> -->
                            <!-- <div class="col-sm-6">
                                        <div class="mb-3">
                                            <label class="form-label">Jumlah Air</label>
                                            <input type="number" name="jumlah_air" class="form-control"
                                                placeholder="Masukkan Jumlah Air" value="{{ old('jumlah_air') }}">
                                            @error('jumlah_air')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div> -->
                            <!-- Upload Foto -->
                            <!-- <div class="col-sm-6">
                                        <div class="mb-3">
                                            <div class="mt-2">
                                                <img id="previewFoto" src="" alt="Preview Foto"
                                                    style="display: none; max-width: 200px;">
                                            </div>
                                            <label class="form-label">Unggah Foto </label>
                                            <input type="file" name="foto_profile" class="form-control" accept="image/*" onchange="previewImage(this)">
                                            @error('foto_profile')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div> -->

                            <div class="mt-3">
                                <a href="{{ route('petugas.index') }}" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Tambahkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewImage(input) {
            var preview = document.getElementById('previewFoto');

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
@endsection