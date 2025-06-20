@extends('layouts.masterlogin')

@section('title', 'Login')

@section('content')
    <div class="row w-100 mx-0 auth-page">
        <div class="col-md-8 col-xl-6 mx-auto">
            <div class="card">
                <div class="row">
                    <div class="col-md-4 pe-md-0">
                        <div class="auth-side-wrapper d-flex align-items-center justify-content-center"
                            style="background: #6571FF; min-height: 400px;">
                            <div class="text-center">
                                <div class="logo-container">
                                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo PAMSIMAS"
                                        class="img-fluid logo-responsive">
                                </div>
                                <div class="logo-text mt-3">
                                    <h4 class="text-white mb-0">PAMSIMAS</h4>
                                    <p class="text-white-50 small">Desa Tenggerlor</p>
                                </div>
                            </div>
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
                                <div class="mb-3 position-relative">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            autocomplete="current-password" placeholder="Masukkan Password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <button type="submit"
                                        class="btn btn-primary me-2 mb-2 mb-md-0 text-white">Login</button>
                                    <a href="{{ route('auth.lupa-password') }}"
                                        class="text-muted text-decoration-none forgot-password-link">
                                        <small>Lupa Username/Password?</small>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS untuk responsif dan styling -->
    <style>
        .logo-responsive {
            max-width: 120px;
            height: auto;
            background: #6571FF;
            border-radius: 50%;
            padding: 10px;
            backdrop-filter: blur(5px);
            border: 2px solid #6571FF;
        }

        .logo-container {
            background: #6571FF;
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid #6571FF;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .auth-side-wrapper {
            border-radius: 0.375rem 0 0 0.375rem;
        }

        .forgot-password-link:hover {
            color: #6571FF !important;
            text-decoration: underline !important;
        }

        /* Styling untuk toggle password button */
        #togglePassword {
            border-left: 0;
            background: transparent;
            color: #6c757d;
            border-color: #ced4da;
        }

        #togglePassword:hover {
            background: transparent;
            border-color: #ced4da;
        }

        #togglePassword:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group .form-control:focus {
            border-right: 0;
            box-shadow: none;
            border-color: #ced4da;
        }

        .input-group .form-control:focus + .btn {
            border-color: #ced4da;
        }

        /* Responsif untuk mobile */
        @media (max-width: 767.98px) {
            .auth-side-wrapper {
                min-height: 200px !important;
                border-radius: 0.375rem 0.375rem 0 0;
            }

            .logo-responsive {
                max-width: 80px;
                background: #6571FF;
                padding: 8px;
            }

            .logo-container {
                padding: 15px !important;
            }

            .logo-text h4 {
                font-size: 1.2rem;
            }

            .logo-text p {
                font-size: 0.8rem;
            }

            .auth-form-wrapper {
                padding: 2rem 1.5rem !important;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: stretch !important;
            }

            .forgot-password-link {
                text-align: center;
                margin-top: 1rem;
            }

            #togglePassword {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }

        /* Untuk tablet */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .logo-responsive {
                max-width: 100px;
            }

            .logo-container {
                padding: 18px;
            }
        }

        /* Untuk desktop */
        @media (min-width: 992px) {
            .logo-responsive {
                max-width: 140px;
            }

            .logo-container {
                padding: 22px;
            }
        }
    </style>

    <!-- Font Awesome untuk icon mata -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- JavaScript untuk toggle password -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePassword.addEventListener('click', function() {
                // Toggle tipe input password
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon mata
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        });

        // Script untuk error message
        @if(session('errorlogin'))
            document.addEventListener('DOMContentLoaded', function() {
                const errorMessage = document.getElementById('custom-error-message');
                if (errorMessage) {
                    setTimeout(function() {
                        errorMessage.style.display = 'none';
                    }, 3000);
                }
            });
        @endif
    </script>

@endsection