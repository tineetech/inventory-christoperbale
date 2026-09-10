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
            <h4 class="font-weight-bold py-3 mb-0">Laporan Penjualan Web</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Penjualan Web</li>
                </ol>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.penjualan-web') }}" id="penjualanWebFilterForm">
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
                                <label class="font-weight-bold">Status Penjualan Web</label>
                                <select name="status" class="form-control">
                                    <option value="">-- Semua Status --</option>
                                    <option value="proses" {{ $filters['status'] == 'proses' ? 'selected' : '' }}>Proses</option>
                                    <option value="packing" {{ $filters['status'] == 'packing' ? 'selected' : '' }}>Packing</option>
                                    <option value="dikirim" {{ $filters['status'] == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                    <option value="selesai" {{ $filters['status'] == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Scan Out</label>
                                <select name="scan_out" class="form-control">
                                    <option value="">-- Semua Scan Out --</option>
                                    <option value="pending" {{ $filters['scan_out'] == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="done" {{ $filters['scan_out'] == 'done' ? 'selected' : '' }}>Done</option>
                                    <option value="failed" {{ $filters['scan_out'] == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                            <div class="form-group col-md-8">
                                <label class="font-weight-bold">Cari</label>
                                <input type="text" class="form-control" name="search"
                                    placeholder="Kode penjualan / Nomor resi / Pesanan / Pembeli / Dropshipper..."
                                    value="{{ $filters['search'] }}">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end" style="gap: 10px;">
                            <a href="{{ route('laporan.penjualan-web.print', $filters) }}" target="_blank"
                                class="btn btn-success">
                                <i class="feather icon-printer"></i> Print
                            </a>
                            <a href="{{ route('laporan.penjualan-web.pdf', $filters) }}" class="btn btn-danger">
                                <i class="feather icon-file-text"></i> PDF
                            </a>
                            <a href="{{ route('laporan.penjualan-web.excel', $filters) }}" class="btn btn-warning text-white">
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
                        <i class="feather icon-globe mr-2"></i> Data Laporan Penjualan Web
                        <span class="badge badge-light ml-1">{{ $penjualan->total() }} transaksi</span>
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <small class="text-muted">
                            Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                        </small>
                    </div>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0" id="penjualanWebTable" style="min-width:1100px">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Penjualan</th>
                                <th>Nomor Resi</th>
                                <th>Pembeli</th>
                                <th>Alamat Pengiriman</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Total Harga</th>
                                <th>Scan Out</th>
                                <th>Retur?</th>
                                <th style="width:90px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse ($penjualan as $pj)
                                <tr class="main-row" data-id="{{ $pj->id }}" style="cursor:pointer">
                                    <td>{{ $penjualan->firstItem() + $loop->index }}</td>
                                    <td class="kode-click" style="color:#00499b;text-decoration:underline;white-space:nowrap">
                                        <strong>{{ $pj->kode_penjualan }}</strong>
                                    </td>
                                    <td style="white-space:nowrap">{{ $pj->nomor_resi ?? '-' }}</td>
                                    <td style="white-space:nowrap">
                                        {{ $pj->address->recipient_name ?? ($pj->user->nama ?? '-') }}
                                        <br>
                                        <small class="text-muted">{{ $pj->address->phone ?? '' }}</small>
                                    </td>
                                    <td style="max-width:260px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word">
                                        @php
                                            $alamatPenuh = '';
                                            if ($pj->address) {
                                                $alamatPenuh = $pj->address->address ?? '';
                                                if ($pj->address->district) $alamatPenuh .= ', ' . $pj->address->district;
                                                if ($pj->address->city) $alamatPenuh .= ', ' . $pj->address->city;
                                                if ($pj->address->province) $alamatPenuh .= ', ' . $pj->address->province;
                                                if ($pj->address->postal_code) $alamatPenuh .= ' - ' . $pj->address->postal_code;
                                                $alamatPenuh = \Illuminate\Support\Str::words($alamatPenuh, 10, '...');
                                            }
                                        @endphp
                                        @if ($pj->address)
                                            <small>{{ $alamatPenuh }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge = match($pj->status ?? '') {
                                                'proses' => 'badge-warning',
                                                'packing' => 'badge-info',
                                                'dikirim' => 'badge-primary',
                                                'selesai' => 'badge-success',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ ucfirst($pj->status ?? '-') }}</span>
                                    </td>
                                    <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y H:i') }}</td>
                                    <td style="font-weight:bold;white-space:nowrap">Rp {{ number_format($pj->total_harga, 0, ',', '.') }}</td>
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
                                    <td>
                                        @if ($pj->is_retur === 'yes')
                                            <span class="badge text-white" style="background:#00499b">Ya</span>
                                        @elseif ($pj->is_retur === 'no')
                                            <span class="badge badge-danger">Tidak</span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">
                                        @if ($pj->is_retur === 'no')
                                            <a href="/transaksi/penjualan/retur/{{ $pj->id }}?back={{ urlencode(request()->getQueryString()) }}" class="btn btn-sm btn-danger">
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
                                    <td colspan="11">
                                        <div class="p-2 p-md-3" style="overflow-x:auto">
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <strong>Pembeli:</strong> {{ $pj->address->recipient_name ?? ($pj->user->nama ?? '-') }}
                                                    @if ($pj->address && $pj->address->phone)
                                                        <span class="text-muted">({{ $pj->address->phone }})</span>
                                                    @endif
                                                    <br>
                                                    <strong>Alamat:</strong>
                                                    <span style="white-space:normal;word-wrap:break-word;overflow-wrap:break-word">
                                                        {{ $pj->address->address ?? '-' }}
                                                        @if ($pj->address)
                                                            @if ($pj->address->district) , {{ $pj->address->district }} @endif
                                                            @if ($pj->address->city) , {{ $pj->address->city }} @endif
                                                            @if ($pj->address->province) , {{ $pj->address->province }} @endif
                                                            @if ($pj->address->postal_code) - {{ $pj->address->postal_code }} @endif
                                                            @if ($pj->address->catatan)
                                                                <br><span class="text-muted">Catatan: {{ $pj->address->catatan }}</span>
                                                            @endif
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Pengiriman:</strong>
                                                    @if ($pj->shipment)
                                                        {{ strtoupper($pj->shipment->courier ?? '') }}
                                                        {{ $pj->shipment->service ?? '' }}
                                                        @if ($pj->shipment->tracking_number)
                                                            <span class="text-muted">({{ $pj->shipment->tracking_number }})</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                    <br>
                                                    <strong>Pembayaran:</strong>
                                                    @if ($pj->pembayaran)
                                                        {{ strtoupper($pj->pembayaran->payment_method ?? $pj->pembayaran->payment_type ?? '-') }}
                                                        <span class="badge {{ $pj->pembayaran->status == 'settlement' || $pj->pembayaran->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                                                            {{ ucfirst($pj->pembayaran->status ?? '-') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </div>
                                            </div>
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
                                    <td colspan="11" class="text-center text-muted py-4">Tidak ada data penjualan web.</td>
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
                            form="penjualanWebFilterForm" style="width:72px"
                            onchange="document.getElementById('penjualanWebFilterForm').submit()">
                            <option value="10" {{ $filters['per_page'] == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $filters['per_page'] == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $filters['per_page'] == 100 ? 'selected' : '' }}>100</option>
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
                            @php
                                $currentPage = $penjualan->currentPage();
                                $lastPage = $penjualan->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                                if ($start <= 3) $end = min($lastPage, 5);
                                if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                            @endphp

                            <li class="page-item {{ $penjualan->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->appends(['per_page' => request('per_page')])->url(1) }}">
                                    <i class="feather icon-chevrons-left"></i>
                                </a>
                            </li>
                            <li class="page-item {{ $penjualan->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->previousPageUrl() }}">
                                    <i class="feather icon-chevron-left"></i>
                                </a>
                            </li>

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

                            <li class="page-item {{ !$penjualan->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->nextPageUrl() }}">
                                    <i class="feather icon-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item {{ !$penjualan->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->appends(['per_page' => request('per_page')])->url($lastPage) }}">
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
        document.getElementById('penjualanWebTable').addEventListener('click', function(e) {
            const row = e.target.closest('.main-row');
            if (!row) return;
            if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;

            const id = row.getAttribute('data-id');
            const detailRow = document.getElementById('detail-' + id);
            if (!detailRow) return;

            detailRow.style.display = detailRow.style.display === 'table-row' ? 'none' : 'table-row';
        });

        // ── DELETE CONFIRM ───────────────────────────────────
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
    </script>
@endsection