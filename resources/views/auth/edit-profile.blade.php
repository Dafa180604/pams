@extends('layouts.master')
@section('title', 'Profile')
@section('content')
<div class="page-content">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-4xl w-full bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center text-gray-800">Perbarui Profil</h2>
            <!-- Form Profil -->
            <div class="mt-6">
                <form action="{{ route('profile.update', $data->username) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Foto Profile -->
                    <div class="flex justify-center">
                        <div class="relative">
                            <img id="profile-image" class="w-32 h-32 rounded-full border-2 border-gray-300 object-cover cursor-pointer"
                                src="{{ strpos($data->foto_profile, 'storage.googleapis.com') !== false ? $data->foto_profile : asset('storage/' . $data->foto_profile) }}"
                                alt="Profile Photo" onclick="openImage(this.src)">
                            <input type="file" id="fileInput" name="foto_profile" class="hidden" accept="image/*" />
                            <label for="fileInput"
                                class="absolute bottom-0 right-0 p-2 bg-blue-500 text-white rounded-full cursor-pointer">
                                <i data-feather="camera"></i>
                            </label>
                        </div>
                    </div>
                    @error('foto_profile')
                        <div class="text-red-500 mt-1 text-center text-sm">{{ $message }}</div>
                    @enderror

                    <!-- Nama -->
                    <div class="mt-4">
                        <label for="nama" class="block text-gray-700">Nama</label>
                        <input type="text" id="nama" name="nama"
                            class="w-full mt-2 p-2 border @error('nama') border-red-500 @else border-gray-300 @enderror rounded-lg"
                            value="{{ old('nama', $data->nama) }}">
                        @error('nama')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Username -->
                    <div class="mt-4">
                        <label for="username" class="block text-gray-700">Username</label>
                        <input type="text" id="username" name="username"
                            class="w-full mt-2 p-2 border @error('username') border-red-500 @else border-gray-300 @enderror rounded-lg"
                            value="{{ old('username', $data->username) }}">
                        @error('username')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Alamat -->
                    <div class="mt-4">
                        <label for="alamat" class="block text-gray-700">Alamat</label>
                        <input type="text" id="alamat" name="alamat"
                            class="w-full mt-2 p-2 border @error('alamat') border-red-500 @else border-gray-300 @enderror rounded-lg"
                            value="{{ old('alamat', $data->alamat) }}">
                        @error('alamat')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- RW & RT -->
                    <div class="mt-4 flex space-x-4">
                        <div class="w-1/2">
                            <label for="rw" class="block text-gray-700">RW</label>
                            <input type="text" id="rw" name="rw"
                                class="w-full mt-2 p-2 border @error('rw') border-red-500 @else border-gray-300 @enderror rounded-lg"
                                value="{{ old('rw', $data->rw) }}">
                            @error('rw')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="w-1/2">
                            <label for="rt" class="block text-gray-700">RT</label>
                            <input type="text" id="rt" name="rt"
                                class="w-full mt-2 p-2 border @error('rt') border-red-500 @else border-gray-300 @enderror rounded-lg"
                                value="{{ old('rt', $data->rt) }}">
                            @error('rt')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Nomor HP -->
                    <div class="mt-4">
                        <label for="no_hp" class="block text-gray-700">Nomor HP</label>
                        <input type="text" id="no_hp" name="no_hp"
                            class="w-full mt-2 p-2 border @error('no_hp') border-red-500 @else border-gray-300 @enderror rounded-lg"
                            value="{{ old('no_hp', $data->no_hp) }}">
                        @error('no_hp')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Password Section
                    <div class="mt-6 border-t pt-4">
                        <h3 class="text-lg font-medium text-gray-800">Ubah Password (Opsional)</h3>

                        <div class="mt-4">
                            <label for="password_lama" class="block text-gray-700">Password Lama</label>
                            <input type="password" id="password_lama" name="password_lama"
                                class="w-full mt-2 p-2 border @error('password_lama') border-red-500 @else border-gray-300 @enderror rounded-lg">
                            @error('password_lama')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 flex space-x-4">
                            <div class="w-1/2">
                                <label for="password_baru" class="block text-gray-700">Password Baru</label>
                                <input type="password" id="password_baru" name="password_baru"
                                    class="w-full mt-2 p-2 border @error('password_baru') border-red-500 @else border-gray-300 @enderror rounded-lg">
                                @error('password_baru')
                                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-1/2">
                                <label for="ulang_password" class="block text-gray-700">Ulangi Password Baru</label>
                                <input type="password" id="ulang_password" name="ulang_password"
                                    class="w-full mt-2 p-2 border @error('ulang_password') border-red-500 @else border-gray-300 @enderror rounded-lg">
                                @error('ulang_password')
                                    <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div> -->

                    <!-- Update Button -->
                    <div class="mt-6 flex justify-center">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            Simpan Perubahan
                        </button>
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

<script>
    // Image popup functions
    function openImage(src) {
        document.getElementById('largeImage').src = src;
        document.getElementById('imagePopup').style.display = 'flex';
    }
    
    function closeImage() {
        document.getElementById('imagePopup').style.display = 'none';
    }
    
    // Close popup with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImage();
        }
    });
    
    // Close popup by clicking outside the image
    document.getElementById('imagePopup').addEventListener('click', function(event) {
        if (event.target === this) {
            closeImage();
        }
    });

    // File input preview functionality
    document.getElementById('fileInput').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('profile-image').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection