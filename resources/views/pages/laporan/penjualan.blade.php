@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Laporan Penjualan</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Penjualan</li>
                </ol>
            </div>

            <div class="card mb-4">
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-file-text mr-2"></i> Data Laporan Penjualan
                    </h6>
                    <span class="badge badge-light">{{ $penjualan->count() }} transaksi</span>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Penjualan</th>
                                <th>Dropshipper</th>
                                <th>Tanggal</th>
                                <th>Total Harga</th>
                                <th>Dibuat Oleh</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penjualan as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $item->kode_penjualan }}</strong></td>
                                    <td>{{ $item->dropshipper->nama ?? '-' }}</td>
                                    <td>{{ date('d M Y', strtotime($item->tanggal)) }}</td>
                                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    <td>{{ $item->user->nama ?? '-' }}</td>
                                    <td>{{ $item->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data penjualan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
