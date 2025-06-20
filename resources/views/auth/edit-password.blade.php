@extends('layouts.master')
@section('title', 'Profile')
@section('content')
<div class="page-content">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-4xl w-full bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center text-gray-800">Perbarui Password</h2>
            <!-- Form Profil -->
            <div class="mt-6">
                <form action="{{ route('profile.updatePassword', $data->username) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Password Lama -->
                    <div class="mt-4">
                        <label for="password_lama" class="block text-gray-700">Password Lama</label>
                        <div class="relative mt-2">
                            <input type="password" id="password_lama" name="password_lama"
                                class="w-full p-2 pr-10 border @error('password_lama') border-red-500 @else border-gray-300 @enderror rounded-lg">
                            <button type="button" class="absolute top-1/2 right-3 transform -translate-y-1/2 toggle-password bg-white px-1 rounded" 
                                    data-target="password_lama">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        @error('password_lama')
                            <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Baru dan Konfirmasi -->
                    <div class="mt-4 flex space-x-4">
                        <div class="w-1/2">
                            <label for="password_baru" class="block text-gray-700">Password Baru</label>
                            <div class="relative mt-2">
                                <input type="password" id="password_baru" name="password_baru"
                                    class="w-full p-2 pr-10 border @error('password_baru') border-red-500 @else border-gray-300 @enderror rounded-lg">
                                <button type="button" class="absolute top-1/2 right-3 transform -translate-y-1/2 toggle-password bg-white px-1 rounded" 
                                        data-target="password_baru">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                            @error('password_baru')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="w-1/2">
                            <label for="password_baru_confirmation" class="block text-gray-700">Ulangi Password Baru</label>
                            <div class="relative mt-2">
                                <input type="password" id="password_baru_confirmation" name="password_baru_confirmation"
                                    class="w-full p-2 pr-10 border @error('password_baru_confirmation') border-red-500 @else border-gray-300 @enderror rounded-lg">
                                <button type="button" class="absolute top-1/2 right-3 transform -translate-y-1/2 toggle-password bg-white px-1 rounded" 
                                        data-target="password_baru_confirmation">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                            @error('password_baru_confirmation')
                                <div class="text-red-500 mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

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

<!-- Font Awesome untuk icon mata -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Object untuk menyimpan state toggle setiap field
        const toggleStates = {
            password_lama: false,
            password_baru: false,
            password_baru_confirmation: false
        };

        // Toggle password functionality
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                // Toggle state
                toggleStates[targetId] = !toggleStates[targetId];
                
                if (toggleStates[targetId]) {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Event listener untuk mempertahankan state toggle saat input berubah
        document.querySelectorAll('input[type="password"], input[type="text"]').forEach(input => {
            input.addEventListener('input', function() {
                const inputId = this.id;
                if (toggleStates.hasOwnProperty(inputId)) {
                    // Pertahankan type berdasarkan state yang tersimpan
                    if (toggleStates[inputId]) {
                        this.type = 'text';
                    } else {
                        this.type = 'password';
                    }
                    
                    // Update icon berdasarkan state
                    const button = document.querySelector(`[data-target="${inputId}"]`);
                    const icon = button.querySelector('i');
                    
                    if (toggleStates[inputId]) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        });
    });

    // Script untuk preview file (jika ada)
    if (document.getElementById('fileInput')) {
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
    }
</script>
@endsection