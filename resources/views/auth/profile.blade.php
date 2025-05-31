@extends('layouts.master')
@section('title', 'Profile')
@section('content')
<div class="page-content">
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-4xl w-full bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-center text-gray-800">Data Profil</h2>

        <!-- Form Profil -->
        <div class="mt-6">
            <form action="#" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Foto Profile -->
                <div class="flex justify-center">
                    <div class="relative">
                        <!-- Menampilkan gambar profil -->
                        <img id="profile-image" class="w-32 h-32 rounded-full border-2 border-gray-300 cursor-pointer"
                        src="{{ strpos($data->foto_profile, 'storage.googleapis.com') !== false ? $data->foto_profile : asset('storage/' . $data->foto_profile) }}"
                            alt="Foto Profil" onclick="openImage(this.src)">
                        <!-- <input type="file" id="fileInput" class="absolute bottom-0 right-0 opacity-0 cursor-pointer" />
                        <label for="fileInput"
                            class="absolute bottom-0 right-0 p-2 bg-blue-500 text-white rounded-full cursor-pointer">
                            <i data-feather="camera"></i>
                        </label> -->
                    </div>
                </div>

                <!-- Nama -->
                <div class="mt-4">
                    <label for="nama" class="block text-gray-700">Nama</label>
                    <input type="text" id="nama" name="nama" class="w-full mt-2 p-2 border border-gray-300 rounded-lg"
                        value="{{ $data->nama }}" disabled>
                </div>

                <!-- Alamat -->
                <div class="mt-4">
                    <label for="alamat" class="block text-gray-700">Alamat</label>
                    <input type="text" id="alamat" name="alamat"
                        class="w-full mt-2 p-2 border border-gray-300 rounded-lg" value="{{ $data->alamat }}" disabled>
                </div>

                <!-- Email -->
                <div class="mt-4">
                    <label for="email" class="block text-gray-700">Username</label>
                    <div class="relative flex items-center space-x-2">
                        <input type="text" id="username" name="username"
                            class="w-full mt-2 p-2 border border-gray-300 rounded-lg pr-20" 
                            value="{{ $data->username }}" disabled>
                    </div>
                </div>

                <!-- Nomor HP -->
                <div class="mt-4">
                    <label for="nomorHP" class="block text-gray-700">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp"
                        class="w-full mt-2 p-2 border border-gray-300 rounded-lg" value="{{ $data->no_hp }}" disabled>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<!-- Image Popup -->
<div id="imagePopup" class="popup-overlay" style="display: none;">
    <span class="close-btn" onclick="closeImage()">×</span>
    <img id="largeImage" src="" alt="Large Image">
</div>

<style>
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    #largeImage {
        max-width: 90%;
        max-height: 90%;
        border: 2px solid white;
        border-radius: 8px;
    }
    .close-btn {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 40px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        transition: color 0.3s ease;
    }
    .close-btn:hover {
        color: #ccc;
    }
</style>

<!-- JavaScript untuk membuka dan menutup gambar -->
<script>
    function openImage(src) {
        // Menampilkan gambar besar
        document.getElementById('largeImage').src = src;
        document.getElementById('imagePopup').style.display = 'flex';
    }
    
    function closeImage() {
        // Menutup popup gambar
        document.getElementById('imagePopup').style.display = 'none';
    }
    
    // Menutup popup dengan menekan tombol Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImage();
        }
    });
    
    // Menutup popup dengan mengklik area di luar gambar
    document.getElementById('imagePopup').addEventListener('click', function(event) {
        if (event.target === this) {
            closeImage();
        }
    });
    
    document.addEventListener('DOMContentLoaded', function () {
        const filterInputs = document.querySelectorAll('input[name="end_date"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function () {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection