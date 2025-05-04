@extends('layouts.master')
@section('title', 'Petugas')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Data Master Petugas</li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
                            <div>
                                <h4 class="mb-3 mb-md-2">Data Petugas</h4>
                            </div>
                            <div class="d-flex align-items-center flex-wrap text-nowrap">
                                <a href="{{ route('petugas.create') }}" class="btn btn-primary d-flex align-items-center">
                                    <i class="btn-icon-prepend" data-feather="plus-square"></i>
                                    <span class="ms-2">Tambah Data</span>
                                </a>
                            </div>
                        </div>
                        <div class="flex justify-center mb-4">
                            <input type="text" id="searchInput" placeholder="Cari petugas..."
                                class="border rounded-lg p-2 w-1/2 focus:border-gray-400 focus:ring-0 focus:outline-none" />
                        </div>
                        <div class="flex flex-wrap justify-center">
                            @if($datapetugas->filter(fn($item) => $item->role === 'petugas')->isEmpty())
                                <div class="text-center w-full p-4">
                                    <p class="text-gray-500">Belum ada data petugas.</p>
                                </div>
                            @else
                                @foreach($datapetugas as $data)
                                    @if($data->role !== 'Admin')
                                        <div
                                            class="bg-white shadow-md rounded-lg overflow-hidden m-4 w-80 transition-transform transform hover:scale-105 cursor-pointer">
                                            <a href="{{ route('petugas.show', $data->id_users) }}">
                                                <div class="p-4">
                                                    <div class="flex items-center">
                                                    <img src="{{ $data->foto_profile ?? 'https://via.placeholder.com/100' }}" 
                                                        alt="Foto Profil" 
                                                        class="w-16 h-16 rounded-full border-2 border-gray-200 mr-4 object-cover">
                                                    <div>
                                                            <h2 class="text-xl font-semibold text-black">{{ $data->nama }}</h2>
                                                            <p class="text-gray-500">{{ $data->role }}</p>
                                                        </div>
                                                    </div>
                                                    <p class="text-black"><strong>Telepon:</strong> {{ $data->no_hp }}</p>
                                                    <p class="text-black"><strong>Alamat:</strong> RT {{ $data->rt }} RW
                                                        {{ $data->rw }}, {{ $data->alamat }}</p>
                                                </div>
                                                <div class="flex justify-end p-4 border-t">
                                                    <a href="{{ route('petugas.pilihPelanggan', ['id_users' => $data->id_users]) }}" class="bg-primary text-white py-2 px-3 rounded flex items-center mr-2"
                                                        title="Pilih Pelanggan">
                                                        <i data-feather="user-check" class="w-5 h-5"></i>
                                                    </a>
                                                    <a href="{{ route('petugas.edit', $data->id_users) }}"
                                                        class="bg-warning text-white py-2 px-3 rounded flex items-center mr-2">
                                                        <i data-feather="edit" class="w-5 h-5"></i>
                                                    </a>
                                                    <form action="{{ route('petugas.destroy', $data->id_users) }}" method="POST"
                                                        class="delete-user-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-danger text-white py-2 px-3 rounded delete-user-button flex items-center"
                                                            data-id-users="{{ $data->id_users }}"
                                                            data-nama="{{ $data->nama }}">
                                                            <i data-feather="trash" class="w-5 h-5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Ambil elemen input dan daftar petugas
        const searchInput = document.getElementById('searchInput');
        const petugasCards = document.querySelectorAll('.bg-white'); // Mengambil semua kartu petugas

        searchInput.addEventListener('input', function () {
            const searchTerm = searchInput.value.toLowerCase().trim(); // Ambil nilai input, ubah ke huruf kecil dan trim spasi

            // Menghilangkan tanda baca dari input pencarian
            const cleanedSearchTerm = searchTerm.replace(/[.,]/g, '');

            // Hanya lakukan pencarian jika panjang input >= 3
            if (cleanedSearchTerm.length >= 1) {
                petugasCards.forEach(card => {
                    const name = card.querySelector('h2').innerText.toLowerCase(); // Ambil nama petugas

                    // Menghilangkan tanda baca dari nama
                    const cleanedName = name.replace(/[.,]/g, '');

                    // Memecah istilah pencarian dan nama ke dalam array kata
                    const searchWords = cleanedSearchTerm.split(/\s+/);
                    const nameWords = cleanedName.split(/\s+/);

                    // Cek jika semua kata dalam pencarian ada dalam nama
                    const matches = searchWords.every(word =>
                        nameWords.some(nameWord => nameWord.startsWith(word))
                    );

                    // Tampilkan atau sembunyikan kartu berdasarkan pencocokan
                    card.style.display = matches ? '' : 'none';
                });
            } else {
                // Jika kurang dari 3 huruf, tampilkan semua kartu
                petugasCards.forEach(card => {
                    card.style.display = ''; // Tampilkan semua kartu
                });
            }
        });
    </script>
@endsection