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
        <a href="" class="nav-link">
          <i class="link-icon" data-feather="home"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>
      <li class="nav-item nav-category">Kelola Data Master</li>
      <li class="nav-item">
        <a href="{{route('pemakaian.index')}}" class="nav-link">
          <i class="link-icon" data-feather="droplet"></i>
          <span class="link-title">Pemakaian</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{route('petugas.index')}}" class="nav-link">
          <i class="link-icon" data-feather="user"></i>
          <span class="link-title">Data Petugas</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{route('pelanggan.index')}}" class="nav-link">
          <i class="link-icon" data-feather="users"></i>
          <span class="link-title">Data Pelanggan</span>
        </a>
      </li>
            <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#biaya" role="button" aria-expanded="false"
          aria-controls="biaya">
          <i class="link-icon" data-feather="dollar-sign"></i>
          <span class="link-title">Biaya</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse" id="biaya">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{route('bebanbiaya.index')}}" class="nav-link">Beban Biaya</a>
            </li>
            <li class="nav-item">
              <a href="{{route('kategoribiayaair.index')}}" class="nav-link">Kategori Biaya</a>
            </li>
            <li class="nav-item">
              <a href="{{route('biayagolongan.index')}}" class="nav-link">Biaya Premium</a>
            </li>
            <li class="nav-item">
              <a href="{{route('BiayaDenda.index')}}" class="nav-link">Biaya Denda</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item nav-category">Transaksi</li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#transaksi" role="button" aria-expanded="false"
          aria-controls="transaksi">
          <i class="link-icon" data-feather="mail"></i>
          <span class="link-title">Transaksi</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse" id="transaksi">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{route('belumlunas.index')}}" class="nav-link">Belum Lunas</a>
            </li>
            <li class="nav-item">
              <a href="{{route('lunas.index')}}" class="nav-link">Lunas</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item nav-category">Aktivitas</li>
      <li class="nav-item">
        <a href="{{route('keluhan.index')}}" class="nav-link">
          <i class="link-icon" data-feather="book"></i>
          <span class="link-title">Keluhan</span>
        </a>
        <a href="{{route('pengeluaran.index')}}" class="nav-link">
          <i class="link-icon" data-feather="book"></i>
          <span class="link-title">Pengeluaran</span>
        </a>
      </li>
      <li class="nav-item nav-category">Laporan</li>
      <li class="nav-item">
        <a href="{{route('laporan.index')}}" class="nav-link">
          <i class="link-icon" data-feather="file-text"></i>
          <span class="link-title">Laporan</span>
        </a>
      </li>
    </ul>
  </div>
</nav>