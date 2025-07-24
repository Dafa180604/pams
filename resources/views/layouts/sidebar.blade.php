<nav class="sidebar">
  <div class="sidebar-header">
    <a href="#" class="sidebar-brand">
      PAMSIMAS
    </a>
    <div class="sidebar-toggler not-active">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  <div class="sidebar-body">
    <ul class="nav">
      <li class="nav-item nav-category">Menu</li>
      <li class="nav-item">
        <a href="{{route('DashboardAdmin.index')}}" class="nav-link {{ Request::routeIs('DashboardAdmin.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="home"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>
      <li class="nav-item nav-category">Kelola Data Master</li>
      <!-- <li class="nav-item">
        <a href="{{route('pemakaian.index')}}" class="nav-link">
          <i class="link-icon" data-feather="droplet"></i>
          <span class="link-title">Pemakaian</span>
        </a>
      </li> -->
      <li class="nav-item">
        <a href="{{route('petugas.index')}}" class="nav-link {{ Request::routeIs('petugas.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="user"></i>
          <span class="link-title">Data Petugas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{route('pelanggan.index')}}" class="nav-link {{ Request::routeIs('pelanggan.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="users"></i>
          <span class="link-title">Data Pelanggan</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#biaya" role="button" aria-expanded="{{ Request::routeIs('bebanbiaya.*', 'kategoribiayaair.*', 'biayagolongan.*', 'BiayaDenda.*') ? 'true' : 'false' }}"
          aria-controls="biaya">
          <i class="link-icon" data-feather="dollar-sign"></i>
          <span class="link-title">Biaya</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ Request::routeIs('bebanbiaya.*', 'kategoribiayaair.*', 'biayagolongan.*', 'BiayaDenda.*') ? 'show' : '' }}" id="biaya">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{route('bebanbiaya.index')}}" class="nav-link {{ Request::routeIs('bebanbiaya.*') ? 'active' : '' }}">Beban Biaya</a>
            </li>
            <li class="nav-item">
              <a href="{{route('kategoribiayaair.index')}}" class="nav-link {{ Request::routeIs('kategoribiayaair.*') ? 'active' : '' }}">Kategori Biaya</a>
            </li>
            <li class="nav-item">
              <a href="{{route('biayagolongan.index')}}" class="nav-link {{ Request::routeIs('biayagolongan.*') ? 'active' : '' }}">Biaya Instalasi</a>
            </li>
            <li class="nav-item">
              <a href="{{route('BiayaDenda.index')}}" class="nav-link {{ Request::routeIs('BiayaDenda.*') ? 'active' : '' }}">Biaya Denda</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item nav-category">Transaksi</li>
      <li class="nav-item">
        {{-- <a href="{{route('pelunasan.index')}}" class="nav-link {{ Request::routeIs('pelanggan.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="droplet"></i>
          <span class="link-title">Data Pemakaian</span>
        </a> --}}
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#transaksi" role="button" aria-expanded="{{ Request::routeIs('belumlunas.*', 'lunas.*') ? 'true' : 'false' }}"
          aria-controls="transaksi">
          <i class="link-icon" data-feather="mail"></i>
          <span class="link-title">Transaksi</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse {{ Request::routeIs('belumlunas.*', 'lunas.*') ? 'show' : '' }}" id="transaksi">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{route('pelunasan.index')}}" class="nav-link {{ Request::routeIs('pelunasan.*') ? 'active' : '' }}">Belum Lunas</a>
            </li>
            <li class="nav-item">
              <a href="{{route('lunas.index')}}" class="nav-link {{ Request::routeIs('lunas.*') ? 'active' : '' }}">Lunas</a>
            </li>
            <li class="nav-item">
              <a href="{{ route('pemulihan.riwayat') }}" class="nav-link {{ Request::routeIs('lunas.*') ? 'active' : '' }}">Riwayat Pemulihan</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item nav-category">Aktivitas</li>
      <!-- PERBAIKAN: Pisahkan Keluhan dan Pengeluaran ke dalam nav-item terpisah -->
      <li class="nav-item">
        <a href="{{route('keluhan.index')}}" class="nav-link {{ Request::routeIs('keluhan.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="alert-triangle"></i>
          <span class="link-title">Keluhan</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{route('pengeluaran.index')}}" class="nav-link {{ Request::routeIs('pengeluaran.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="book"></i>
          <span class="link-title">Pengeluaran</span>
        </a>
      </li>
      <li class="nav-item nav-category">Laporan</li>
      <li class="nav-item">
        <a href="{{route('laporan.index')}}" class="nav-link {{ Request::routeIs('laporan.*') ? 'active' : '' }}">
          <i class="link-icon" data-feather="file-text"></i>
          <span class="link-title">Laporan</span>
        </a>
      </li>
    </ul>
  </div>
</nav>