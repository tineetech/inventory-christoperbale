<style>
    #layout-sidenav .sidenav-menu {
        margin-left: 0.75rem;
        margin-right: 0.75rem;
        background-color: rgba(33, 37, 41, 0.035);
        border-radius: 0.5rem;
    }

    #layout-sidenav .sidenav-menu .sidenav-menu {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
        background-color: rgba(33, 37, 41, 0.035);
    }

    #layout-sidenav .sidenav-menu .sidenav-link {
        width: 100%;
    }

    #layout-sidenav .sidenav-item.active-parent > .sidenav-toggle {
        width: 100%;
        background-color: rgba(255, 122, 0, 0.06);
        /* border-radius: 0.375rem; */
        box-shadow: inset 3px 0 0 0 #ff7a00;
    }

    #layout-sidenav .sidenav-inner > .sidenav-item.active > .sidenav-link {
        width: 100%;
        background-color: rgba(255, 122, 0, 0.08);
        box-shadow: inset 3px 0 0 0 #ff7a00;
    }

    #layout-sidenav .sidenav-menu .sidenav-item.active > .sidenav-link {
        font-weight: 700;
        border-radius: 0.375rem;
    }

    #layout-sidenav .sidenav-menu .sidenav-link:hover,
    #layout-sidenav .sidenav-menu .sidenav-link:focus {
        font-weight: 600;
    }
