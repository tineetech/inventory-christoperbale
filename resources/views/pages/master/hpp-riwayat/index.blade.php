@extends('layouts.main')

@section('style')
    <style>
        .row-has-detail td {
            background: #eef6ff;
        }
        .main-row.row-has-detail:hover td {
            background: #e2f0ff;
        }
    </style>
@endsection

@php
if (!function_exists('sortUrl')) {
    function sortUrl($col) {
        $params = request()->all();
        $params['sort_col'] = $col;
        $params['sort_dir'] = request('sort_col') === $col && request('sort_dir') === 'asc' ? 'desc' : 'asc';
        return url()->current() . '?' . http_build_query($params);
    }
}
if (!function_exists('sortIcon')) {
    function sortIcon($col) {
        if (request('sort_col') === $col) {
            $dir = request('sort_dir') === 'asc' ? 'up' : 'down';
            return '<i class="feather icon-chevron-' . $dir . ' sort-icon"></i>';
        }
        return '<i class="feather icon-chevrons-up sort-icon"></i>';
    }
}
@endphp

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Riwayat HPP</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Riwayat HPP</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="col-sm-12">
                                <div class="card mb-4">

                                    {{-- CARD HEADER --}}
                                    <div style="border:none !important" class="card-header">

                                        {{-- Row 1: Title --}}
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-row sm-flex-col">
                                            <h6 class="card-header-title mb-0 text-warning">
                                                <i class="feather icon-trending-up mr-2"></i> Data Riwayat HPP
                                                <small class="text-muted">Log perubahan harga HPP barang</small>
                                            </h6>
                                            <span class="badge badge-warning">{{ $hppRiwayat->total() }} riwayat</span>
                                        </div>

                                        {{-- Filter Form --}}
                                        <form method="GET" id="filterForm" class="d-flex flex-wrap align-items-center" style="gap:6px">

                                            {{-- Search + tombol Cari/Reset --}}
                                            <div class="d-flex flex-wrap align-items-center w-100" style="gap:6px">
                                                <div class="input-group input-group-sm flex-grow-1" style="min-width:180px">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="feather icon-search"></i></span>
                                                    </div>
                                                    <input type="text" name="search" class="form-control" id="searchTable"
                                                        placeholder="Cari SKU / nama barang..."
                                                        value="{{ request('search') }}">
                                                </div>

                                                <div class="d-flex align-items-center flex-shrink-0" style="gap:6px">
                                                    <button type="submit" class="btn btn-sm btn-secondary">
                                                        <i class="feather icon-search"></i>
                                                        <span class="d-none d-sm-inline">Cari</span>
                                                    </button>
                                                    @if (request('search'))
                                                        <a href="{{ route('hpp-riwayat.index', ['per_page' => request('per_page', 10)]) }}"
                                                            class="btn btn-sm btn-outline-secondary">
                                                            <i class="feather icon-x"></i>
                                                            <span class="d-none d-sm-inline">Reset</span>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            <input type="hidden" name="sort_col" value="{{ request('sort_col') }}">
                                            <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                                            <input type="hidden" name="per_page" id="perPageInput" value="{{ request('per_page', 10) }}">
                                        </form>

                                    </div>

                                    <div class="nav-tabs-top">
                                        <div class="tab-content" style="width:100%">
                                            <div class="tab-pane fade show active pb-4 px-2 px-md-4" id="sale-stats">
                                                <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                                                    <table class="table table-modern table-hover" id="table"
                                                        style="min-width:720px">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:50px">No</th>
                                                                <th>SKU</th>
                                                                <th>Nama Barang</th>
                                                                <th>
                                                                    <a href="{{ sortUrl('hpp_lama') }}" style="color:inherit">
                                                                        HPP Lama
                                                                        {!! sortIcon('hpp_lama') !!}
                                                                    </a>
                                                                </th>
                                                                <th>
                                                                    <a href="{{ sortUrl('hpp_baru') }}" style="color:inherit">
                                                                        HPP Baru
                                                                        {!! sortIcon('hpp_baru') !!}
                                                                    </a>
                                                                </th>
                                                                <th>Selisih</th>
                                                                <th>
                                                                    <a href="{{ sortUrl('tanggal') }}" style="color:inherit">
                                                                        Tanggal
                                                                        {!! sortIcon('tanggal') !!}
                                                                    </a>
                                                                </th>
                                                                <th class="d-none d-md-table-cell">Dibuat Oleh</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tableBody">
                                                            @forelse ($hppRiwayat as $r)
                                                                <tr class="main-row {{ $r->detail->isNotEmpty() ? 'row-has-detail' : '' }}"
                                                                    data-id="{{ $r->id }}" style="cursor:pointer">
                                                                    <td>
                                                                        {{ $hppRiwayat->firstItem() + $loop->index }}
                                                                        @if ($r->detail->isNotEmpty())
                                                                            <i class="feather icon-chevrons-down ml-1 float-right"
                                                                                style="color:#00499b;font-size:12px"></i>
                                                                        @endif
                                                                    </td>
                                                                    <td style="white-space:nowrap">{{ $r->barang->sku ?? '-' }}</td>
                                                                    <td>
                                                                        {{ \Illuminate\Support\Str::limit($r->barang->nama_barang ?? '-', 28, '...') }}
                                                                        @if ($r->detail->isNotEmpty())
                                                                            <span class="badge badge-info ml-1" title="Klik row untuk lihat rincian biaya">
                                                                                {{ $r->detail->count() }} biaya
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td style="white-space:nowrap">Rp {{ number_format($r->hpp_lama, 0, ',', '.') }}</td>
                                                                    <td style="font-weight:bold;white-space:nowrap">Rp {{ number_format($r->hpp_baru, 0, ',', '.') }}</td>
                                                                    <td style="white-space:nowrap">
                                                                        @php $selisih = $r->hpp_baru - $r->hpp_lama; @endphp
                                                                        <span class="badge {{ $selisih >= 0 ? 'badge-success' : 'badge-danger' }}">
                                                                            {{ $selisih >= 0 ? '+' : '' }} Rp {{ number_format($selisih, 0, ',', '.') }}
                                                                        </span>
                                                                    </td>
                                                                    <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y H:i') }}</td>
                                                                    <td class="d-none d-md-table-cell">{{ $r->user->nama ?? '-' }}</td>
                                                                </tr>
                                                                @if ($r->detail->isNotEmpty())
                                                                    <tr class="detail-row" id="detail-{{ $r->id }}" style="display:none;background:#f9f9f9">
                                                                        <td colspan="8">
                                                                            <div class="p-2 p-md-3" style="overflow-x:auto">
                                                                                <div class="mb-2">
                                                                    <span class="badge" style="background:#00499b;color:#fff">
                                                                        {{ $r->detail->count() }} biaya pembentuk HPP
                                                                    </span>
                                                                </div>
                                                                                <table class="table table-sm table-bordered mb-0" style="min-width:400px">
                                                                                    <thead class="thead-light">
                                                                                        <tr>
                                                                                            <th>No</th><th>Nama Biaya</th><th>Harga</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        @php $totalDetail = 0; @endphp
                                                                                        @foreach ($r->detail as $d)
                                                                                            @php $totalDetail += $d->harga; @endphp
                                                                                            <tr>
                                                                                                <td>{{ $loop->iteration }}</td>
                                                                                                <td>{{ $d->nama_biaya }}</td>
                                                                                                <td>Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                        <tr style="background:#f1f1f1;font-weight:bold">
                                                                                            <td colspan="2" class="text-right">Total Biaya</td>
                                                                                            <td>Rp {{ number_format($totalDetail, 0, ',', '.') }}</td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @empty
                                                                <tr>
                                                                    <td colspan="8" class="text-center text-muted py-4">
                                                                        <i class="feather icon-inbox mr-2"></i>
                                                                        Tidak ada data riwayat HPP
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>

                                                {{-- Pagination & Info --}}
                                                <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top"
                                                    style="gap:8px">
                                                    <div class="d-flex align-items-center">
                                                        <span class="mr-2 text-muted small">Show</span>
                                                        <select class="form-control form-control-sm" id="entriesSelect"
                                                            style="width:72px">
                                                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                                        </select>
                                                        <span class="ml-2 text-muted small">entries</span>
                                                    </div>
                                                    <div class="text-muted small">
                                                        @if ($hppRiwayat->total() > 0)
                                                            Showing <strong>{{ $hppRiwayat->firstItem() }}</strong>
                                                            to <strong>{{ $hppRiwayat->lastItem() }}</strong>
                                                            of <strong>{{ $hppRiwayat->total() }}</strong> entries
                                                        @else
                                                            No entries found
                                                        @endif
                                                    </div>
                                                    <nav>
                                                        <ul class="pagination pagination-sm mb-0">
                                                            <li class="page-item {{ $hppRiwayat->onFirstPage() ? 'disabled' : '' }}">
                                                                <a class="page-link" href="{{ $hppRiwayat->previousPageUrl() }}">
                                                                    <i class="feather icon-chevron-left"></i>
                                                                </a>
                                                            </li>

                                                            @php
                                                                $currentPage = $hppRiwayat->currentPage();
                                                                $lastPage = $hppRiwayat->lastPage();
                                                                $start = max(1, $currentPage - 2);
                                                                $end = min($lastPage, $currentPage + 2);
                                                                if ($start <= 3) $end = min($lastPage, 5);
                                                                if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                                                            @endphp

                                                            @if ($lastPage > 7 && $start > 1)
                                                                <li class="page-item">
                                                                    <a class="page-link" href="{{ $hppRiwayat->url(1) }}">1</a>
                                                                </li>
                                                                @if ($start > 2)
                                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                                @endif
                                                            @endif

                                                            @for ($i = $start; $i <= $end; $i++)
                                                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                                    <a class="page-link" href="{{ $hppRiwayat->url($i) }}">{{ $i }}</a>
                                                                </li>
                                                            @endfor

                                                            @if ($lastPage > 7 && $end < $lastPage)
                                                                @if ($end < $lastPage - 1)
                                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                                @endif
                                                                <li class="page-item">
                                                                    <a class="page-link" href="{{ $hppRiwayat->url($lastPage) }}">{{ $lastPage }}</a>
                                                                </li>
                                                            @endif

                                                            <li class="page-item {{ !$hppRiwayat->hasMorePages() ? 'disabled' : '' }}">
                                                                <a class="page-link" href="{{ $hppRiwayat->nextPageUrl() }}">
                                                                    <i class="feather icon-chevron-right"></i>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </nav>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

@section('scripts')
    <script>
        // =====================================================
        // EXPAND DETAIL ROW
        // =====================================================
        document.getElementById('tableBody').addEventListener('click', function(e) {
            const row = e.target.closest('.main-row');
            if (!row) return;
            if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;

            const id = row.getAttribute('data-id');
            const detailRow = document.getElementById('detail-' + id);
            if (!detailRow) return;

            detailRow.style.display = detailRow.style.display === 'table-row' ? 'none' : 'table-row';
        });

        // =====================================================
        // ENTRIES SELECT
        // =====================================================
        document.getElementById('entriesSelect').addEventListener('change', function() {
            document.getElementById('perPageInput').value = this.value;
            document.getElementById('filterForm').submit();
        });
    </script>
@endsection