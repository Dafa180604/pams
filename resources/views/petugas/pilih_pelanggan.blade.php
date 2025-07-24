@extends('layouts.master')
@section('title', 'Pilih Pelanggan')
@section('content')
    <div class="page-content">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('petugas.index') }}">Petugas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pilih Pelanggan</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <!-- Notification area for conflicts -->
                        <!-- Notification area for conflicts -->
                        @if (session('conflicts'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <h5 class="mb-2"><i class="icon fas fa-exclamation-triangle"></i> Peringatan!</h5>
                                <p><strong>Apakah anda yakin penugasan diganti?</strong> Terdapat area yang sudah ditugaskan
                                    ke petugas lain:</p>

                                <ul class="mb-3">
                                    @foreach (session('conflicts') as $conflict)
                                        <li>
                                            Area <strong>{{ $conflict['area_display'] }}</strong>
                                            ({{ $conflict['pelanggan_count'] }} pelanggan)
                                            saat ini ditugaskan ke petugas <strong>{{ $conflict['petugas_name'] }}</strong>
                                        </li>
                                    @endforeach
                                </ul>

                                <p>Jika anda melanjutkan, hak akses petugas lama akan dihapus dan diperbarui dengan hak
                                    akses yang baru.</p>

                                <form action="{{ route('petugas.updateAkses', $petugas->id_users) }}" method="POST">
                                    @csrf
                                    @foreach (session('new_assignments') as $id)
                                        <input type="hidden" name="pelanggan_ids[]" value="{{ $id }}">
                                    @endforeach
                                    <input type="hidden" name="confirm_reassign" value="1">
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-danger">Ya, Ganti Penugasan</button>
                                        <a href="{{ route('petugas.pilihPelanggan', $petugas->id_users) }}"
                                            class="btn btn-secondary ml-2">Batal</a>
                                    </div>
                                </form>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                            <h4 class="mb-3 mb-md-2">Pilih Tempat Pencatatan untuk Petugas: {{ $petugas->nama }}</h4>

                            <!-- Tombol Simpan Akses -->
                            <form action="{{ route('petugas.updateAkses', $petugas->id_users) }}" method="POST"
                                id="aksesPelangganForm">
                                @csrf
                                <!-- Input hidden untuk menyimpan state saat ini -->
                                <div id="hiddenInputsContainer">
                                    @foreach ($aksesPelanggan as $idPelanggan)
                                        <input type="hidden" name="pelanggan_ids[]" value="{{ $idPelanggan }}">
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Akses</button>
                            </form>
                        </div>
                        {{-- Filter Form --}}
                        <form method="GET" action="" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <select name="alamat" id="alamat" class="form-control">
                                        <option value="">Semua Alamat</option>
                                        @foreach ($alamatList as $alamat)
                                            <option value="{{ $alamat }}"
                                                {{ $filterAlamat == $alamat ? 'selected' : '' }}>
                                                {{ $alamat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="rw" class="form-label">RW</label>
                                    <select name="rw" id="rw" class="form-control">
                                        <option value="">Semua RW</option>
                                        @foreach ($rwList as $rw)
                                            <option value="{{ $rw }}" {{ $filterRW == $rw ? 'selected' : '' }}>
                                                {{ $rw }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="rt" class="form-label">RT</label>
                                    <select name="rt" id="rt" class="form-control">
                                        <option value="">Semua RT</option>
                                        @foreach ($rtList as $rt)
                                            <option value="{{ $rt }}" {{ $filterRT == $rt ? 'selected' : '' }}>
                                                {{ $rt }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                        {{-- Table Area/Tempat --}}
                        <div class="table-responsive">
                            <table id="dataTableExample" class="table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Alamat</th>
                                        <th>RW</th>
                                        <th>RT</th>
                                        <th>Petugas yang Memiliki Akses</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dataArea as $index => $area)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $area['alamat'] }}</td>
                                            <td>{{ $area['rw'] }}</td>
                                            <td>{{ $area['rt'] }}</td>
                                            <td>
                                                @if (isset($petugasAksesArea[$area['area_key']]) && count($petugasAksesArea[$area['area_key']]) > 0)
                                                    @foreach ($petugasAksesArea[$area['area_key']] as $petugas_akses)
                                                        <span
                                                            class="badge badge-light text-dark border mr-1">{{ $petugas_akses['nama'] }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="checkbox" class="area-checkbox"
                                                    data-area-key="{{ $area['area_key'] }}"
                                                    data-pelanggan-ids="{{ implode(',', $area['pelanggan_ids']) }}"
                                                    {{ isset($areaSelected[$area['area_key']]) && $areaSelected[$area['area_key']] ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Data tidak ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua elemen input dan select yang ada dalam form filter
            const filterInputs = document.querySelectorAll(
                'select[name="alamat"], select[name="rw"], select[name="rt"]');

            // Tambahkan event listener untuk setiap elemen
            filterInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Cari form terdekat yang berisi input ini
                    const form = this.closest('form');
                    if (form) {
                        form.submit(); // Submit form secara otomatis saat nilai berubah
                    }
                });
            });

            // Menangani checkboxes untuk area
            const areaCheckboxes = document.querySelectorAll('.area-checkbox');
            const hiddenContainer = document.getElementById('hiddenInputsContainer');
            const form = document.getElementById('aksesPelangganForm');

            // Fungsi untuk membersihkan semua hidden inputs
            function clearHiddenInputs() {
                const existingInputs = hiddenContainer.querySelectorAll('input[name="pelanggan_ids[]"]');
                existingInputs.forEach(input => input.remove());
            }

            // Fungsi untuk menambahkan hidden input
            function addHiddenInput(pelangganId) {
                if (pelangganId && pelangganId.trim() !== '') {
                    const existingInput = hiddenContainer.querySelector(`input[value="${pelangganId}"]`);
                    if (!existingInput) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'pelanggan_ids[]';
                        input.value = pelangganId.trim();
                        hiddenContainer.appendChild(input);
                    }
                }
            }

            // Fungsi untuk menghapus hidden input
            function removeHiddenInput(pelangganId) {
                if (pelangganId && pelangganId.trim() !== '') {
                    const existingInput = hiddenContainer.querySelector(`input[value="${pelangganId.trim()}"]`);
                    if (existingInput) {
                        existingInput.remove();
                    }
                }
            }

            // Event listener untuk checkbox area
            areaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const areaKey = this.getAttribute('data-area-key');
                    const pelangganIdsStr = this.getAttribute('data-pelanggan-ids');

                    if (!pelangganIdsStr) return;

                    const pelangganIds = pelangganIdsStr.split(',').filter(id => id && id.trim() !==
                        '');

                    if (this.checked) {
                        // Jika area dicentang, tambahkan semua pelanggan di area ini
                        pelangganIds.forEach(pelangganId => {
                            addHiddenInput(pelangganId);
                        });
                        console.log(
                            `Area ${areaKey} dipilih. Menambahkan ${pelangganIds.length} pelanggan.`
                            );
                    } else {
                        // Jika area tidak dicentang, hapus semua pelanggan di area ini
                        pelangganIds.forEach(pelangganId => {
                            removeHiddenInput(pelangganId);
                        });
                        console.log(
                            `Area ${areaKey} tidak dipilih. Menghapus ${pelangganIds.length} pelanggan.`
                            );
                    }

                    // Update tampilan jumlah pelanggan terpilih (opsional)
                    updateSelectedCount();
                });
            });

            // Fungsi untuk sync checkbox dengan hidden inputs yang sudah ada
            function syncCheckboxes() {
                const existingInputs = hiddenContainer.querySelectorAll('input[name="pelanggan_ids[]"]');
                const existingIds = Array.from(existingInputs).map(input => input.value.trim());

                areaCheckboxes.forEach(checkbox => {
                    const pelangganIdsStr = checkbox.getAttribute('data-pelanggan-ids');
                    if (!pelangganIdsStr) return;

                    const pelangganIds = pelangganIdsStr.split(',').filter(id => id && id.trim() !== '');

                    // Cek apakah semua pelanggan di area ini sudah terpilih
                    const allSelected = pelangganIds.length > 0 && pelangganIds.every(id => existingIds
                        .includes(id.trim()));

                    checkbox.checked = allSelected;
                });
            }

            // Fungsi untuk menampilkan jumlah pelanggan terpilih (opsional)
            function updateSelectedCount() {
                const existingInputs = hiddenContainer.querySelectorAll('input[name="pelanggan_ids[]"]');
                const count = existingInputs.length;

                // Hanya log, tidak mengubah nama tombol
                console.log(`Total pelanggan terpilih: ${count}`);
            }

            // Jalankan sync saat halaman dimuat
            syncCheckboxes();
            updateSelectedCount();

            // Validasi sebelum submit
            form.addEventListener('submit', function(e) {
                const existingInputs = hiddenContainer.querySelectorAll('input[name="pelanggan_ids[]"]');

                if (existingInputs.length === 0) {
                    const confirmed = confirm(
                        'Tidak ada pelanggan yang dipilih. Apakah Anda yakin ingin melanjutkan? Ini akan menghapus semua akses pelanggan untuk petugas ini.'
                        );
                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }
                }

                // Debug: tampilkan ID yang akan dikirim
                const ids = Array.from(existingInputs).map(input => input.value);
                console.log('IDs yang akan dikirim:', ids);
            });

            // Debug function untuk melihat state hidden inputs
            window.debugHiddenInputs = function() {
                const inputs = hiddenContainer.querySelectorAll('input[name="pelanggan_ids[]"]');
                console.log('Current hidden inputs:', Array.from(inputs).map(input => input.value));
            };
        });
    </script>
@endsection
