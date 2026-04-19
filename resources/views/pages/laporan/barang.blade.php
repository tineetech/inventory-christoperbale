@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Laporan Barang</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Barang</li>
                </ol>
            </div>

            <div class="card mb-4">
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-box mr-2"></i> Data Laporan Barang
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
                                <th>Harga 1</th>
                                <th>Harga 2</th>
                                <th>Stok</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($barang as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->sku }}</td>
                                    <td><strong>{{ $item->nama_barang }}</strong></td>
                                    <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                                    <td>{{ number_format($item->harga_1) }}</td>
                                    <td>{{ number_format($item->harga_2) }}</td>
                                    <td>{{ $item->stok->jumlah_stok ?? 0 }}</td>
                                    <td>{{ $item->keterangan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
