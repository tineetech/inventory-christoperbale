@extends('layouts.main')

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
            <h4 class="font-weight-bold py-3 mb-0">Penjualan</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item active">Penjualan</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12">

                            @if (session('success'))
                                <div class="card mb-4 border-success js-alert-success">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-1 text-success"><i class="feather icon-check-circle"></i> Success
                                            </h5>
                                            <p class="mb-0 text-muted">{{ session('success') }}</p>
                                        </div>
                                        <div class="display-4 text-success"><i class="feather icon-check-circle"></i></div>
                                    </div>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="card mb-4 border-danger">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-1 text-danger"><i class="feather icon-x-circle"></i> Error</h5>
                                            <p class="mb-0 text-muted">{{ session('error') }}</p>
                                        </div>
                                        <div class="display-4 text-danger"><i class="feather icon-x-circle"></i></div>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="col-sm-12">
                            <div class="card mb-4">

                                {{-- CARD HEADER --}}
                                <div style="border:none !important" class="card-header">

                                    {{-- Row 1: Title + Tambah button --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-row sm-flex-col">
                                        <h6 class="card-header-title mb-0">
                                            <i class="feather icon-truck mr-2"></i> Data Penjualan
                                        </h6>
                                        @if(hasPermission('tambah', 'penjualan'))
                                        <div class="d-flex g-5">

                                            <button id="btnBulkDownload"
                                                    class="btn btn-secondary btn-sm d-none"
                                                    onclick="bulkDownloadStruk()">
                                                <i class="feather icon-download"></i>
                                                <span class="d-none d-sm-inline">Download Struk (<span id="selectedCount">0</span>)</span>
                                                <span class="d-inline d-sm-none">DL (<span class="selectedCountMobile">0</span>)</span>
                                            </button>
                                            <button id="btnBulkDelete"
                                                    class="btn btn-danger btn-sm d-none"
                                                    onclick="bulkDelete()">
                                                <i class="feather icon-trash"></i>
                                                <span class="d-none d-sm-inline">Hapus (<span id="selectedCountDelete">0</span>)</span>
                                                <span class="d-inline d-sm-none">Del (<span class="selectedCountDeleteMobile">0</span>)</span>
                                            </button>
                                            <a href="{{ route('penjualan.create.multiple') }}" class="btn btn-info btn-sm">
                                                <i class="feather icon-plus"></i>
                                                <span class="d-none d-sm-inline">Buat Penjualan</span>
                                                <span class="d-inline d-sm-none">Tambah</span>
                                            </a>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Filter Form --}}
                                    <form method="GET" id="filterForm" class="d-flex flex-wrap align-items-center" style="gap:6px">

                                        {{-- Baris 1: Tanggal & Waktu --}}
                                        <div class="d-flex flex-wrap align-items-center w-100" style="gap:6px">
                                            <input type="date" name="date_from" class="form-control form-control-sm"
                                                style="min-width:130px;flex:1 1 130px" value="{{ request('date_from', today()->format('Y-m-d')) }}">
                                            <input type="time" name="time_from" class="form-control form-control-sm"
                                                style="min-width:100px;flex:1 1 100px" value="{{ request('time_from') }}">
                                            <span class="text-muted small">s/d</span>
                                            <input type="date" name="date_to" class="form-control form-control-sm"
                                                style="min-width:130px;flex:1 1 130px" value="{{ request('date_to', today()->format('Y-m-d')) }}">
                                            <input type="time" name="time_to" class="form-control form-control-sm"
                                                style="min-width:100px;flex:1 1 100px" value="{{ request('time_to') }}">
                                        </div>

                                        {{-- Baris 2: Search fullwidth --}}
                                        <div class="input-group input-group-sm w-100">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="feather icon-search"></i></span>
                                            </div>
                                            <input type="text" name="search" class="form-control" id="searchTable"
                                                placeholder="Cari kode penjualan, nomor resi, dropshipper..."
                                                value="{{ request('search') }}">
                                        </div>

                                        {{-- Baris 3: Dropdown filter --}}
                                        <div class="d-flex flex-wrap w-100" style="gap:6px">
                                            <select name="dropshipper" class="form-control form-control-sm auto-submit"
                                                style="min-width:140px;flex:2 1 160px">
                                                <option value="">-- Semua Dropshipper --</option>
                                                @foreach($dropshippers as $ds)
                                                    <option value="{{ $ds->nama }}" {{ request('dropshipper') == $ds->nama ? 'selected' : '' }}>{{ $ds->nama }}</option>
                                                @endforeach
                                            </select>
                                            <select name="print_status" class="form-control form-control-sm auto-submit"
                                                style="min-width:130px;flex:1 1 130px">
                                                <option value="">-- Semua Status Print --</option>
                                                <option value="belum" {{ request('print_status') == 'belum' ? 'selected' : '' }}>Belum Print</option>
                                                <option value="sudah" {{ request('print_status') == 'sudah' ? 'selected' : '' }}>Sudah Print</option>
                                            </select>
                                            <select name="scan_out" class="form-control form-control-sm auto-submit"
                                                style="min-width:130px;flex:1 1 130px">
                                                <option value="">-- Semua Scan Out --</option>
                                                <option value="pending" {{ request('scan_out') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="done" {{ request('scan_out') == 'done' ? 'selected' : '' }}>Done</option>
                                            </select>

                                            <input type="text" class="form-control" id="scanOutPenjualan"
                                                placeholder="(SCAN OUT) Arahkan scanner ke nomor resi..."
                                                style="min-width:130px;flex:1 1 130px"
                                                autocomplete="off" inputmode="none">
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
                                                    style="min-width:700px">
                                                    <thead>
                                                        <tr>
                                                            <th class="checkbox-col" style="width:36px">
                                                                <input type="checkbox" id="checkAll">
                                                            </th>
                                                            <th>No</th>
                                                            <th>
                                                                <a href="{{ sortUrl('kode_penjualan') }}" style="color:inherit">
                                                                    Kode Penjualan
                                                                    {!! sortIcon('kode_penjualan') !!}
                                                                </a>
                                                            </th>
                                                            <th>
                                                                <a href="{{ sortUrl('nomor_resi') }}" style="color:inherit">
                                                                    Nomor Resi
                                                                    {!! sortIcon('nomor_resi') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-lg-table-cell">
                                                                <a href="{{ sortUrl('nomor_pesanan') }}" style="color:inherit">
                                                                    No. Pesanan
                                                                    {!! sortIcon('nomor_pesanan') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-md-table-cell">Dropshipper</th>
                                                            <th>
                                                                <a href="{{ sortUrl('tanggal') }}" style="color:inherit">
                                                                    Tanggal
                                                                    {!! sortIcon('tanggal') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-md-table-cell">
                                                                <a href="{{ sortUrl('total_harga') }}" style="color:inherit">
                                                                    Total Harga
                                                                    {!! sortIcon('total_harga') !!}
                                                                </a>
                                                            </th>
                                                            <th>
                                                                <a href="{{ sortUrl('scan_out') }}" style="color:inherit">
                                                                    Scan Out
                                                                    {!! sortIcon('scan_out') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-sm-table-cell">
                                                                <a href="{{ sortUrl('is_draft') }}" style="color:inherit">
                                                                    Draft? {!! sortIcon('is_draft') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-sm-table-cell">
                                                                <a href="{{ sortUrl('is_retur') }}" style="color:inherit">
                                                                    Retur? {!! sortIcon('is_retur') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-sm-table-cell">
                                                                <a href="{{ sortUrl('strukprint_status') }}" style="color:inherit">
                                                                    Print? {!! sortIcon('strukprint_status') !!}
                                                                </a>
                                                            </th>
                                                            <th style="width:90px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableBody">
                                                        @forelse ($penjualan as $pj)
                                                            <tr class="main-row" data-id="{{ $pj->id }}" style="cursor:pointer">
                                                                <td class="checkbox-col"><input type="checkbox" class="row-check"></td>
                                                                <td>{{ $penjualan->firstItem() + $loop->index }}</td>
                                                                <td class="kode-click" style="color:#00499b;text-decoration:underline;white-space:nowrap">
                                                                    <strong>{{ $pj->kode_penjualan }}</strong>
                                                                </td>
                                                                <td style="white-space:nowrap">{{ $pj->nomor_resi ?? '-' }}</td>
                                                                <td class="d-none d-lg-table-cell">{{ $pj->nomor_pesanan ?? '-' }}</td>
                                                                <td class="d-none d-md-table-cell">{{ $pj->dropshipper->nama ?? '-' }}</td>
                                                                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y H:i') }}</td>
                                                                <td class="d-none d-md-table-cell" style="font-weight:bold;white-space:nowrap">Rp {{ number_format($pj->total_harga, 0, ',', '.') }}</td>
                                                                <td>
                                                                    @php
                                                                        $scanBadge = match($pj->scan_out ?? 'nothing') {
                                                                            'pending' => 'badge-warning',
                                                                            'done' => 'badge-success',
                                                                            'failed' => 'badge-danger',
                                                                            default => 'badge-secondary'
                                                                        };
                                                                        $scanLabel = match($pj->scan_out ?? 'nothing') {
                                                                            'pending' => 'Pending',
                                                                            'done' => 'Done',
                                                                            'failed' => 'Failed',
                                                                            default => '-'
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $scanBadge }}">{{ $scanLabel }}</span>
                                                                </td>
                                                                <td class="d-none d-sm-table-cell">
                                                                    @if ($pj->is_draft === 'yes')
                                                                        <span class="badge text-white" style="background:#00499b">Ya</span>
                                                                    @elseif ($pj->is_draft === 'no')
                                                                        <span class="badge badge-danger">Tidak</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="d-none d-sm-table-cell">
                                                                    @if ($pj->is_retur === 'yes')
                                                                        <span class="badge text-white" style="background:#00499b">Ya</span>
                                                                    @elseif ($pj->is_retur === 'no')
                                                                        <span class="badge badge-danger">Tidak</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="d-none d-sm-table-cell">
                                                                    @if ($pj->strukprint_status === 'sudah')
                                                                        <span class="badge badge-success">Sudah</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Belum</span>
                                                                    @endif
                                                                </td>
                                                                <td style="white-space:nowrap">
                                                                    @if ($pj->is_retur === 'no')
                                                                        <a href="/transaksi/penjualan/retur/{{ $pj->id }}" class="btn btn-sm btn-danger">
                                                                            <i class="feather icon-edit"></i> Retur
                                                                        </a>
                                                                    @endif
                                                                    <a href="/transaksi/penjualan/{{ $pj->id }}/struk/download" class="btn btn-sm btn-info">
                                                                        <i class="feather icon-download"></i> File
                                                                    </a>
                                                                    @if(hasPermission('edit', 'penjualan'))
                                                                    <a href="/transaksi/penjualan/edit/{{ $pj->id }}" class="btn btn-sm btn-warning">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>
                                                                    @endif
                                                                    @if(hasPermission('hapus', 'penjualan'))
                                                                    <form id="delete-form-{{ $pj->id }}" action="/transaksi/penjualan/delete/{{ $pj->id }}" method="POST" style="display:inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" onclick="confirmDelete({{ $pj->id }})" class="btn btn-sm btn-danger">
                                                                            <i class="feather icon-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr class="detail-row" id="detail-{{ $pj->id }}" style="display:none;background:#f9f9f9">
                                                                <td colspan="13">
                                                                    <div class="p-2 p-md-3" style="overflow-x:auto">
                                                                        <table class="table table-sm table-bordered mb-0" style="min-width:500px">
                                                                            <thead class="thead-light">
                                                                                <tr>
                                                                                    <th>No Resi</th><th>SKU</th><th>Nama Barang</th>
                                                                                    <th>Stok</th><th>Qty</th><th>Harga</th><th>Subtotal</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @php $totalDetail = 0; @endphp
                                                                                @forelse ($pj->detail as $d)
                                                                                    @php $totalDetail += $d->subtotal; @endphp
                                                                                    <tr>
                                                                                        <td>{{ $d->nomor_resi ?? '-' }}</td>
                                                                                        <td>{{ $d->barang->sku ?? '-' }}</td>
                                                                                        <td>{{ $d->barang->nama_barang ?? '-' }}</td>
                                                                                        <td>{{ $d->barang->stok->jumlah_stok ?? 0 }}</td>
                                                                                        <td>{{ $d->qty }}</td>
                                                                                        <td>Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                                                                        <td>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr><td colspan="7" class="text-center text-muted">Tidak ada detail.</td></tr>
                                                                                @endforelse
                                                                                <tr style="background:#f1f1f1;font-weight:bold">
                                                                                    <td colspan="6" class="text-right">Total Penjualan</td>
                                                                                    <td>Rp {{ number_format($totalDetail, 0, ',', '.') }}</td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="13" class="text-center text-muted py-4">Tidak ada data penjualan.</td>
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
                                                    @if ($penjualan->total() > 0)
                                                        Showing <strong>{{ $penjualan->firstItem() }}</strong>
                                                        to <strong>{{ $penjualan->lastItem() }}</strong>
                                                        of <strong>{{ $penjualan->total() }}</strong> entries
                                                    @else
                                                        No entries found
                                                    @endif
                                                </div>
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0">
                                                        {{-- Previous --}}
                                                        <li class="page-item {{ $penjualan->onFirstPage() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $penjualan->previousPageUrl() }}">
                                                                <i class="feather icon-chevron-left"></i>
                                                            </a>
                                                        </li>

                                                        @php
                                                            $currentPage = $penjualan->currentPage();
                                                            $lastPage = $penjualan->lastPage();
                                                            $start = max(1, $currentPage - 2);
                                                            $end = min($lastPage, $currentPage + 2);
                                                            if ($start <= 3) $end = min($lastPage, 5);
                                                            if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                                                        @endphp

                                                        @if ($lastPage > 7 && $start > 1)
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $penjualan->url(1) }}">1</a>
                                                            </li>
                                                            @if ($start > 2)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                        @endif

                                                        @for ($i = $start; $i <= $end; $i++)
                                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                                <a class="page-link" href="{{ $penjualan->url($i) }}">{{ $i }}</a>
                                                            </li>
                                                        @endfor

                                                        @if ($lastPage > 7 && $end < $lastPage)
                                                            @if ($end < $lastPage - 1)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $penjualan->url($lastPage) }}">{{ $lastPage }}</a>
                                                            </li>
                                                        @endif

                                                        {{-- Next --}}
                                                        <li class="page-item {{ !$penjualan->hasMorePages() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $penjualan->nextPageUrl() }}">
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

        @include('components.footer')
    </div>
@endsection

@section('scripts')
    <script>
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // =====================================================
        // AUTO-SUBMIT FILTER FORM
        // =====================================================
        document.querySelectorAll('.auto-submit').forEach(el => {
            el.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        let searchTimeout;
        document.getElementById('searchTable').addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });

        document.getElementById('entriesSelect').addEventListener('change', function() {
            document.getElementById('perPageInput').value = this.value;
            document.getElementById('filterForm').submit();
        });

        // =====================================================
        // SCAN OUT
        // =====================================================
        function focusScan() {
            const el = document.getElementById('scanOutPenjualan');
            if (el) el.focus();
        }

        const scanInput = document.getElementById('scanOutPenjualan');
        let scanTimeout = null;

        scanInput.addEventListener('input', function() {
            clearTimeout(scanTimeout);
            if (!this.value.trim()) return;
            scanTimeout = setTimeout(() => {
                const val = this.value.trim();
                if (val) {
                    processScan(val);
                    this.value = '';
                }
            }, 300);
        });

        scanInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(scanTimeout);
                const val = this.value.trim();
                if (val) {
                    processScan(val);
                    this.value = '';
                }
                e.preventDefault();
            }
        });

        async function processScan(nomor_resi) {
            try {
                const res = await fetch('/api/penjualan/scan-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ nomor_resi }),
                });

                const json = await res.json();

                if (json.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Scan Out Berhasil',
                        text: json.message,
                    });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const alreadyDone = json.penjualan && json.penjualan.scan_out === 'done';
                    Toast.fire({
                        icon: alreadyDone ? 'warning' : 'error',
                        title: alreadyDone ? 'Sudah Di-scan' : 'Tidak Ditemukan',
                        text: json.message,
                    });
                }
            } catch (e) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
                console.error(e);
            } finally {
                focusScan();
            }
        }

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
        // CHECK ALL
        // =====================================================
        document.getElementById('checkAll').addEventListener('click', function() {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });

        document.getElementById('tableBody').addEventListener('change', function(e) {
            if (e.target.classList.contains('row-check')) updateBulkBar();
        });

        // =====================================================
        // BULK ACTIONS
        // =====================================================
        function getSelectedIds() {
            const checked = document.querySelectorAll('#tableBody .row-check:checked');
            return Array.from(checked).map(cb => {
                return parseInt(cb.closest('tr.main-row').getAttribute('data-id'));
            });
        }

        function updateBulkBar() {
            const ids = getSelectedIds();
            const btn = document.getElementById('btnBulkDownload');
            const btnDel = document.getElementById('btnBulkDelete');

            document.querySelectorAll('#selectedCount, .selectedCountMobile')
                .forEach(el => el.textContent = ids.length);
            document.querySelectorAll('#selectedCountDelete, .selectedCountDeleteMobile')
                .forEach(el => el.textContent = ids.length);

            if (ids.length > 0) {
                btn.classList.remove('d-none');
                btnDel.classList.remove('d-none');
            } else {
                btn.classList.add('d-none');
                btnDel.classList.add('d-none');
            }
        }

        async function bulkDelete() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            const result = await Swal.fire({
                title: 'Hapus ' + ids.length + ' penjualan?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus semua!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            const btn = document.getElementById('btnBulkDelete');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menghapus...';

            try {
                const res = await fetch('/transaksi/penjualan/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ ids }),
                });

                const json = await res.json();

                if (json.success) {
                    Toast.fire({ icon: 'success', title: 'Berhasil', text: json.message });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Toast.fire({ icon: 'error', title: 'Gagal', text: json.message ?? 'Terjadi kesalahan.' });
                }
            } catch (e) {
                Toast.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
                console.error(e);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="feather icon-download"></i> <span class="d-none d-sm-inline">Download Struk (<span id="selectedCount">0</span>)</span><span class="d-inline d-sm-none">DL (<span class="selectedCountMobile">0</span>)</span>';
                updateBulkBar();
            }
        }

        async function bulkDownloadStruk() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            const btn = document.getElementById('btnBulkDownload');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Memproses...';

            try {
                const res = await fetch('/transaksi/penjualan/bulk-struk-download', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ ids }),
                });

                if (!res.ok) {
                    const err = await res.json();
                    Toast.fire({ icon: 'error', title: 'Gagal', text: err.error ?? 'Terjadi kesalahan.' });
                    return;
                }

                const blob = await res.blob();
                const url  = URL.createObjectURL(blob);
                const a    = document.createElement('a');
                a.href     = url;
                a.download = 'struk-bulk-' + new Date().toISOString().slice(0,10) + '.pdf';
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);

                Toast.fire({ icon: 'success', title: 'Berhasil', text: ids.length + ' struk berhasil didownload.' });
                document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
            } catch (e) {
                Toast.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
                console.error(e);
            } finally {
                btn.disabled = false;
                updateBulkBar();
            }
        }

        // =====================================================
        // DELETE CONFIRM
        // =====================================================
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Data akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        // =====================================================
        // SESSION ALERT AUTO-DISMISS
        // =====================================================
        setTimeout(() => {
            const alertCard = document.querySelector('.js-alert-success');
            if (alertCard) {
                alertCard.style.transition = '0.5s';
                alertCard.style.opacity = '0';
                setTimeout(() => alertCard.remove(), 500);
            }
        }, 4000);

        // =====================================================
        // INIT
        // =====================================================
        document.addEventListener('DOMContentLoaded', () => {
            focusScan();
        });
    </script>
@endsection
