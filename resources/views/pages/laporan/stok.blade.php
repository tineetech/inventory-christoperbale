@extends('layouts.main')

@php
    $statusLabels = [
        'semua' => 'Semua',
        'aman' => 'Aman',
        'minimum' => 'Minimum',
        'habis' => 'Habis',
    ];
@endphp

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
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.stok') }}">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Dari Tanggal</label>
                                <input type="date" class="form-control" name="dari_tanggal"
                                    value="{{ $filters['dari_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="sampai_tanggal"
                                    value="{{ $filters['sampai_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Nama Barang</label>
                                <select class="form-control" name="barang_id">
                                    <option value="">(Semua)</option>
                                    @foreach ($barangOptions as $option)
                                        <option value="{{ $option->id }}"
                                            {{ (string) $filters['barang_id'] === (string) $option->id ? 'selected' : '' }}>
                                            {{ $option->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Status Stok</label>
                                <select class="form-control" name="status">
                                    @foreach ($statusLabels as $value => $label)
                                        <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>
                                            ({{ $label }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end" style="gap: 10px;">
                            <a href="{{ route('laporan.stok.print', $filters) }}" target="_blank" class="btn btn-success">
                                <i class="feather icon-printer"></i> Print
                            </a>
                            <a href="{{ route('laporan.stok.excel', $filters) }}" class="btn btn-warning text-white">
                                <i class="feather icon-download"></i> Excel
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="feather icon-refresh-cw"></i> Proses
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-archive mr-2"></i> Data Laporan Stok
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <span class="badge badge-light">{{ $barang->count() }} barang</span>
                        <small class="text-muted">
                            Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                        </small>
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
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data stok pada filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
