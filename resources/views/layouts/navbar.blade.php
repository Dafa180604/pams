<nav class="navbar">
    <a href="#" class="sidebar-toggler">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p></p>
                        <a href="javascript:;" class="text-muted">Clear all</a>
                    </div>
                    <div class="p-1">
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div
                                class="wd-30 ht-30 d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <i class="icon-sm text-white" data-feather="gift"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p></p>
                                <p class="tx-12 text-muted"></p>
                            </div>
                        </a>
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2">
                            <div
                                class="wd-30 ht-30 d-flex align-items-center justify-content-center bg-primary rounded-circle me-3">
                                <i class="icon-sm text-white" data-feather="alert-circle"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p></p>
                                <p class="tx-12 text-muted"></p>
                            </div>
                        </a>
                    </div>
                    <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
                        <a href="javascript:;"></a>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="wd-30 ht-30 rounded-circle"
                        src="{{ Auth::user()->foto_profile ?? 'https://via.placeholder.com/100' }}" alt="Foto Profil">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            <img src="{{ Auth::user()->foto_profile ?? 'https://via.placeholder.com/100' }}"
                                class="wd-80 ht-80 rounded-circle" alt="Foto Profil">
                        </div>
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">{{ Auth::user()->nama }}</p>
                            <p class="tx-12 text-muted"></p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li class="dropdown-item py-2 d-flex align-items-center">
                            <a href="{{route('profile.index')}}" class="text-body d-flex align-items-center">
                                <i class="me-2 icon-md" data-feather="user"></i>
                                <span>Profil</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2 d-flex align-items-center">
                            <a href="{{ route('profile.edit', Auth::user()->username) }}"
                                class="text-body d-flex align-items-center">
                                <i class="me-2 icon-md" data-feather="edit"></i>
                                <span>Edit Profil</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2 d-flex align-items-center">
                            <a href="{{ route('profile.edit-password', Auth::user()->username) }}"
                                class="text-body d-flex align-items-center">
                                <i class="me-2 icon-md" data-feather="key"></i>
                                <span>Ganti Password</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2 d-flex align-items-center">
                            <a href="{{ route('logout') }}" class="text-body d-flex align-items-center"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="me-2 icon-md" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                            <!-- Hidden logout form -->
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>