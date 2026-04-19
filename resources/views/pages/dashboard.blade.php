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
<h6 class="mb-0 text-muted">Total <span class="text-primary">Supplier</span></h6>
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
<h6 class="mb-0 text-muted">Total <span class="text-success">Dropshipper</span></h6>
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
                                <span class="switcher switcher-primary switcher-sm d-inline-block align-middle mr-0 ml-2"><input type="checkbox" class="switcher-input" checked><span class="switcher-indicator"><span class="switcher-yes"></span><span
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
<div class="row">

<div class="col-md-6">
<div class="card d-flex w-100 mb-4">
<div class="row no-gutters row-bordered row-border-light h-100">

<!-- TOTAL SUPPLIER -->
<div class="d-flex col-lg-6 align-items-center">
<div class="card-body media align-items-center text-dark">
<i class="lnr lnr-store display-4 text-primary"></i>
<span class="media-body ml-3">
<span class="text-big mr-1 text-primary">{{ $totalSupplier }}</span>
<br>
<small class="text-muted">Supplier</small>
</span>
</div>
</div>

<!-- TOTAL DROPSHIPPER -->
<div class="d-flex col-lg-6 align-items-center">
<div class="card-body media align-items-center text-dark">
<i class="lnr lnr-user display-4 text-success"></i>
<span class="media-body ml-3">
<span class="text-big mr-1 text-success">{{ $totalDropshipper }}</span>
<br>
<small class="text-muted">Dropshipper</small>
</span>
</div>
</div>

<!-- ADJUSTMENT -->
<div class="d-flex col-lg-6 align-items-center">
<div class="card-body media align-items-center text-dark">
<i class="lnr lnr-cog display-4 text-danger"></i>
<span class="media-body ml-3">
<span class="text-big mr-1 text-danger">{{ $adjustHariIni }}</span>
<br>
<small class="text-muted">Adjustment Hari Ini</small>
</span>
</div>
</div>

<!-- STOK MOVEMENT -->
<div class="d-flex col-lg-6 align-items-center">
<div class="card-body media align-items-center text-dark">
<i class="lnr lnr-sync display-4 text-info"></i>
<span class="media-body ml-3">
<span class="text-big mr-1 text-info">{{ $stokMovementHariIni }}</span>
<br>
<small class="text-muted">Stok Movement</small>
</span>
</div>
</div>

</div>
</div>
</div>

<div class="col-md-6">

<div class="card mb-4 bg-primary text-white">
<div class="card-body">
<div class="d-flex align-items-center">
<div class="lnr lnr-cart display-4"></div>
<div class="ml-3">
<div class="small">Penjualan Hari Ini</div>
<div class="text-large">{{ $penjualanHariIni }}</div>
</div>
</div>
</div>
</div>

<div class="card mb-4">
<div class="card-body">
<div class="d-flex align-items-center">
<div class="lnr lnr-enter display-4 text-warning"></div>
<div class="ml-3">
<div class="text-muted small">Pembelian Hari Ini</div>
<div class="text-large">{{ $pembelianHariIni }}</div>
</div>
</div>
</div>
</div>

</div>

</div>

</div>

@include('components.footer')

</div>

@endsection