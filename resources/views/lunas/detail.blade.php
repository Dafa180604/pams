@extends('layouts.master')

@section('title', 'Data Transaksi')

@section('content')
  <div class="p-4">
    <nav class="mb-4">
    <ol class="flex flex-wrap items-center space-x-2">
      <li><a href="{{ route('lunas.index') }}" class="text-blue-600 hover:text-blue-800">Data Master Transaksi</a></li>
      <li class="flex items-center">
      <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
        clip-rule="evenodd"></path>
      </svg>
      </li>
      <li class="text-gray-600" aria-current="page">ID Transaksi {{ $dataTransaksi->id_transaksi }}</li>
    </ol>
    </nav>

    <div class="w-full">
    <div class="bg-white rounded-lg shadow-md">
      <div class="p-6">
      <div class="flex flex-col md:flex-row justify-between">
        <div class="w-full md:w-1/3 mb-4 md:mb-0">
        <div class="mt-3 text-lg font-semibold">Detail Transaksi</div>
        <h6 class="mt-2 mb-1">#{{ $dataTransaksi->id_transaksi }}</h6>
        <p class="mt-2 mb-1 font-bold">Nama :
          {{ optional($dataTransaksi->pemakaian->users()->withTrashed()->first())->nama ?? 'Pengguna dihapus' }}
        </p>
        <p class="mb-3">No Handphone :
          {{ optional($dataTransaksi->pemakaian->users()->withTrashed()->first())->no_hp ?? 'Tidak tersedia' }}
        </p>
        <h5 class="mt-4 mb-2 text-gray-600">Alamat :</h5>
        <p>
          {{ optional($dataTransaksi->pemakaian->users()->withTrashed()->first())->alamat ?? 'Tidak tersedia' }}, RT
          {{ optional($dataTransaksi->pemakaian->users()->withTrashed()->first())->rt ?? '-' }}, RW
          {{ optional($dataTransaksi->pemakaian->users()->withTrashed()->first())->rw ?? '-' }}
        </p>
        </div>
        <div class="w-full md:w-1/3 md:text-right">
        <h4 class="font-bold uppercase mt-4 mb-2">{{ $dataTransaksi->status_pembayaran }}</h4>
        <p class="mb-1">ID pemakaian : {{ $dataTransaksi->pemakaian->id_pemakaian }}</p>
        <h4 class="font-normal">Nama Petugas/Pencatat :
          @if($dataTransaksi->pemakaian->petugas)
          @php
        $petugasIdArray = explode(',', $dataTransaksi->pemakaian->petugas);
        $petugasNames = [];
        foreach ($petugasIdArray as $petugasId) {
        $petugasId = trim($petugasId);
        if (isset($petugasUsers[$petugasId])) {
        $petugasNames[] = $petugasUsers[$petugasId]->nama;
        } else {
        $petugasNames[] = $petugasId;
        }
        }
        echo implode(', ', $petugasNames);
        @endphp
      @else
        -
      @endif
        </h4>
        <h6 class="mb-0 mt-3 font-normal"><span class="text-gray-600">Tanggal Mencatat :</span>
          {{ $dataTransaksi->pemakaian->waktu_catat }}</h6>
        <h6 class="font-normal"><span class="text-gray-600">Tanggal Pembayaran :</span>
          {{ $dataTransaksi->tgl_pembayaran }}</h6>
        </div>
      </div>
      <div class="mt-5">
        <div class="overflow-x-auto">
        <h4 class="mt-3 mb-4 text-lg font-semibold text-black">Rincian Biaya</h4>
        <table class="w-full table-auto">
          @php
      // Mengambil dan decode data detail_biaya
      $detailBiaya = json_decode($dataTransaksi->detail_biaya ?? '{}', true);
      $beban = $detailBiaya['beban'] ?? ['tarif' => 0];
      $kategoriList = $detailBiaya['kategori'] ?? [];
      $totalPemakaian = $dataTransaksi->pemakaian->jumlah_pemakaian ?? 0;
      @endphp

          <!-- Tampilkan biaya beban -->
          <tr class="border-b">
          <th class="py-2 text-left">Beban</th>
          <td class="py-2 text-right">
            Rp {{ number_format($beban['tarif'], 0, ',', '.') }}
          </td>
          </tr>

          <!-- Tampilkan semua kategori yang digunakan dari detail_biaya -->
          @foreach($kategoriList as $kategori)
        <tr class="border-b">
        <th class="py-2 text-left">{{ $kategori['volume'] }} m³ × Rp
        {{ number_format($kategori['tarif'], 0, ',', '.') }}
        </th>
        <td class="py-2 text-right">Rp {{ number_format($kategori['subtotal'], 0, ',', '.') }}</td>
        </tr>
      @endforeach

          <!-- Tampilkan total pemakaian -->
          <tr class="border-b">
          <th class="py-2 text-left">{{ $totalPemakaian }} m³ Total Pemakaian</th>
          <td class="py-2 text-right"></td>
          </tr>
        </table>
        </div>
      </div>
      <div class="mt-5">
        <div class="flex flex-col md:flex-row">
        <div class="w-full md:w-1/2 md:ml-auto">
          <div class="overflow-x-auto">
          <table class="w-full table-auto">
            <tbody>
            <tr class="border-b">
              <td class="py-2 px-4 font-bold">Biaya Denda</td>
              <td class="py-2 px-4 font-bold text-right">-Rp
              {{ number_format($dataTransaksi->rp_denda, 0, ',', '.') }}
              </td>
            </tr>
            <tr class="bg-gray-100">
              <td class="py-2 px-4 font-bold">Total Tagihan</td>
              <td class="py-2 px-4 font-bold text-right">Rp
              {{ number_format($dataTransaksi->jumlah_rp, 0, ',', '.') }}
              </td>
            </tr>
            <tr class="border-b">
              <td class="py-2 px-4 font-bold">Uang Bayar</td>
              <td class="py-2 px-4 font-bold text-right">Rp
              {{ number_format($dataTransaksi->uang_bayar, 0, ',', '.') }}
              </td>
            </tr>
            <tr class="border-b">
              <td class="py-2 px-4 font-bold">Kembalian</td>
              <td class="py-2 px-4 font-bold text-right">Rp
              {{ number_format($dataTransaksi->kembalian, 0, ',', '.') }}
              </td>
            </tr>
            </tbody>
          </table>
          </div>
        </div>
        </div>
      </div>
      <div class="w-full mt-6">
        @if($dataTransaksi->status_pembayaran == 'Lunas')
      <a href="{{ route('lunas.cetak', $dataTransaksi->id_transaksi) }}"
      class="float-right px-4 py-2 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 transition-colors"
      target="_blank">
      <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
      </svg>
      Print
      </a>
      @endif
      </div>
      </div>
    </div>
    </div>
  </div>
@endsection