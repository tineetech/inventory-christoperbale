@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Dashboard</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>

            <div class="row">

                <!-- LEFT SIDE -->
                <div class="col-lg-5">
                    <div class="row">

                        <!-- TOTAL BARANG -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-2">{{ $totalBarang }}</h2>
                                            <p class="text-muted mb-0">
                                                <span class="badge badge-primary">Barang</span> Terdaftar
                                            </p>
                                        </div>
                                        <div class="lnr lnr-database display-4 text-primary"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TOTAL STOK -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-2">{{ $totalStok }}</h2>
                                            <p class="text-muted mb-0">
                                                <span class="badge badge-success">Stok</span> Gudang
                                            </p>
                                        </div>
                                        <div class="lnr lnr-layers display-4 text-success"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PENJUALAN HARI INI -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-2">{{ $penjualanHariIni }}</h2>
                                            <p class="text-muted mb-0">
                                                <span class="badge badge-danger">Penjualan</span> Hari Ini
                                            </p>
                                        </div>
                                        <div class="lnr lnr-cart display-4 text-danger"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PEMBELIAN HARI INI -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-2">{{ $pembelianHariIni }}</h2>
                                            <p class="text-muted mb-0">
                                                <span class="badge badge-warning">Pembelian</span> Hari Ini
                                            </p>
                                        </div>
                                        <div class="lnr lnr-enter display-4 text-warning"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INFO TAMBAHAN -->
                        <div class="col-sm-12">
                            <div class="card d-flex w-100 mb-4">
                                <div class="row no-gutters row-bordered row-border-light h-100">

                                    <!-- SUPPLIER -->
                                    <div class="d-flex col-md-6 align-items-center">
                                        <div class="card-body">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-auto">
                                                    <i class="lnr lnr-store text-primary display-4"></i>
                                                </div>
                                                <div class="col">
                                                    <h6 class="mb-0 text-muted">Total <span
                                                            class="text-primary">Supplier</span></h6>
                                                    <h4 class="mt-3 mb-0">{{ $totalSupplier }}</h4>
                                                </div>
                                            </div>
                                            <p class="mb-0 text-muted">Supplier terdaftar</p>
                                        </div>
                                    </div>

                                    <!-- DROPSHIPPER -->
                                    <div class="d-flex col-md-6 align-items-center">
                                        <div class="card-body">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-auto">
                                                    <i class="lnr lnr-user text-success display-4"></i>
                                                </div>
                                                <div class="col">
                                                    <h6 class="mb-0 text-muted">Total <span
                                                            class="text-success">Dropshipper</span></h6>
                                                    <h4 class="mt-3 mb-0">{{ $totalDropshipper }}</h4>
                                                </div>
                                            </div>
                                            <p class="mb-0 text-muted">Dropshipper aktif</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT SIDE -->

                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header with-elements">
                            <h6 class="card-header-title mb-0">Statistics</h6>
                            <div class="card-header-elements ml-auto">
                                <label class="text m-0">
                                    <span class="text-light text-tiny font-weight-semibold align-middle">SHOW STATS</span>
                                    <span
                                        class="switcher switcher-primary switcher-sm d-inline-block align-middle mr-0 ml-2"><input
                                            type="checkbox" class="switcher-input" checked><span
                                            class="switcher-indicator"><span class="switcher-yes"></span><span
                                                class="switcher-no"></span></span></span>
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="statistics-chart-1" style="height:300px"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- BOTTOM SECTION -->
            {{-- Stok Kritis --}}
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center" style="border:none">
                            <h6 class="mb-0 text-danger">
                                <i class="feather icon-alert-triangle mr-2"></i> Stok Kritis
                                <small class="text-muted">Stok dibawah minimum dan dibawah 10 yang perlu di restock</small>
                            </h6>
                            <span class="badge badge-danger">{{ $stokKritis->count() }} barang</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>SKU</th>
                                        <th>Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Stok Minimum</th>
                                        <th>Stok Sekarang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($stokKritis as $index => $item)
                                        <tr onclick="window.open('/master/barang/edit/' + {{ $item->id }})" style="cursor: pointer">
                                            <td>{{ $index + 1 }}</td>
                                            <td><code>{{ $item->sku }}</code></td>
                                            <td><strong>{{ $item->nama_barang }}</strong></td>
                                            <td>{{ $item->satuan->nama_satuan }}</td>
                                            <td>{{ $item->stok_minimum }}</td>
                                            <td>
                                                <span class="text-danger font-weight-bold">
                                                    {{ $item->stok->jumlah_stok ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $stokNow = $item->stok->jumlah_stok ?? 0;
                                                @endphp

                                                @if ($stokNow == 0)
                                                    <span class="badge badge-danger">Habis</span>
                                                @else
                                                    <span class="badge badge-warning">Kritis</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="feather icon-check-circle text-success mr-2"></i>
                                                Semua stok aman
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        @include('components.footer')

    </div>
@endsection
