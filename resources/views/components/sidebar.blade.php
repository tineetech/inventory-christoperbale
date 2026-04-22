<div id="layout-sidenav" class="layout-sidenav sidenav sidenav-vertical bg-white logo-dark">

    <div class="app-brand demo">
        <span class="app-brand-logo demo">
            <img src="{{ asset('assets/img/logo-inv.png') }}" class="img-fluid" style="width:40px;">
        </span>
        <a href="{{ route('dashboard.index') }}" class="app-brand-text demo sidenav-text font-weight-normal ml-2">
            POS Inventory
        </a>
        <a href="javascript:" class="layout-sidenav-toggle sidenav-link text-large ml-auto">
            <i class="ion ion-md-menu align-middle"></i>
        </a>
    </div>

    <div class="sidenav-divider mt-0"></div>

    <ul class="sidenav-inner py-1">

        <!-- DASHBOARD -->
        @if(hasPermission('lihat', 'dashboard'))
            <li class="sidenav-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                <a href="{{ route('dashboard.index') }}" class="sidenav-link">
                    <i class="sidenav-icon feather icon-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>
        @endif

        <!-- MASTER -->
        @if(hasPermission('lihat', 'supplier') || hasPermission('lihat', 'satuan') || hasPermission('lihat', 'barang') || hasPermission('lihat', 'dropshipper'))
            <li class="sidenav-item {{ request()->routeIs('supplier.*','satuan.*','barang.*','dropshipper.*') ? 'active open' : '' }}">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-layers"></i>
                    <div>Master</div>
                </a>
                <ul class="sidenav-menu">

                    @if(hasPermission('lihat', 'supplier'))
                        <li class="sidenav-item {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                            <a href="{{ route('supplier.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-truck" data-feather="truck" style="width:16px;height:18px;margin-right:12px"></i>
                                <div>Supplier</div>
                            </a>
                        </li>
                    @endif

                    @if(hasPermission('lihat', 'satuan'))
                        <li class="sidenav-item {{ request()->routeIs('satuan.*') ? 'active' : '' }}">
                            <a href="{{ route('satuan.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-bar-chart-2"></i>
                                <div>Satuan</div>
                            </a>
                        </li>
                    @endif

                    @if(hasPermission('lihat', 'barang'))
                        <li class="sidenav-item {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                            <a href="{{ route('barang.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-package"></i>
                                <div>Barang</div>
                            </a>
                        </li>
                    @endif

                    @if(hasPermission('lihat', 'dropshipper'))
                        <li class="sidenav-item {{ request()->routeIs('dropshipper.*') ? 'active' : '' }}">
                            <a href="{{ route('dropshipper.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-user-plus"></i>
                                <div>Dropshipper</div>
                            </a>
                        </li>
                    @endif

                </ul>
            </li>
        @endif


        <!-- TRANSAKSI -->
        @if(hasPermission('lihat', 'pembelian') || hasPermission('lihat', 'penjualan') || hasPermission('lihat', 'manajemen_stok'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-header small font-weight-semibold">Transaksi</li>

            @if(hasPermission('lihat', 'pembelian'))
                <li class="sidenav-item {{ request()->routeIs('pembelian.*') ? 'active' : '' }}">
                    <a href="{{ route('pembelian.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-shopping-cart"></i>
                        <div>Pembelian</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'penjualan'))
                <li class="sidenav-item {{ request()->routeIs('penjualan.*') ? 'active' : '' }}">
                    <a href="{{ route('penjualan.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-credit-card"></i>
                        <div>Penjualan</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'manajemen_stok'))
                <li class="sidenav-item {{ request()->routeIs('manage-stok.*') ? 'active' : '' }}">
                    <a href="{{ route('manage-stok.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-sliders"></i>
                        <div>Manajemen Stok</div>
                    </a>
                </li>
            @endif
        @endif


        <!-- LAPORAN -->
        @if(hasPermission('lihat', 'laporan_pembelian') || hasPermission('lihat', 'laporan_penjualan') || hasPermission('lihat', 'laporan_stok') || hasPermission('lihat', 'laporan_barang'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-header small font-weight-semibold">Laporan</li>

            @if(hasPermission('lihat', 'laporan_pembelian'))
                <li class="sidenav-item {{ request()->routeIs('laporan-pembelian.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.pembelian') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-file-text"></i>
                        <div>Pembelian</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'laporan_penjualan'))
                <li class="sidenav-item {{ request()->routeIs('laporan-penjualan.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.penjualan') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-file-text"></i>
                        <div>Penjualan</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'laporan_stok'))
                <li class="sidenav-item {{ request()->routeIs('laporan-stok.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.stok') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-archive" data-feather="archive" style="width:16px;height:18px;margin-right:15px"></i>
                        <div>Stok</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'laporan_barang'))
                <li class="sidenav-item {{ request()->routeIs('laporan-barang.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.barang') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-box"></i>
                        <div>Barang</div>
                    </a>
                </li>
            @endif
        @endif


        <!-- KONFIGURASI -->
        @if(hasPermission('lihat', 'pengguna') || hasPermission('lihat', 'hak_akses') || hasPermission('lihat', 'role_hak_akses') || hasPermission('lihat', 'backup_database'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-header small font-weight-semibold">Konfigurasi</li>

            @if(hasPermission('lihat', 'pengguna'))
                <li class="sidenav-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
                    <a href="{{ route('pengguna.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-users"></i>
                        <div>Pengguna</div>
                    </a>
                </li>
            @endif


            @if(hasPermission('lihat', 'hak_akses'))
                <li class="sidenav-item {{ request()->routeIs('hak_akses.*') ? 'active' : '' }}">
                    <a href="{{ route('hak_akses.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-shield"></i>
                        <div>Hak Akses</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'role_hak_akses'))
                <li class="sidenav-item {{ request()->routeIs('role_hak_akses.*') ? 'active' : '' }}">
                    <a href="{{ route('role_hak_akses.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-lock"></i>
                        <div>Mapping Role Akses</div>
                    </a>
                </li>
            @endif

            @if(hasPermission('lihat', 'backup_database'))
                <li class="sidenav-item {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                    <a href="{{ route('backup.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-archive" data-feather="database" style="width:16px;height:18px;margin-right:15px"></i>
                        <div>Backup Database</div>
                    </a>
                </li>
            @endif
        @endif

    </ul>

</div>
