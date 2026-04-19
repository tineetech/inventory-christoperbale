@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Laporan Stok</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Stok</li>
                </ol>
            </div>

            <div class="card mb-4">
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-archive mr-2"></i> Data Laporan Stok
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <span class="badge badge-light">{{ $barang->count() }} barang</span>
                        <a href="{{ route('barang.create') }}" class="btn btn-sm text-white"
                            style="background: linear-gradient(135deg, #ff8a00, #ff5b00); border: none; box-shadow: 0 6px 14px rgba(255, 107, 0, 0.25);">
                            <i class="feather icon-plus"></i> Tambah Barang
                        </a>
                    </div>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>SKU</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Stok Saat Ini</th>
                                <th>Stok Minimum</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barang as $index => $item)
                                @php
                                    $stokSaatIni = $item->stok->jumlah_stok ?? 0;
                                    $minimum = $item->stok_minimum ?? 0;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->sku }}</td>
                                    <td><strong>{{ $item->nama_barang }}</strong></td>
                                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                                    <td>{{ $stokSaatIni }}</td>
                                    <td>{{ $minimum }}</td>
                                    <td>
                                        @if ($stokSaatIni <= 0)
                                            <span class="badge badge-danger">Habis</span>
                                        @elseif ($stokSaatIni <= $minimum)
                                            <span class="badge badge-warning">Minimum</span>
                                        @else
                                            <span class="badge badge-success">Aman</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
