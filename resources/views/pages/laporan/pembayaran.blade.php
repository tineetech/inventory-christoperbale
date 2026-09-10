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
            <h4 class="font-weight-bold py-3 mb-0">Laporan Pembayaran</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Pembayaran</li>
                </ol>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.pembayaran') }}" id="pembayaranFilterForm">
                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Dari Tanggal</label>
                                <input type="date" class="form-control" name="dari_tanggal"
                                    value="{{ $filters['dari_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-1">
                                <label class="font-weight-bold">Jam</label>
                                <input type="time" class="form-control" name="dari_jam"
                                    value="{{ $filters['dari_jam'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="sampai_tanggal"
                                    value="{{ $filters['sampai_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-1">
                                <label class="font-weight-bold">Jam</label>
                                <input type="time" class="form-control" name="sampai_jam"
                                    value="{{ $filters['sampai_jam'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="capture" {{ $filters['status'] == 'capture' ? 'selected' : '' }}>Capture</option>
                                    <option value="settlement" {{ $filters['status'] == 'settlement' ? 'selected' : '' }}>Settlement</option>
                                    <option value="paid" {{ $filters['status'] == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="deny" {{ $filters['status'] == 'deny' ? 'selected' : '' }}>Deny</option>
                                    <option value="cancel" {{ $filters['status'] == 'cancel' ? 'selected' : '' }}>Cancel</option>
                                    <option value="expire" {{ $filters['status'] == 'expire' ? 'selected' : '' }}>Expire</option>
                                    <option value="failure" {{ $filters['status'] == 'failure' ? 'selected' : '' }}>Failure</option>
                                    <option value="refund" {{ $filters['status'] == 'refund' ? 'selected' : '' }}>Refund</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="font-weight-bold">Cari</label>
                                <input type="text" class="form-control" name="search"
                                    placeholder="Kode penjualan / Order ID / Transaction ID / Metode..."
                                    value="{{ $filters['search'] }}">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end" style="gap: 10px;">
                            <a href="{{ route('laporan.pembayaran.print', $filters) }}" target="_blank"
                                class="btn btn-success">
                                <i class="feather icon-printer"></i> Print
                            </a>
                            <a href="{{ route('laporan.pembayaran.pdf', $filters) }}" class="btn btn-danger">
                                <i class="feather icon-file-text"></i> PDF
                            </a>
                            <a href="{{ route('laporan.pembayaran.excel', $filters) }}" class="btn btn-warning text-white">
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
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-credit-card mr-2"></i> Data Laporan Pembayaran
                        <span class="badge badge-light ml-1">{{ $pembayaran->total() }} transaksi</span>
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <small class="text-muted">
                            Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                        </small>
                    </div>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0" id="pembayaranTable" style="min-width:1100px">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Penjualan</th>
                                <th>Order ID</th>
                                <th>Metode Pembayaran</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Dibayar</th>
                                <th>Transaction ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pembayaran as $pb)
                                <tr class="main-row" data-id="{{ $pb->id }}" style="cursor:pointer">
                                    <td>{{ $pembayaran->firstItem() + $loop->index }}</td>
                                    <td class="kode-click" style="color:#00499b;white-space:nowrap">
                                        @if ($pb->penjualan)
                                            <strong>{{ $pb->penjualan->kode_penjualan }}</strong>
                                        @elseif ($pb->penjualanDraft)
                                            <strong>{{ $pb->penjualanDraft->kode_penjualan }}</strong>
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
                                    <td style="white-space:nowrap">
                                        {{ \Carbon\Carbon::parse($pb->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="white-space:nowrap">
                                        {{ $pb->paid_at ? \Carbon\Carbon::parse($pb->paid_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td style="white-space:nowrap">{{ $pb->transaction_id ?? '-' }}</td>
                                </tr>
                                <tr class="detail-row" id="detail-{{ $pb->id }}" style="display:none;background:#f9f9f9">
                                    <td colspan="9">
                                        <div class="p-2 p-md-3" style="overflow-x:auto">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    @if ($pb->penjualan)
                                                        <strong>Kode Penjualan:</strong> {{ $pb->penjualan->kode_penjualan }}
                                                        <br>
                                                        <strong>Tanggal:</strong> {{ $pb->penjualan->tanggal }}
                                                        <br>
                                                        <strong>Pembeli:</strong>
                                                        {{ $pb->penjualan->address->recipient_name ?? ($pb->penjualan->user->nama ?? '-') }}
                                                        @if ($pb->penjualan->address && $pb->penjualan->address->phone)
                                                            <span class="text-muted">({{ $pb->penjualan->address->phone }})</span>
                                                        @endif
                                                    @elseif ($pb->penjualanDraft)
                                                        <strong>Kode (Draft):</strong> {{ $pb->penjualanDraft->kode_penjualan }}
                                                        <br>
                                                        <strong>Status Draft:</strong>
                                                        {{ $pb->penjualanDraft->status ?? '-' }}
                                                    @else
                                                        <span class="text-muted">Tidak terhubung dengan penjualan.</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Order ID:</strong> {{ $pb->order_id_midtrans ?? '-' }}
                                                    <br>
                                                    <strong>Transaction ID:</strong> {{ $pb->transaction_id ?? '-' }}
                                                    <br>
                                                    <strong>Dibuat:</strong>
                                                    {{ \Carbon\Carbon::parse($pb->created_at)->format('d/m/Y H:i') }}
                                                    <br>
                                                    <strong>Dibayar:</strong>
                                                    {{ $pb->paid_at ? \Carbon\Carbon::parse($pb->paid_at)->format('d/m/Y H:i') : '-' }}
                                                    @if ($pb->expired_at)
                                                        <br>
                                                        <strong>Expired:</strong>
                                                        {{ \Carbon\Carbon::parse($pb->expired_at)->format('d/m/Y H:i') }}
                                                    @endif
                                                    @if ($pb->alasan_override_pembayaran)
                                                        <br>
                                                        <strong>Alasan Override:</strong>
                                                        {{ $pb->alasan_override_pembayaran }}
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($pb->penjualan && $pb->penjualan->detail->isNotEmpty())
                                                <table class="table table-sm table-bordered mb-0" style="min-width:500px">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>No Resi</th><th>SKU</th><th>Nama Barang</th>
                                                            <th>Qty</th><th>Harga</th><th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $totalDetail = 0; @endphp
                                                        @foreach ($pb->penjualan->detail as $d)
                                                            @php $totalDetail += $d->subtotal; @endphp
                                                            <tr>
                                                                <td>{{ $d->nomor_resi ?? '-' }}</td>
                                                                <td>{{ $d->barang->sku ?? '-' }}</td>
                                                                <td>{{ $d->barang->nama_barang ?? '-' }}</td>
                                                                <td>{{ $d->qty }}</td>
                                                                <td>Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                                                <td>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr style="background:#f1f1f1;font-weight:bold">
                                                            <td colspan="5" class="text-right">Total Penjualan</td>
                                                            <td>Rp {{ number_format($totalDetail, 0, ',', '.') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Tidak ada data pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top"
                    style="gap:8px">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 text-muted small">Show</span>
                        <select class="form-control form-control-sm" name="per_page"
                            form="pembayaranFilterForm" style="width:72px"
                            onchange="document.getElementById('pembayaranFilterForm').submit()">
                            <option value="10" {{ $filters['per_page'] == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $filters['per_page'] == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $filters['per_page'] == 100 ? 'selected' : '' }}>100</option>
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
                            @php
                                $currentPage = $pembayaran->currentPage();
                                $lastPage = $pembayaran->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                                if ($start <= 3) $end = min($lastPage, 5);
                                if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                            @endphp

                            <li class="page-item {{ $pembayaran->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $pembayaran->appends(['per_page' => request('per_page')])->url(1) }}">
                                    <i class="feather icon-chevrons-left"></i>
                                </a>
                            </li>
                            <li class="page-item {{ $pembayaran->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $pembayaran->previousPageUrl() }}">
                                    <i class="feather icon-chevron-left"></i>
                                </a>
                            </li>

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

                            <li class="page-item {{ !$pembayaran->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $pembayaran->nextPageUrl() }}">
                                    <i class="feather icon-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item {{ !$pembayaran->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $pembayaran->appends(['per_page' => request('per_page')])->url($lastPage) }}">
                                    <i class="feather icon-chevrons-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // ── Expand detail row ─────────────────────────────────
        document.getElementById('pembayaranTable').addEventListener('click', function(e) {
            const row = e.target.closest('.main-row');
            if (!row) return;
            if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;

            const id = row.getAttribute('data-id');
            const detailRow = document.getElementById('detail-' + id);
            if (!detailRow) return;

            detailRow.style.display = detailRow.style.display === 'table-row' ? 'none' : 'table-row';
        });
    </script>
@endsection