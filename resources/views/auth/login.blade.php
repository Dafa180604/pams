@extends('layouts.masterlogin')

@section('title', 'Login')

@section('content')
    <div class="row w-100 mx-0 auth-page">
        <div class="col-md-8 col-xl-6 mx-auto">
            <div class="card">
                <div class="row">
                    <div class="col-md-4 pe-md-0">
                        <div class="auth-side-wrapper" style="background: #6571FF;">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo PAMSIMAS"
                                style="width: 350px; height: 350px; object-fit: cover; padding-right: 50px;">
                        </div>
                    </div>
                    <div class="col-md-8 ps-md-0">
                        <div class="auth-form-wrapper px-4 py-5">
                            <h2 class="noble-ui-logo d-block mb-2">KPSPAMS<span> DS.TENGGERLOR</span></h2>
                            <h5 class="text-muted fw-normal mb-4">Selamat Datang! Silahkan Log in ke akun anda.</h5>

                            <!-- Menampilkan pesan jika login berhasil atau gagal -->
                            @if(session('successlogin'))
                                <div class="alert alert-success" role="alert">
                                    {!! session('successlogin') !!}
                                </div>
                            @endif

                            @if(session('errorlogin'))
                                <div id="custom-error-message" class="alert alert-danger" role="alert">
                                    {{ session('errorlogin') }}
                                </div>
                            @endif

                            <form class="forms-sample" action="{{ route('loginsukses') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Masukkan User Name"
                                        value="{{ session('username') ?? old('username') }}">
                                    @error('username')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3 relative">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        autocomplete="current-password" placeholder="Masukkan Password">
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary me-2 mb-2 mb-md-0 text-white">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('errorlogin'))
        <script>
            // Menampilkan pesan error di elemen dengan ID 'custom-error-message'
            document.getElementById('custom-error-message').innerHTML = '{{ session('errorlogin') }}';

            // Menunggu 3 detik sebelum menghilangkan pesan
            setTimeout(function () {
                var errorMessage = document.getElementById('custom-error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none'; // Menghilangkan pesan
                }
            }, 3000); // 3000 ms = 3 detik
        </script>
    @endif
@endsection