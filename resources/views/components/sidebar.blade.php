<div id="layout-sidenav" style="z-index: 9999" class="layout-sidenav sidenav sidenav-vertical bg-white logo-dark">

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

    <ul class="sidenav-inner py-1" style="padding-bottom: 80px !important">

        <!-- DASHBOARD -->
        @if (hasPermission('lihat', 'dashboard'))
            <li class="sidenav-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                <a href="{{ route('dashboard.index') }}" class="sidenav-link">
                    <i class="sidenav-icon feather icon-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>
        @endif

        <!-- MASTER -->
        @if (hasPermission('lihat', 'supplier') ||
                hasPermission('lihat', 'satuan') ||
                hasPermission('lihat', 'barang') ||
                hasPermission('lihat', 'dropshipper') ||
                hasPermission('lihat', 'brand'))
            <li class="sidenav-item  open">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-layers"></i>
                    <div>Master</div>
                </a>
                <ul class="sidenav-menu">

                    @if (hasPermission('lihat', 'supplier'))
                        <li class="sidenav-item {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                            <a href="{{ route('supplier.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-truck" data-feather="truck" width="16" height="16"></i>
                                <div>Supplier</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'satuan'))
                        <li class="sidenav-item {{ request()->routeIs('satuan.*') ? 'active' : '' }}">
                            <a href="{{ route('satuan.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-bar-chart-2"></i>
                                <div>Satuan</div>
                            </a>
                        </li>
                    @endif


                    @if (hasPermission('lihat', 'barang'))
                        <li class="sidenav-item {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                            <a href="{{ route('barang.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-package"></i>
                                <div>Barang</div>
                            </a>
                        </li>
                    @endif
                    @if (hasPermission('lihat', 'barang'))
                        <li class="sidenav-item {{ request()->routeIs('hpp-riwayat.*') ? 'active' : '' }}">
                            <a href="{{ route('hpp-riwayat.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-trending-up"></i>
                                <div>Riwayat HPP</div>
                            </a>
                        </li>
                    @endif
                    @if (hasPermission('lihat', 'dropshipper'))
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
        @if (hasPermission('lihat', 'pembelian') ||
                hasPermission('lihat', 'penjualan') ||
                hasPermission('lihat', 'manajemen_stok'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-header small font-weight-semibold">Transaksi</li>

            @if (hasPermission('lihat', 'pembelian'))
                <li class="sidenav-item {{ request()->routeIs('pembelian.*') ? 'active' : '' }}">
                    <a href="{{ route('pembelian.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-shopping-cart"></i>
                        <div>Pembelian</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'penjualan'))
                <li class="sidenav-item {{ request()->routeIs('penjualan.*') && !request()->routeIs('penjualan.draft') ? 'active' : '' }}">
                    <a href="{{ route('penjualan.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-credit-card"></i>
                        <div>Penjualan</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'penjualan'))
                <li class="sidenav-item {{ request()->routeIs('penjualan.draft') ? 'active' : '' }}">
                    <a href="{{ route('penjualan.draft') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-file-text"></i>
                        <div>Penjualan Draft</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'penjualan'))
                <li class="sidenav-item {{ request()->routeIs('penjualan.web') ? 'active' : '' }}">
                    <a href="{{ route('penjualan.web') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-globe"></i>
                        <div>Penjualan Web</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'penjualan'))
                <li class="sidenav-item {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}">
                    <a href="{{ route('pembayaran.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-credit-card"></i>
                        <div>Riwayat Pembayaran</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'manajemen_stok'))
                <li class="sidenav-item {{ request()->routeIs('manage-stok.*') ? 'active' : '' }}">
                    <a href="{{ route('manage-stok.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-sliders"></i>
                        <div>Manajemen Stok Global</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'hitung_stok'))
                <li class="sidenav-item {{ request()->routeIs('hitung-stok.*') ? 'active' : '' }}">
                    <a href="{{ route('hitung-stok.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-sliders"></i>
                        <div>Hitung Stok</div>
                    </a>
                </li>
            @endif
        @endif


        <!-- LAPORAN -->
        @if (hasPermission('lihat', 'laporan_pembelian') ||
                hasPermission('lihat', 'laporan_penjualan') ||
                hasPermission('lihat', 'laporan_stok') ||
                hasPermission('lihat', 'laporan_barang'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-header small font-weight-semibold">Laporan</li>

            @if (hasPermission('lihat', 'laporan_pembelian'))
                <li class="sidenav-item {{ request()->routeIs('laporan-pembelian.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.pembelian') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-file-text"></i>
                        <div>Lap. Pembelian</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_penjualan'))
                <li class="sidenav-item {{ request()->routeIs('laporan-penjualan.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.penjualan') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-file-text"></i>
                        <div>Lap. Penjualan</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_penjualan'))
                <li class="sidenav-item {{ request()->routeIs('laporan-penjualan-web.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.penjualan-web') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-globe"></i>
                        <div>Lap. Penjualan Web</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_penjualan'))
                <li class="sidenav-item {{ request()->routeIs('laporan-pembayaran.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.pembayaran') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-credit-card"></i>
                        <div>Lap. Pembayaran</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_stok'))
                <li class="sidenav-item {{ request()->routeIs('laporan-stok.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.stok') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-archive" data-feather="archive" width="16" height="16"></i>
                        <div>Lap. Stok</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_stok'))
                <li class="sidenav-item {{ request()->routeIs('laporan-stok-kritis.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.stok-kritis') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-alert-triangle" data-feather="alert-triangle" width="16" height="16"></i>
                        <div>Lap. Stok Kritis</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_barang'))
                <li class="sidenav-item {{ request()->routeIs('laporan-barang.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.barang') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-box"></i>
                        <div>Lap. Barang</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'laporan_retur'))
                <li class="sidenav-item {{ request()->routeIs('laporan-retur.*') ? 'active' : '' }}">
                    <a href="{{ route('laporan.retur') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-box"></i>
                        <div>Lap. Retur Penjualan</div>
                    </a>
                </li>
            @endif
        @endif

        
        @if (hasPermission('lihat', 'web-manage'))
        <li class="sidenav-divider mb-1"></li>
        <li class="sidenav-header small font-weight-semibold">Web Management</li>
            <li class="sidenav-item {{ request()->routeIs('brand.*') ? 'active' : '' }}">
                <a href="{{ route('brand.index') }}" class="sidenav-link">
                    <i class="sidenav-icon feather icon-tag"></i>
                    <div>Brand Management</div>
                </a>
            </li>



            <li class="sidenav-item ">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-layers"></i>
                    <div>Produk Management</div>
                </a>
                <ul class="sidenav-menu">
                    <!-- BARANG - WEB -->
                    @if (hasPermission('lihat', 'web-manage'))

                        @if (hasPermission('lihat', 'web-manage'))
                            <li class="sidenav-item {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                                <a href="{{ route('produk.index') }}" class="sidenav-link">
                                    <i class="sidenav-icon feather icon-package"></i>
                                    <div>Produk</div>
                                </a>
                            </li>
                        @endif

                        @if (hasPermission('lihat', 'web-manage'))
                            <li class="sidenav-item {{ request()->routeIs('produk_varian.*') ? 'active' : '' }}">
                                <a href="{{ route('produk_varian.index') }}" class="sidenav-link">
                                    <i class="sidenav-icon feather icon-layers"></i>
                                    <div>Produk Varian</div>
                                </a>
                            </li>
                        @endif

                        @if (hasPermission('lihat', 'web-manage'))
                            <li class="sidenav-item {{ request()->routeIs('produk_foto.*') ? 'active' : '' }}">
                                <a href="{{ route('produk_foto.index') }}" class="sidenav-link">
                                    <i class="sidenav-icon feather icon-image"></i>
                                    <div>Produk Foto</div>
                                </a>
                            </li>
                        @endif

                    @endif
                </ul>
            </li>

            <li class="sidenav-item ">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-layers"></i>
                    <div>Marketing</div>
                </a>
                <ul class="sidenav-menu">
                    <!-- web-manage - WEB -->
                    @if (hasPermission('lihat', 'web-manage'))
                        <li class="sidenav-item {{ request()->routeIs('discount.*') ? 'active' : '' }}">
                            <a href="{{ route('discount.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-percent"></i>
                                <div>Discount Management</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'web-manage'))
                        <li class="sidenav-item {{ request()->routeIs('voucher.*') ? 'active' : '' }}">
                            <a href="{{ route('voucher.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-tag"></i>
                                <div>Voucher Management</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'web-manage'))
                        <li class="sidenav-item {{ request()->routeIs('user_voucher.*') ? 'active' : '' }}">
                            <a href="{{ route('user_voucher.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-users"></i>
                                <div>User Voucher</div>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

            @if (hasPermission('lihat', 'web-manage'))
                <li class="sidenav-item {{ request()->routeIs('chatbot_faq.*') ? 'active' : '' }}">
                    <a href="{{ route('chatbot_faq.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-message-square"></i>
                        <div>Chatbot</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'web-manage'))
                <li class="sidenav-item {{ request()->routeIs('banner.*') ? 'active' : '' }}">
                    <a href="{{ route('banner.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-image"></i>
                        <div>Kelola Banner</div>
                    </a>
                </li>
            @endif

        @endif

        <!-- KONFIGURASI -->
        @if (hasPermission('lihat', 'pengguna') ||
                hasPermission('lihat', 'hak_akses') ||
                hasPermission('lihat', 'role_hak_akses') ||
                hasPermission('lihat', 'backup_database') ||
                hasPermission('lihat', 'pengaturan_web'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-header small font-weight-semibold">Konfigurasi</li>

            @if (hasPermission('lihat', 'pengguna'))
                <li class="sidenav-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
                    <a href="{{ route('pengguna.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-users"></i>
                        <div>Pengguna</div>
                    </a>
                </li>
            @endif


            @if (hasPermission('lihat', 'hak_akses'))
                <li class="sidenav-item {{ request()->routeIs('hak_akses.*') ? 'active' : '' }}">
                    <a href="{{ route('hak_akses.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-shield"></i>
                        <div>Hak Akses</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'role_hak_akses'))
                <li class="sidenav-item {{ request()->routeIs('role_hak_akses.*') ? 'active' : '' }}">
                    <a href="{{ route('role_hak_akses.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-lock"></i>
                        <div>Mapping Role Akses</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'backup_database'))
                <li class="sidenav-item {{ request()->routeIs('backup.*') ? 'active' : '' }}">
                    <a href="{{ route('backup.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-archive" data-feather="database" width="16" height="16"></i>
                        <div>Backup Database</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'pengaturan_web'))
                <li class="sidenav-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
                    <a href="{{ route('notifikasi.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-bell" data-feather="bell" width="16" height="16"></i>
                        <div>Notifikasi</div>
                    </a>
                </li>
            @endif

            @if (hasPermission('lihat', 'pengaturan_web'))
                <li class="sidenav-item {{ request()->routeIs('pengaturan_web.*') ? 'active' : '' }}">
                    <a href="{{ route('pengaturan_web.index') }}" class="sidenav-link">
                        <i class="sidenav-icon feather icon-settings"></i>
                        <div>Pengaturan Web</div>
                    </a>
                </li>
            @endif
        @endif

    </ul>

</div>
