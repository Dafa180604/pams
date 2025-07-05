<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Responsive HTML Admin Dashboard Template based on Bootstrap 5">
    <meta name="author" content="NobleUI">
    <meta name="keywords"
        content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <title>PAMSIMAS | @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- End fonts -->

    <!-- core:css -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/core/core.css') }}">
    <!-- endinject -->

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
    <!-- End plugin css for this page -->

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/flatpickr/flatpickr.min.css') }}">
    <!-- End plugin css for this page -->

    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather-font/css/iconfont.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <!-- endinject -->

    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/demo1/style.css') }}">
    <!-- End layout styles -->

    <link rel="shortcut icon" href="{{ asset('assets/images/air.png') }}" />
    {{-- tailwind --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="main-wrapper">

        <!-- sidebar -->
        @include('layouts.sidebar')
        <!-- end sidebar -->

        <!--  -->

        <div class="page-wrapper">

            <!-- navbar -->
            @include('layouts.navbar')
            <!-- end navbar -->

            <!-- Content -->
            @yield('content')
            <!-- End Content -->

            <!-- partial:partials/_footer.html -->
            <footer
                class="footer d-flex flex-column flex-md-row align-items-center justify-content-between px-4 py-3 border-top small">
                <p class="text-muted mb-1 mb-md-0">Copyright © 2024 <a href="https://www.nobleui.com"
                        target="_blank">PAMSIMAS</a>.</p>
                <!-- <p class="text-muted">Handcrafted With <i class="mb-1 text-primary ms-1 icon-sm"
                        data-feather="heart"></i></p> -->
            </footer>
            <!-- partial -->

        </div>
    </div>

    <!-- core:js -->
    <script src="{{ asset('assets/vendors/core/core.js') }}"></script>
    <!-- endinject -->

    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
    <!-- End plugin js for this page -->

    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.min.js')}}"></script>
    <script src="{{ asset('assets/vendors/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <!-- End plugin js for this page -->

    <!-- inject:js -->
    <script src="{{ asset('assets/vendors/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <!-- endinject -->

    <!-- Custom js for this page -->
    <script src="{{ asset('assets/js/sweet-alert.js')}}"></script>
    <script src="{{ asset('assets/js/data-table.js') }}"></script>

    <script src="{{ asset('assets/js/flatpickr.js')}}"></script>
    <!-- End custom js for this page -->

    <!-- Custom js for this page -->
    <script src="{{ asset('assets/js/dashboard-light.js') }}"></script>
    <!-- End custom js for this page -->

    <script>
        @if(session('delete'))
            Swal.fire(
                'Terhapus!',
                'Data berhasil dihapus.',
                'success'
            );
        @elseif(session('success'))
            Swal.fire(
                'Berhasil!',
                'Data berhasil ditambahkan.',
                'success'
            );
        @elseif(session('successPassword'))
            Swal.fire(
                'Berhasil!',
                'Password berhasil diganti.',
                'success'
            );
        @elseif(session('successupdateprofile'))
            Swal.fire(
                'Berhasil!',
                'Profil Berhasil Diperbarui.',
                'success'
            );
        @elseif(session('update'))
            Swal.fire(
                'Berhasil!',
                'Data berhasil diperbarui.',
                'success'
            );
        @elseif(session('berhasil'))
            Swal.fire(
                'Berhasil!',
                'Penugasan Berhasil diPerbarui.',
                'success'
            );
        @elseif(session('successlogin'))
            Swal.fire({
                title: 'Berhasil Masuk!',
                html: '{!! session("successlogin") !!}',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        @elseif(session('error'))
            Swal.fire({
                title: 'Gagal Masuk!',
                text: '{{ session("error") }}',
                icon: 'error',
                confirmButtonText: 'OK'
            }); 
        @elseif(session('pembayaran_berhasil'))
            Swal.fire({
                title: 'Pembayaran Berhasil!',
                html: 'Transaksi telah diproses',
                icon: 'success',
                confirmButtonColor: '#6366f1'
            });
        @endif
        document.querySelectorAll('.delete-user-button').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                // Get user data from button attributes
                const nama = this.getAttribute('data-nama');
                const idUsers = this.getAttribute('data-id-users');

                if (!nama || !idUsers) {
                    console.error('Data pelanggan tidak ditemukan');
                    return;
                }

                // SweetAlert Confirmation
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    html: `Apakah anda yakin ingin menghapus pelanggan <strong>${nama}</strong>?`,
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonClass: 'me-2',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Tidak, batalkan!',
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger me-2'
                    },
                    buttonsStyling: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the parent form
                        this.closest('form').submit();
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire({
                            title: 'Dibatalkan',
                            text: 'Data tidak jadi dihapus :)',
                            icon: 'error'
                        });
                    }
                });
                //
                Swal.fire({
                    title: 'Konfirmasi Perubahan Status',
                    html: `Apakah Anda yakin ingin mengubah status menjadi <strong>"Belum Diterima"</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonClass: 'me-2',
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-light'
                    },
                    buttonsStyling: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form atau jalankan aksi perubahan status di sini
                        this.closest('form').submit();
                    }
                });

            });
        });
    </script>
</body>

</html>