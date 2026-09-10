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
            <h4 class="font-weight-bold py-3 mb-0">Riwayat Pembayaran</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item active">Riwayat Pembayaran</li>
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

                            {{-- ===== CARD 1: FILTER ===== --}}
                            <div class="card mb-4">
                                <div style="border:none !important" class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-filter mr-2"></i> Filter Riwayat Pembayaran
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="GET" id="filterForm">
                                        <div class="form-row">
                                            <div class="form-group col-md-2">
                                                <label class="font-weight-bold">Dari Tanggal</label>
                                                <input type="date" name="date_from" class="form-control"
                                                    value="{{ request('date_from', today()->format('Y-m-d')) }}">
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label class="font-weight-bold">Sampai Tanggal</label>
                                                <input type="date" name="date_to" class="form-control"
                                                    value="{{ request('date_to', today()->format('Y-m-d')) }}">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold">Cari</label>
                                                <input type="text" name="search" id="searchTable" class="form-control"
                                                    placeholder="Cari kode penjualan, order id, transaction id, metode..."
                                                    value="{{ request('search') }}">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold">Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">-- Semua Status --</option>
                                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="capture" {{ request('status') == 'capture' ? 'selected' : '' }}>Capture</option>
                                                    <option value="settlement" {{ request('status') == 'settlement' ? 'selected' : '' }}>Settlement</option>
                                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="deny" {{ request('status') == 'deny' ? 'selected' : '' }}>Deny</option>
                                                    <option value="cancel" {{ request('status') == 'cancel' ? 'selected' : '' }}>Cancel</option>
                                                    <option value="expire" {{ request('status') == 'expire' ? 'selected' : '' }}>Expire</option>
                                                    <option value="failure" {{ request('status') == 'failure' ? 'selected' : '' }}>Failure</option>
                                                    <option value="refund" {{ request('status') == 'refund' ? 'selected' : '' }}>Refund</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap justify-content-end" style="gap:10px">
                                            <button type="submit" class="btn btn-info">
                                                <i class="feather icon-refresh-cw"></i> Proses
                                            </button>
                                            @if (request('search') || request('status') || request('date_from') != today()->format('Y-m-d') || request('date_to') != today()->format('Y-m-d'))
                                                <a href="{{ route('pembayaran.index', ['per_page' => request('per_page', 10)]) }}"
                                                    class="btn btn-outline-secondary">
                                                    <i class="feather icon-rotate-ccw"></i> Reset
                                                </a>
                                            @endif
                                        </div>

                                        <input type="hidden" name="sort_col" value="{{ request('sort_col') }}">
                                        <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                                        <input type="hidden" name="per_page" id="perPageInput" value="{{ request('per_page', 10) }}">
                                    </form>
                                </div>
                            </div>

                            {{-- ===== CARD 2: DATA ===== --}}
                            <div class="card mb-4">
                                <div style="border:none !important" class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-credit-card mr-2"></i> Data Riwayat Pembayaran
                                        <span class="badge badge-light ml-1">{{ $pembayaran->total() }} transaksi</span>
                                    </h6>
                                </div>

                                <div class="nav-tabs-top">
                                    <div class="tab-content" style="width:100%">
                                        <div class="tab-pane fade show active pb-4 px-2 px-md-4">
                                            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                                                <table class="table table-modern table-hover" id="table"
                                                    style="min-width:700px">
                                                    <thead>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Kode Penjualan</th>
                                                            <th>
                                                                <a href="{{ sortUrl('order_id_midtrans') }}" style="color:inherit">
                                                                    Order ID
                                                                    {!! sortIcon('order_id_midtrans') !!}
                                                                </a>
                                                            </th>
                                                            <th>
                                                                <a href="{{ sortUrl('payment_method') }}" style="color:inherit">
                                                                    Metode Pembayaran
                                                                    {!! sortIcon('payment_method') !!}
                                                                </a>
                                                            </th>
                                                            <th>
                                                                <a href="{{ sortUrl('amount') }}" style="color:inherit">
                                                                    Jumlah
                                                                    {!! sortIcon('amount') !!}
                                                                </a>
                                                            </th>
                                                            <th>
                                                                <a href="{{ sortUrl('status') }}" style="color:inherit">
                                                                    Status
                                                                    {!! sortIcon('status') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-md-table-cell">
                                                                <a href="{{ sortUrl('created_at') }}" style="color:inherit">
                                                                    Dibuat
                                                                    {!! sortIcon('created_at') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-md-table-cell">Dibayar</th>
                                                            <th class="d-none d-lg-table-cell">Transaction ID</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($pembayaran as $pb)
                                                            <tr>
                                                                <td>{{ $pembayaran->firstItem() + $loop->index }}</td>
                                                                <td style="white-space:nowrap">
                                                                    @if ($pb->penjualan)
                                                                        <a href="{{ route('penjualan.edit', $pb->penjualan_id) }}" style="color:#00499b">
                                                                            {{ $pb->penjualan->kode_penjualan }}
                                                                        </a>
                                                                    @elseif ($pb->penjualanDraft)
                                                                        <span class="text-muted">{{ $pb->penjualanDraft->kode_penjualan }}</span>
                                                                        <span class="badge badge-light">Draft</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td style="white-space:nowrap">{{ $pb->order_id_midtrans ?? '-' }}</td>
                                                                <td style="white-space:nowrap">
                                                                    @php $pm = strtoupper($pb->payment_method ?? $pb->payment_type ?? '-'); @endphp
                                                                    {{ $pm }}
                                                                </td>
                                                                <td style="font-weight:bold;white-space:nowrap">Rp {{ number_format($pb->amount, 0, ',', '.') }}</td>
                                                                <td>
                                                                    @php
                                                                        $st = strtolower($pb->status ?? '');
                                                                        $stBadge = match($st) {
                                                                            'settlement', 'capture', 'captured', 'paid', 'success' => 'badge-success',
                                                                            'pending', 'challenge' => 'badge-warning',
                                                                            'deny', 'cancel', 'expire', 'failure', 'refund' => 'badge-danger',
                                                                            default => 'badge-secondary'
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $stBadge }}">{{ ucfirst($pb->status ?? '-') }}</span>
                                                                </td>
                                                                <td class="d-none d-md-table-cell" style="white-space:nowrap">
                                                                    {{ \Carbon\Carbon::parse($pb->created_at)->format('d/m/Y H:i') }}
                                                                </td>
                                                                <td class="d-none d-md-table-cell" style="white-space:nowrap">
                                                                    {{ $pb->paid_at ? \Carbon\Carbon::parse($pb->paid_at)->format('d/m/Y H:i') : '-' }}
                                                                </td>
                                                                <td class="d-none d-lg-table-cell" style="white-space:nowrap">{{ $pb->transaction_id ?? '-' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="9" class="text-center text-muted py-4">Tidak ada data pembayaran.</td>
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
                                                    @if ($pembayaran->total() > 0)
                                                        Showing <strong>{{ $pembayaran->firstItem() }}</strong>
                                                        to <strong>{{ $pembayaran->lastItem() }}</strong>
                                                        of <strong>{{ $pembayaran->total() }}</strong> entries
                                                    @else
                                                        No entries found
                                                    @endif
                                                </div>
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0">
                                                        {{-- Previous --}}
                                                        <li class="page-item {{ $pembayaran->onFirstPage() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $pembayaran->previousPageUrl() }}">
                                                                <i class="feather icon-chevron-left"></i>
                                                            </a>
                                                        </li>

                                                        @php
                                                            $currentPage = $pembayaran->currentPage();
                                                            $lastPage = $pembayaran->lastPage();
                                                            $start = max(1, $currentPage - 2);
                                                            $end = min($lastPage, $currentPage + 2);
                                                            if ($start <= 3) $end = min($lastPage, 5);
                                                            if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                                                        @endphp

                                                        @if ($lastPage > 7 && $start > 1)
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $pembayaran->url(1) }}">1</a>
                                                            </li>
                                                            @if ($start > 2)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                        @endif

                                                        @for ($i = $start; $i <= $end; $i++)
                                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                                <a class="page-link" href="{{ $pembayaran->url($i) }}">{{ $i }}</a>
                                                            </li>
                                                        @endfor

                                                        @if ($lastPage > 7 && $end < $lastPage)
                                                            @if ($end < $lastPage - 1)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $pembayaran->url($lastPage) }}">{{ $lastPage }}</a>
                                                            </li>
                                                        @endif

                                                        {{-- Next --}}
                                                        <li class="page-item {{ !$pembayaran->hasMorePages() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $pembayaran->nextPageUrl() }}">
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
        // Cari via tombol/Enter (form submit), bukan otomatis saat mengetik
        document.getElementById('searchTable').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });

        document.getElementById('entriesSelect').addEventListener('change', function() {
            document.getElementById('perPageInput').value = this.value;
            document.getElementById('filterForm').submit();
        });

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
    </script>
@endsection