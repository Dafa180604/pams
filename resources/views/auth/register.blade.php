@extends('layouts.masterlogin')

@section('title', 'Login')

@section('content')

<div class="main-wrapper">
    <div class="page-wrapper full-page">
        <div class="page-content d-flex align-items-center justify-content-center">

            <div class="row w-100 mx-0 auth-page">
                <div class="col-md-8 col-xl-6 mx-auto">
                    <div class="card">
                        <div class="row">
                            <div class="col-md-4 pe-md-0">
                                <div class="auth-side-wrapper" style="background: #6571FF;">

                                </div>
                            </div>
                            <div class="col-md-8 ps-md-0">
                                <div class="auth-form-wrapper px-4 py-5">
                                    <a href="#" class="noble-ui-logo d-block mb-2">PEMSIMAS<span> Dusun
                                            Watuduwur</span></a>
                                    <h5 class="text-muted fw-normal mb-4">Membuat Akun Petugas.</h5>
                                    <form class="forms-sample" action="{{ route('create') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama</label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                autocomplete="nama" placeholder="Masukkan Nama">
                                            @error('nama')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label">Alamat</label>
                                            <input type="text" class="form-control" id="alamat" name="alamat"
                                                autocomplete="alamat" placeholder="Masukkan Alamat">
                                            @error('alamat')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Masukkan Email">
                                            @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="nomorHP" class="form-label">Nomor HP</label>
                                            <input type="tel" class="form-control" id="nomorHP" name="nomorHP"
                                                autocomplete="tel" placeholder="Masukkan Nomor HP" maxlength="13">
                                            @error('nomorHP')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label class="form-label">Unggah Foto <span
                                                        class="text-danger">*</span></label>
                                                <input type="file" name="foto_profile" class="form-control"
                                                    accept="image/*" capture="camera" required>
                                                <small class="text-muted">Hanya mendukung file gambar (jpeg, png, jpg).
                                                    Ukuran
                                                    maksimum 2MB.</small>
                                                @error('foto_profile')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="userPassword"
                                                name="password" placeholder="Password">
                                            @error('password')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div>
                                            <button type="submit"
                                                class="btn btn-primary me-2 mb-2 mb-md-0 text-white">Register</button>
                                        </div>
                                        <a href="{{ route("login")}}" class="d-block mt-3 text-muted">Sudah Memiliki
                                            Akun? Sign in</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>