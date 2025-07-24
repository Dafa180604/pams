@extends('layouts.master')
@section('title', 'Edit Pelanggan')
@section('content')
<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('pelanggan.index') }}">Data Master Pelanggan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title fs-4 mb-4">Form Edit Data Pelanggan</h4>
                    <form action="{{ route('pelanggan.update', $data->id_users) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama" 
                                        value="{{ old('nama', $data->nama) }}">
                                    @error('nama')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <select name="alamat" class="form-control">
                                        <option value="">Pilih Alamat</option>
                                        <option value="Watuduwur" {{ old('alamat', $data->alamat) == 'Watuduwur' ? 'selected' : '' }}>Watuduwur</option>
                                        <option value="Pulorjo" {{ old('alamat', $data->alamat) == 'Pulorjo' ? 'selected' : '' }}>Pulorjo</option>
                                        <option value="Babadan" {{ old('alamat', $data->alamat) == 'Babadan' ? 'selected' : '' }}>Babadan</option>
                                        <option value="Tenggerlor" {{ old('alamat', $data->alamat) == 'Tenggerlor' ? 'selected' : '' }}>Tenggerlor</option>
                                        <option value="Wangkal" {{ old('alamat', $data->alamat) == 'Wangkal' ? 'selected' : '' }}>Wangkal</option>
                                    </select>
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
                                        value="{{ old('no_hp', $data->no_hp) }}">
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
                                            <select name="rt" class="form-control">
                                                <option value="">Pilih RT</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                    <option value="{{ $i }}" {{ old('rt', $data->rt) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                            @error('rt')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-3">
                                            <label class="form-label">RW</label>
                                            <select name="rw" class="form-control">
                                                <option value="">Pilih RW</option>
                                                @for($i = 1; $i <= 10; $i++)
                                                    <option value="{{ $i }}" {{ old('rw', $data->rw) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
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
                                    <input type="text" name="username" class="form-control" placeholder="Masukkan Username" 
                                        value="{{ old('username', $data->username) }}">
                                    @error('username')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password Baru (Opsional)">
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Golongan</label>
                                    <select name="golongan" class="form-control">
                                        <option value="Bantuan" {{ old('golongan', $data->golongan) == 'Bantuan' ? 'selected' : '' }}>Bantuan</option>
                                        <option value="Berbayar" {{ old('golongan', $data->golongan) == 'Berbayar' ? 'selected' : '' }}>Berbayar</option>
                                    </select>
                                    @error('golongan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Air</label>
                                    <input type="number" name="jumlah_air" class="form-control" placeholder="Masukkan Jumlah Air" 
                                        value="{{ old('jumlah_air', $data->jumlah_air) }}">
                                    @error('jumlah_air')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                                                <div class="mt-3">
                            <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection