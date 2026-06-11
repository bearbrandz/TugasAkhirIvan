<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Sistem Informasi Apotek</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Legacy theme overrides for form cards and tables -->
    <link href="{{ asset('conquer/css/custom.css') }}?v={{ time() }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/apotek-ui.css') }}?v={{ time() }}">

    <style>
    /* Sidebar logo Apotek Medico */
    .sidebar-header {
        min-height: 88px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .sidebar-logo-medico {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .sidebar-logo-medico img {
        width: 100%;
        max-width: 210px;
        height: auto;
        object-fit: contain;
        display: block;
    }

    /* Topbar hanya untuk tanggal dan user di kanan */
    .topbar {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        padding-left: 24px;
        padding-right: 24px;
    }

    .topbar-right {
        margin-left: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 12px;
        width: auto !important;
    }

    .topbar-date,
    .topbar-user {
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .sidebar-logo-medico img {
            max-width: 170px;
        }

        .topbar {
            padding-left: 12px;
            padding-right: 12px;
        }

        .topbar-right {
            gap: 8px;
        }

        .user-name {
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
</style>

    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-medico" style="display:flex; justify-content:center; align-items:center; width:100%; height:100%;">
                <div style="font-family: 'Arial Black', Impact, sans-serif; font-size: 28px; line-height: 1.1; text-align: center; font-weight: 900; letter-spacing: 1px;">
                    <span style="color: #facc15 !important; text-transform: uppercase;">Apotek</span><br>
                    <span style="color: #ef4444 !important; border: 3px solid #ef4444; padding: 0 4px; margin-right: 1px; display: inline-block; line-height: 0.9;">M</span><span style="color: #ef4444 !important;">edico</span>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            @auth
                {{-- ==== ADMIN / APOTEKER ==== --}}
                @if(auth()->user()->tipe_user === 'admin' || auth()->user()->tipe_user === 'apoteker')
                    <li class="menu-item {{ Request::is('home') || Request::is('homeProduk') ? 'active' : '' }}">
                        <a href="{{ route('homeProduk') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                            <span class="menu-label">Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item has-submenu {{ Request::is('notajuals*') || Request::is('notabelis*') || Request::is('retur*') || Request::is('produk/daftarTerima*') || Request::is('produk/daftarKadaluarsa*') || Request::is('racikan/notaracikan*') || Request::is('racikan/daftarNarkotika*') || Request::is('racikan/report*') ? 'active open' : '' }}">
                        <a href="#" class="menu-link menu-toggle">
                            <span class="menu-icon"><i class="fas fa-book-open"></i></span>
                            <span class="menu-label">Transaksi</span>
                            <span class="menu-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ url('notajuals/create') }}" class="{{ Request::is('notajuals/create') ? 'active' : '' }}"><i class="fas fa-basket-shopping"></i> Jual Produk</a></li>
                            <li><a href="{{ url('notabelis/create') }}" class="{{ Request::is('notabelis/create') ? 'active' : '' }}"><i class="fas fa-cart-plus"></i> Beli Produk</a></li>
                            <li><a href="{{ url('notajuals') }}" class="{{ Request::is('notajuals') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Nota Penjualan</a></li>
                             @if(auth()->user()->tipe_user === 'admin' || auth()->user()->tipe_user === 'apoteker')
                                <li><a href="{{ url('notabelis') }}" class="{{ Request::is('notabelis') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Nota Pembelian</a></li>
                                <li><a href="{{ route('produks.daftarTerima') }}" class="{{ Request::is('produk/daftarTerima*') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Nota Penerimaan</a></li>
                                <li><a href="{{ route('retur.index') }}" class="{{ Request::is('retur*') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Daftar Retur</a></li>
                                <li><a href="{{ route('racikans.notaRacikan') }}" class="{{ Request::is('racikan/notaracikan*') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Daftar Peracikan</a></li>
                                <li><a href="{{ route('produks.daftarKadaluarsa') }}" class="{{ Request::is('produk/daftarKadaluarsa*') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Daftar Kadaluarsa</a></li>
                                <li><a href="{{ route('racikans.daftarNarkotika') }}" class="{{ Request::is('racikan/daftarNarkotika*') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Daftar Narkotika</a></li>
                                <li><a href="{{ route('racikans.reportNarkotika') }}" class="{{ Request::is('racikan/report*') ? 'active' : '' }}"><i class="fas fa-file-export"></i> Laporan Narkotika</a></li>
                            @endif
                        </ul>
                    </li>
                    <li class="menu-item {{ Request::is('produk*') && !Request::is('produk/daftarTerima*') && !Request::is('produk/daftarKadaluarsa*') ? 'active' : '' }}">
                        <a href="{{ route('produk') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-pills"></i></span>
                            <span class="menu-label">Produk</span>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('opname*') ? 'active' : '' }}">
                        <a href="{{ route('opname') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-label">Stok Opname</span>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('racikan') || Request::is('racikans*') || Request::is('racikan/komposisi*') || Request::is('racikan/checkout*') || Request::is('racikan/bayar*') || Request::is('racikan/jualracikan*') ? 'active' : '' }}">
                        <a href="{{ route('racikan') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-flask"></i></span>
                            <span class="menu-label">Racikan</span>
                        </a>
                    </li>
                    @if(auth()->user()->tipe_user === 'admin')
                        <li class="menu-item has-submenu {{ Request::is('user*') || Request::is('register') ? 'active open' : '' }}">
                            <a href="#" class="menu-link menu-toggle">
                                <span class="menu-icon"><i class="fas fa-users"></i></span>
                                <span class="menu-label">Karyawan</span>
                                <span class="menu-arrow"><i class="fas fa-chevron-right"></i></span>
                            </a>
                            <ul class="submenu">
                                <li><a href="{{ route('user') }}" class="{{ Request::is('user*') ? 'active' : '' }}"><i class="fas fa-users"></i> Daftar Karyawan</a></li>
                            </ul>
                        </li>
                        <li class="menu-item {{ Request::is('laporan/labarugi*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.labarugi') }}" class="menu-link">
                                <span class="menu-icon"><i class="fas fa-chart-line"></i></span>
                                <span class="menu-label">Laporan Laba Rugi</span>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is('log*') ? 'active' : '' }}">
                            <a href="{{ route('log.index') }}" class="menu-link">
                                <span class="menu-icon"><i class="fas fa-list"></i></span>
                                <span class="menu-label">Log Aktivitas</span>
                            </a>
                        </li>
                    @endif
                    <li class="menu-item {{ Request::is('distributor*') ? 'active' : '' }}">
                        <a href="{{ route('distributor') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-truck"></i></span>
                            <span class="menu-label">Distributor</span>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('gudang*') ? 'active' : '' }}">
                        <a href="{{ route('gudang') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-warehouse"></i></span>
                            <span class="menu-label">Gudang</span>
                        </a>
                    </li>
                    @if(auth()->user()->tipe_user === 'admin')
                    <li class="menu-item {{ Request::is('satuan') || Request::is('satuans*') ? 'active' : '' }}">
                        <a href="{{ route('satuan') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-layer-group"></i></span>
                            <span class="menu-label">Satuan Produk</span>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('satuankonversi*') ? 'active' : '' }}">
                        <a href="{{ route('satuankonversi.index') }}" class="menu-link">
                            <span class="menu-icon"><i class="fas fa-arrows-alt-h"></i></span>
                            <span class="menu-label">Konversi Satuan</span>
                        </a>
                    </li>
                    @endif
                @elseif(auth()->user()->tipe_user === 'kasir')
                    {{-- ==== KASIR ==== --}}
                    <li class="menu-item has-submenu {{ Request::is('notajuals*') ? 'active open' : '' }}">
                        <a href="#" class="menu-link menu-toggle">
                            <span class="menu-icon"><i class="fas fa-book-open"></i></span>
                            <span class="menu-label">Transaksi</span>
                            <span class="menu-arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                        <ul class="submenu">
                            <li><a href="{{ url('notajuals/create') }}" class="{{ Request::is('notajuals/create') ? 'active' : '' }}"><i class="fas fa-basket-shopping"></i> Jual Produk</a></li>
                            <li><a href="{{ url('notajuals') }}" class="{{ Request::is('notajuals') ? 'active' : '' }}"><i class="fas fa-file-lines"></i> Nota Penjualan</a></li>
                        </ul>
                    </li>
                @endif
            @else
                {{-- ==== GUEST ==== --}}
                <li class="menu-item {{ Request::is('/') ? 'active' : '' }}">
                    <a href="{{ route('welcome') }}" class="menu-link">
                        <span class="menu-icon"><i class="fas fa-home"></i></span>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>
            @endauth
        </ul>
        <div class="sidebar-footer">
            <div class="sidebar-version">v1.0.0</div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Top Header -->
        <header class="topbar">

            <div class="topbar-right">
                <div class="topbar-date">
                    <i class="fas fa-calendar-alt"></i>
                    <span id="currentDate"></span>
                </div>
                <div class="topbar-user dropdown">
                    <button class="user-btn dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="user-name">{{ auth()->user()->nama ?? 'Guest' }}</span>
                        <i class="fas fa-chevron-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                        @auth
                            <li>
                                <div class="dropdown-header-info">
                                    <div class="dhi-avatar"><i class="fas fa-user-circle"></i></div>
                                    <div>
                                        <strong>{{ auth()->user()->nama }}</strong>
                                        <small>{{ auth()->user()->email }}</small>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @auth
                                <li>
                                    <a class="dropdown-item" href="{{ route('users.edit', auth()->id()) }}">
                                        <i class="fas fa-cog me-2"></i>Pengaturan
                                    </a>
                                </li>
                            @endauth
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i>Keluar
                                </a>
                            </li>
                        @else
                            <li>
                                <a class="dropdown-item" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                                </a>
                            </li>
                        @endauth
                    </ul>
                    @auth
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="page-footer">
            <span>© {{ date('Y') }} Sistem Informasi Apotek. All rights reserved.</span>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>