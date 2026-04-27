@extends('layouts.main')

@section('style')
    <style>
        .main-row {
            cursor: pointer;
        }

        .main-row .kode-click {
            color: #00499b;
            text-decoration: underline;
        }

        .detail-row {
            display: none;
            background: #f9f9f9;
        }
    </style>
@endsection

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
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.penjualan') }}" id="penjualanFilterForm">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Dari Tanggal</label>
                                <input type="date" class="form-control" name="dari_tanggal"
                                    value="{{ $filters['dari_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="sampai_tanggal"
                                    value="{{ $filters['sampai_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Dropshipper</label>
                                <select class="form-control" name="dropshipper_id">
                                    <option value="">(Semua)</option>
                                    @foreach ($dropshipperOptions as $option)
                                        <option value="{{ $option->id }}"
                                            {{ (string) $filters['dropshipper_id'] === (string) $option->id ? 'selected' : '' }}>
                                            {{ $option->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end" style="gap: 10px;">
                            <a href="{{ route('laporan.penjualan.print', $filters) }}" target="_blank" class="btn btn-success">
                                <i class="feather icon-printer"></i> Print
                            </a>
                            <a href="{{ route('laporan.penjualan.pdf', $filters) }}" class="btn btn-danger">
                                <i class="feather icon-file-text"></i> PDF
                            </a>
                            <a href="{{ route('laporan.penjualan.excel', $filters) }}" class="btn btn-warning text-white">
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
                        <i class="feather icon-file-text mr-2"></i> Data Laporan Penjualan
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <span class="badge badge-light">{{ $penjualan->count() }} transaksi</span>
                        <small class="text-muted">
                            Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                        </small>
                    </div>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0" id="penjualanTable">
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
                                <tr class="main-row" data-report-main="true" data-id="{{ $item->id }}"
                                    data-expanded="false">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="kode-click"><strong>{{ $item->kode_penjualan }}</strong></td>
                                    <td>{{ $item->dropshipper->nama ?? '-' }}</td>
                                    <td>{{ date('d M Y', strtotime($item->tanggal)) }}</td>
                                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    <td>{{ $item->user->nama ?? '-' }}</td>
                                    <td>{{ $item->keterangan ?: '-' }}</td>
                                </tr>
                                <tr class="detail-row" data-report-detail="true" id="detail-{{ $item->id }}"
                                    style="display:none; background:#f9f9f9;">
                                    <td colspan="7">
                                        <div class="p-3">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>No Resi</th>
                                                        <th>SKU</th>
                                                        <th>Nama Barang</th>
                                                        <th>Stok Sekarang</th>
                                                        <th>Qty Terjual</th>
                                                        <th>Harga</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $totalDetail = 0;
                                                    @endphp

                                                    @forelse ($item->detail as $detail)
                                                        @php
                                                            $totalDetail += $detail->subtotal;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $detail->nomor_resi ?: '-' }}</td>
                                                            <td>{{ $detail->barang->sku ?? '-' }}</td>
                                                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                                            <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td>
                                                            <td>{{ $detail->qty }}</td>
                                                            <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">
                                                                Belum ada detail barang pada transaksi ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                    @if ($item->detail->isNotEmpty())
                                                        <tr style="background:#f1f1f1;font-weight:bold">
                                                            <td colspan="6" class="text-right">Total Penjualan</td>
                                                            <td>Rp {{ number_format($totalDetail, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row="true">
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data penjualan pada filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('pages.laporan.partials.pagination-controls', [
                    'prefix' => 'penjualan',
                    'perPage' => $filters['per_page'],
                    'totalRows' => $penjualan->count(),
                    'formId' => 'penjualanFilterForm',
                ])
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('pages.laporan.partials.pagination-script')
    <script>
        window.initReportPagination({
            tableIds: ['penjualanTable'],
            entriesSelectId: 'penjualanEntriesSelect',
            paginationId: 'penjualanPagination',
            tableInfoId: 'penjualanTableInfo',
            formId: 'penjualanFilterForm',
            mainRowSelector: 'tbody tr[data-report-main]',
            detailRowSelector: 'tbody tr[data-report-detail]'
        });
    </script>
@endsection
