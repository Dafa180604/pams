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
                            <label for="password_baru_confirmation" class="block text-gray-700">Ulangi Password
                                Baru</label>
                            <input type="password" id="password_baru_confirmation" name="password_baru_confirmation"
                                class="w-full mt-2 p-2 border @error('password_baru_confirmation') border-red-500 @else border-gray-300 @enderror rounded-lg">
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

<script>
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