</style>

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

    @php
        $masterOpen = request()->routeIs('supplier.*', 'satuan.*', 'barang.*', 'hpp-riwayat.*', 'dropshipper.*');
        $transaksiOpen = request()->routeIs('pembelian.*', 'penjualan.*', 'pembayaran.*', 'manage-stok.*', 'hitung-stok.*');
        $laporanOpen = request()->routeIs('laporan.*');
        $webOpen = request()->routeIs(
            'brand.*',
            'produk.*',
            'produk_varian.*',
            'produk_foto.*',
            'discount.*',
            'voucher.*',
            'user_voucher.*',
            'chatbot_faq.*',
            'banner.*'
        );
        $produkGroupOpen = request()->routeIs('produk.*', 'produk_varian.*', 'produk_foto.*');
        $marketingGroupOpen = request()->routeIs('discount.*', 'voucher.*', 'user_voucher.*');
        $konfigOpen = request()->routeIs(
            'pengguna.*',
            'hak_akses.*',
            'role_hak_akses.*',
            'backup.*',
            'notifikasi.*',
            'pengaturan_web.*'
        );
    @endphp

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
            <li class="sidenav-item {{ $masterOpen ? 'open active-parent' : '' }}">
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

                    @if (hasPermission('lihat', 'dashboard_trx'))
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
                hasPermission('lihat', 'manajemen_stok') ||
                hasPermission('lihat', 'hitung_stok'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-item {{ $transaksiOpen ? 'open active-parent' : '' }}">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-shopping-cart"></i>
                    <div>Transaksi</div>
                </a>
                <ul class="sidenav-menu">

                    @if (hasPermission('lihat', 'pembelian'))
                        <li class="sidenav-item {{ request()->routeIs('pembelian.*') ? 'active' : '' }}">
                            <a href="{{ route('pembelian.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-shopping-cart"></i>
                                <div>Pembelian</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'penjualan'))
                        <li class="sidenav-item {{ request()->routeIs('penjualan.*') && !request()->routeIs('penjualan.draft') && !request()->routeIs('penjualan.web') ? 'active' : '' }}">
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

                </ul>
            </li>
        @endif

        <!-- LAPORAN -->
        @if (hasPermission('lihat', 'laporan_pembelian') ||
                hasPermission('lihat', 'laporan_penjualan') ||
                hasPermission('lihat', 'laporan_stok') ||
                hasPermission('lihat', 'laporan_barang') ||
                hasPermission('lihat', 'laporan_retur'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-item {{ $laporanOpen ? 'open active-parent' : '' }}">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-bar-chart-2"></i>
                    <div>Laporan</div>
                </a>
                <ul class="sidenav-menu">

                    @if (hasPermission('lihat', 'laporan_pembelian'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.pembelian*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.pembelian') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-file-text"></i>
                                <div>Lap. Pembelian</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_penjualan'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">
                            <a href="{{ route('laporan.penjualan') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-file-text"></i>
                                <div>Lap. Penjualan</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_penjualan'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.penjualan-web*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.penjualan-web') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-globe"></i>
                                <div>Lap. Penjualan Web</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_penjualan'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.pembayaran*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.pembayaran') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-credit-card"></i>
                                <div>Lap. Pembayaran</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_stok'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
                            <a href="{{ route('laporan.stok') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-file-text"></i>
                                <div>Lap. Stok</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_stok'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.stok-kritis*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.stok-kritis') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-file-text"></i>
                                <div>Lap. Stok Kritis</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_barang'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.barang*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.barang') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-file-text"></i>
                                <div>Lap. Barang</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'laporan_retur'))
                        <li class="sidenav-item {{ request()->routeIs('laporan.retur*') ? 'active' : '' }}">
                            <a href="{{ route('laporan.retur') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-file-text"></i>
                                <div>Lap. Retur Penjualan</div>
                            </a>
                        </li>
                    @endif

                </ul>
            </li>
        @endif

        <!-- WEB MANAGEMENT -->
        @if (hasPermission('lihat', 'web-manage'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-item {{ $webOpen ? 'open active-parent' : '' }}">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-globe"></i>
                    <div>Web Management</div>
                </a>
                <ul class="sidenav-menu">

                    @if (hasPermission('lihat', 'web-manage'))
                        <li class="sidenav-item {{ request()->routeIs('brand.*') ? 'active' : '' }}">
                            <a href="{{ route('brand.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-tag"></i>
                                <div>Brand Management</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'web-manage'))
                        <li class="sidenav-item {{ $produkGroupOpen ? 'open active-parent' : '' }}">
                            <a href="javascript:" class="sidenav-link sidenav-toggle">
                                <i class="sidenav-icon feather icon-layers"></i>
                                <div>Produk Management</div>
                            </a>
                            <ul class="sidenav-menu">
                                <li class="sidenav-item {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                                    <a href="{{ route('produk.index') }}" class="sidenav-link">
                                        <i class="sidenav-icon feather icon-package"></i>
                                        <div>Produk</div>
                                    </a>
                                </li>

                                <li class="sidenav-item {{ request()->routeIs('produk_varian.*') ? 'active' : '' }}">
                                    <a href="{{ route('produk_varian.index') }}" class="sidenav-link">
                                        <i class="sidenav-icon feather icon-layers"></i>
                                        <div>Produk Varian</div>
                                    </a>
                                </li>

                                <li class="sidenav-item {{ request()->routeIs('produk_foto.*') ? 'active' : '' }}">
                                    <a href="{{ route('produk_foto.index') }}" class="sidenav-link">
                                        <i class="sidenav-icon feather icon-image"></i>
                                        <div>Produk Foto</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'web-manage'))
                        <li class="sidenav-item {{ $marketingGroupOpen ? 'open active-parent' : '' }}">
                            <a href="javascript:" class="sidenav-link sidenav-toggle">
                                <i class="sidenav-icon feather icon-layers"></i>
                                <div>Marketing</div>
                            </a>
                            <ul class="sidenav-menu">
                                <li class="sidenav-item {{ request()->routeIs('discount.*') ? 'active' : '' }}">
                                    <a href="{{ route('discount.index') }}" class="sidenav-link">
                                        <i class="sidenav-icon feather icon-percent"></i>
                                        <div>Discount Management</div>
                                    </a>
                                </li>

                                <li class="sidenav-item {{ request()->routeIs('voucher.*') ? 'active' : '' }}">
                                    <a href="{{ route('voucher.index') }}" class="sidenav-link">
                                        <i class="sidenav-icon feather icon-tag"></i>
                                        <div>Voucher Management</div>
                                    </a>
                                </li>

                                <li class="sidenav-item {{ request()->routeIs('user_voucher.*') ? 'active' : '' }}">
                                    <a href="{{ route('user_voucher.index') }}" class="sidenav-link">
                                        <i class="sidenav-icon feather icon-users"></i>
                                        <div>User Voucher</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

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

                </ul>
            </li>
        @endif

        <!-- KONFIGURASI -->
        @if (hasPermission('lihat', 'pengguna') ||
                hasPermission('lihat', 'hak_akses') ||
                hasPermission('lihat', 'role_hak_akses') ||
                hasPermission('lihat', 'backup_database') ||
                hasPermission('lihat', 'pengaturan_web'))
            <li class="sidenav-divider mb-1"></li>
            <li class="sidenav-item {{ $konfigOpen ? 'open active-parent' : '' }}">
                <a href="javascript:" class="sidenav-link sidenav-toggle">
                    <i class="sidenav-icon feather icon-settings"></i>
                    <div>Konfigurasi</div>
                </a>
                <ul class="sidenav-menu">

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
                                <i class="sidenav-icon feather icon-lock"></i>
                                <div>Backup Database</div>
                            </a>
                        </li>
                    @endif

                    @if (hasPermission('lihat', 'pengaturan_web'))
                        <li class="sidenav-item {{ request()->routeIs('notifikasi.*') ? 'active' : '' }}">
                            <a href="{{ route('notifikasi.index') }}" class="sidenav-link">
                                <i class="sidenav-icon feather icon-bell" ></i>
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

                </ul>
            </li>
        @endif

    </ul>

</div>
