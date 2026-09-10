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
            <h4 class="font-weight-bold py-3 mb-0">Penjualan Web</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item active">Penjualan Web</li>
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
                                        <i class="feather icon-filter mr-2"></i> Filter Penjualan Web
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
                                            <div class="form-group col-md-1">
                                                <label class="font-weight-bold">Jam</label>
                                                <input type="time" name="time_from" class="form-control"
                                                    value="{{ request('time_from', '06:00') }}">
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label class="font-weight-bold">Sampai Tanggal</label>
                                                <input type="date" name="date_to" class="form-control"
                                                    value="{{ request('date_to', today()->format('Y-m-d')) }}">
                                            </div>
                                            <div class="form-group col-md-1">
                                                <label class="font-weight-bold">Jam</label>
                                                <input type="time" name="time_to" class="form-control"
                                                    value="{{ request('time_to') }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label class="font-weight-bold">Cari</label>
                                                <input type="text" name="search" id="searchTable" class="form-control"
                                                    placeholder="Cari kode penjualan, nomor resi, pembeli, dropshipper..."
                                                    value="{{ request('search') }}">
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold">Status Penjualan Web</label>
                                                <select name="status" class="form-control">
                                                    <option value="">-- Semua Status --</option>
                                                    <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Proses</option>
                                                    <option value="packing" {{ request('status') == 'packing' ? 'selected' : '' }}>Packing</option>
                                                    <option value="dikirim" {{ request('status') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label class="font-weight-bold">Scan Out</label>
                                                <select name="scan_out" class="form-control">
                                                    <option value="">-- Semua Scan Out --</option>
                                                    <option value="pending" {{ request('scan_out') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="done" {{ request('scan_out') == 'done' ? 'selected' : '' }}>Done</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap justify-content-end" style="gap:10px">
                                            <button type="submit" class="btn btn-info">
                                                <i class="feather icon-refresh-cw"></i> Proses
                                            </button>
                                            @if (request('search') || request('status') || request('scan_out') || request('date_from') != today()->format('Y-m-d') || request('date_to') != today()->format('Y-m-d') || request('time_from') || request('time_to'))
                                                <a href="{{ route('penjualan.web', ['per_page' => request('per_page', 10)]) }}"
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

                            {{-- ===== CARD DRAFT PESANAN WEB (TERPISAH) ===== --}}
                            <style>
                                button[data-toggle="collapse"] .toggle-icon { transition: transform .25s ease; display:inline-block; }
                                button[data-toggle="collapse"][aria-expanded="true"] .toggle-icon { transform: rotate(180deg); }
                            </style>
                            <div class="card mb-4">
                                <div style="border:none !important" class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-clock mr-2"></i> Draft Pesanan Web
                                        <span class="badge badge-light ml-1">{{ $drafts->count() }} draft</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-toggle="collapse" data-target="#collapseDraft"
                                        aria-expanded="false" aria-controls="collapseDraft"
                                        title="Buka/Tutup section Draft Pesanan Web">
                                        <i class="feather icon-chevron-down toggle-icon"></i>
                                    </button>
                                </div>

                                <div class="collapse" id="collapseDraft">
                                <div class="nav-tabs-top">
                                    <div class="tab-content" style="width:100%">
                                        <div class="tab-pane fade show active pb-4 px-2 px-md-4">
                                            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                                                <table class="table table-modern table-hover" id="tableDraft"
                                                    style="min-width:700px">
                                                    <thead>
                                                        <tr>
                                                            <th style="width:50px">No</th>
                                                            <th>Kode Penjualan</th>
                                                            <th>Pembeli</th>
                                                            <th class="d-none d-md-table-cell">Alamat Pengiriman</th>
                                                            <th>Status Pembayaran</th>
                                                            <th class="d-none d-md-table-cell">Tanggal</th>
                                                            <th>Total Harga</th>
                                                            <th style="width:220px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($drafts as $draft)
                                                            @php
                                                                $bayarStatus = $draft->pembayaran->status ?? null;
                                                            @endphp
                                                            <tr style="background:#fffdf3">
                                                                <td>{{ $loop->index + 1 }}</td>
                                                                <td style="white-space:nowrap"><strong>{{ $draft->kode_penjualan }}</strong></td>
                                                                <td style="white-space:nowrap">
                                                                    {{ $draft->address->recipient_name ?? ($draft->creator->nama ?? '-') }}
                                                                    <br>
                                                                    <small class="text-muted">{{ $draft->address->phone ?? '' }}</small>
                                                                </td>
                                                                <td class="d-none d-md-table-cell" style="max-width:260px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word">
                                                                    @if ($draft->address)
                                                                        <small>{{ \Illuminate\Support\Str::words(($draft->address->address ?? '') . ($draft->address->city ? ', ' . $draft->address->city : '') . ($draft->address->province ? ', ' . $draft->address->province : ''), 10, '...') }}</small>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($bayarStatus === 'paid_confirmation')
                                                                        <span class="badge badge-warning">Menunggu Konfirmasi</span>
                                                                    @elseif ($bayarStatus === 'paid')
                                                                        <span class="badge badge-success">Paid</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">{{ ucfirst($bayarStatus ?? '-') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="d-none d-md-table-cell" style="white-space:nowrap">{{ \Carbon\Carbon::parse($draft->tanggal)->format('d/m/Y H:i') }}</td>
                                                                <td style="font-weight:bold;white-space:nowrap">Rp {{ number_format($draft->total_harga, 0, ',', '.') }}</td>
                                                                <td style="white-space:nowrap">
                                                                    @if ($bayarStatus === 'paid_confirmation' && hasPermission('edit', 'penjualan'))
                                                                        <button type="button" class="btn btn-sm btn-success" onclick="confirmDraftPayment({{ $draft->id }})"
                                                                            title="Konfirmasi Pembayaran">
                                                                            <i class="feather icon-check-circle"></i> Konfirmasi Pembayaran
                                                                        </button>
                                                                    @else
                                                                        <span class="text-muted small">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada draft pesanan web.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            {{-- ===== CARD 2: DATA ===== --}}
                            <div class="card mb-4">
                                <div style="border:none !important" class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-globe mr-2"></i> Data Penjualan Web
                                        <span class="badge badge-light ml-1">{{ $penjualan->total() }} transaksi</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-toggle="collapse" data-target="#collapseData"
                                        aria-expanded="true" aria-controls="collapseData"
                                        title="Buka/Tutup section Data Penjualan Web">
                                        <i class="feather icon-chevron-down toggle-icon"></i>
                                    </button>
                                </div>

                                <div class="collapse show" id="collapseData">
                                <div class="nav-tabs-top">
                                    <div class="tab-content" style="width:100%">
                                        <div class="tab-pane fade show active pb-4 px-2 px-md-4">
                                            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch">
                                                <table class="table table-modern table-hover" id="table"
                                                    style="min-width:700px">
                                                    <thead>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>
                                                                <a href="{{ sortUrl('kode_penjualan') }}" style="color:inherit">
                                                                    Kode Penjualan
                                                                    {!! sortIcon('kode_penjualan') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-lg-table-cell">
                                                                <a href="{{ sortUrl('nomor_resi') }}" style="color:inherit">
                                                                    Nomor Resi
                                                                    {!! sortIcon('nomor_resi') !!}
                                                                </a>
                                                            </th>
                                                            <th>Pembeli</th>
                                                            <th class="d-none d-md-table-cell">Alamat Pengiriman</th>
                                                            <th>
                                                                <a href="{{ sortUrl('status') }}" style="color:inherit">
                                                                    Status
                                                                    {!! sortIcon('status') !!}
                                                                </a>
                                                            </th>
                                                            <th class="d-none d-md-table-cell">
                                                                <a href="{{ sortUrl('tanggal') }}" style="color:inherit">
                                                                    Tanggal
                                                                    {!! sortIcon('tanggal') !!}
                                                                </a>
                                                            </th>
                                                            <th>
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
                                                                <a href="{{ sortUrl('is_retur') }}" style="color:inherit">
                                                                    Retur? {!! sortIcon('is_retur') !!}
                                                                </a>
                                                            </th>
                                                            <th style="width:200px">Action</th>
                                                        </tr>
                                                    </thead>
<tbody id="tableBody">
                                                        @forelse ($penjualan as $pj)
                                                            <tr class="main-row" data-id="{{ $pj->id }}" style="cursor:pointer">
                                                                <td>{{ $penjualan->firstItem() + $loop->index }}</td>
                                                                <td class="kode-click" style="color:#00499b;text-decoration:underline;white-space:nowrap">
                                                                    <strong>{{ $pj->kode_penjualan }}</strong>
                                                                </td>
                                                                <td class="d-none d-lg-table-cell" style="white-space:nowrap">{{ $pj->nomor_resi ?? '-' }}</td>
                                                                <td style="white-space:nowrap">
                                                                    {{ $pj->address->recipient_name ?? ($pj->user->nama ?? '-') }}
                                                                    <br>
                                                                    <small class="text-muted">{{ $pj->address->phone ?? '' }}</small>
                                                                </td>
                                                                <td class="d-none d-md-table-cell" style="max-width:260px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word">
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
                                                                <td class="d-none d-md-table-cell" style="white-space:nowrap">{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y H:i') }}</td>
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
                                                                <td class="d-none d-sm-table-cell">
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
                                                                    @if ($pj->dropshipper_id)
                                                                        <a href="/transaksi/penjualan/{{ $pj->id }}/struk/download" class="btn btn-sm btn-info" title="Download File">
                                                                            <i class="feather icon-download"></i> File
                                                                        </a>
                                                                    @else
                                                                        <span class="btn btn-sm btn-info disabled" style="opacity:.5;pointer-events:none" aria-disabled="true"
                                                                            title="Tambahkan dropshipper terlebih dahulu untuk download file">
                                                                            <i class="feather icon-download"></i> File
                                                                        </span>
                                                                    @endif
                                                                    @if (hasPermission('edit', 'penjualan'))
                                                                        <button type="button"
                                                                            class="btn btn-sm {{ $pj->dropshipper_id ? 'btn-success' : 'btn-warning' }}"
                                                                            data-toggle="modal" data-target="#dropshipperModal"
                                                                            onclick="openDropshipperModal({{ $pj->id }}, {{ $pj->dropshipper_id ?? 'null' }})"
                                                                            title="Tambahkan / Ganti Dropshipper">
                                                                            <i class="feather icon-user-plus"></i> <span class="d-none d-md-inline">{{ $pj->dropshipper_id ? 'Ganti' : 'Tambah' }} DS</span>
                                                                        </button>
                                                                    @endif
                                                                    @if(hasPermission('edit', 'penjualan'))
                                                                    <a href="/transaksi/penjualan/edit/{{ $pj->id }}?from=web" class="btn btn-sm btn-warning" title="Edit">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>
                                                                    @endif
                                                                    @if(hasPermission('hapus', 'penjualan'))
                                                                    <form id="delete-form-{{ $pj->id }}" action="/transaksi/penjualan/delete/{{ $pj->id }}" method="POST" style="display:inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" onclick="confirmDelete({{ $pj->id }})" class="btn btn-sm btn-danger" title="Hapus">
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
                                                                        <div class="row mb-2">
                                                                            <div class="col-md-12">
                                                                                <div style="background:#eef6ff;border:1px solid #cfe2ff;border-radius:8px;padding:10px 14px;">
                                                                                    <strong><i class="feather icon-user mr-1"></i> Dropshipper:</strong>
                                                                                    @if ($pj->dropshipper)
                                                                                        <span style="font-weight:600">{{ $pj->dropshipper->nama }}</span>
                                                                                        @if ($pj->dropshipper->no_telp)
                                                                                            <span class="text-muted">({{ $pj->dropshipper->no_telp }})</span>
                                                                                        @endif
                                                                                        @if ($pj->dropshipper->alamat)
                                                                                            <br><small class="text-muted"><i class="feather icon-map-pin" style="width:12px;height:12px"></i> {{ $pj->dropshipper->alamat }}</small>
                                                                                        @endif
                                                                                        @if ($pj->dropshipper->keterangan)
                                                                                            <br><small class="text-muted">Ket: {{ $pj->dropshipper->keterangan }}</small>
                                                                                        @endif
                                                                                    @else
                                                                                        <span class="badge badge-warning ml-1">Belum diatur</span>
                                                                                        <small class="text-muted ml-1">— klik "Tambah DS" untuk menambahkan</small>
                                                                                    @endif
                                                                                </div>
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
        </div>

        {{-- MODAL TAMBAH/GANTI DROPSHIPPER --}}
        <div class="modal fade" id="dropshipperModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather icon-user-plus mr-1"></i> Tambahkan Dropshipper</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="dropshipperPenjualanId" value="">
                        <div class="form-group">
                            <label class="form-label">Pilih Dropshipper</label>
                            <select id="dropshipperSelect" class="form-control">
                                <option value="">-- Pilih Dropshipper --</option>
                                @foreach ($dropshippers as $ds)
                                    <option value="{{ $ds->id }}">{{ $ds->nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Dropshipper yang dipilih akan disimpan ke penjualan ini.</small>
                        </div>
                        <hr class="my-3">
                        <div class="form-group mb-0">
                            <label class="form-label">File Resi <span class="text-muted small">(opsional — PDF atau gambar)</span></label>
                            <div class="custom-file">
                                <input type="file" name="file_resi" id="resiFileInput"
                                    class="custom-file-input" accept="image/*,.pdf">
                                <label class="custom-file-label" for="resiFileInput">Pilih file resi... (jpg, png, webp, pdf)</label>
                            </div>
                            <small class="form-text text-muted">Jika PDF, otomatis dikonversi ke gambar. Nomor resi &amp; nomor pesanan akan discan otomatis dari file.</small>
                            <div id="resiPreviewWrap" style="display:none;margin-top:10px;text-align:center;">
                                <img id="resiPreviewImg" src="" alt="Preview Resi"
                                    style="max-width:100%;max-height:220px;border:1px solid #e0e0e0;border-radius:8px;">
                                <div><button type="button" class="btn btn-sm btn-link text-danger" id="btnRemoveResi"><i class="feather icon-trash-2"></i> Hapus file</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="feather icon-x"></i> Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSaveDropshipper"><i class="feather icon-save"></i> Simpan</button>
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
        // KONFIRMASI PEMBAYARAN DRAFT WEB
        // =====================================================
        async function confirmDraftPayment(id) {
            const result = await Swal.fire({
                title: 'Konfirmasi Pembayaran?',
                text: 'Status pembayaran menjadi PAID dan data akan dipindahkan ke penjualan dengan status packing.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, konfirmasi!',
                cancelButtonText: 'Batal'
            });
            if (!result.isConfirmed) return;

            try {
                const res = await fetch('/transaksi/penjualan/web/draft/' + id + '/confirm-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (err) {
                Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error');
            }
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
        // MODAL TAMBAH / GANTI DROPSHIPPER
        // =====================================================
        let activePenjualanId = null;
        let modalResiFile = null; // File object (hasil konversi jika PDF)

        $('#dropshipperModal').on('shown.bs.modal', function () {
            if (!$.fn.select2) return;
            if (!$(this).find('#dropshipperSelect').data('select2')) {
                $('#dropshipperSelect').select2({
                    dropdownParent: $('#dropshipperModal'),
                    placeholder: '-- Pilih Dropshipper --',
                    width: '100%'
                });
            }
            $('#dropshipperSelect').val('').trigger('change');
        });

        function openDropshipperModal(id, currentId) {
            activePenjualanId = id;
            document.getElementById('dropshipperPenjualanId').value = id;
            resetResiInput();
            const sel = document.getElementById('dropshipperSelect');
            if (window.jQuery && $(sel).data('select2')) {
                $(sel).val(currentId || '').trigger('change');
            } else {
                sel.value = currentId || '';
            }
        }

        // =====================================================
        // FILE RESI: PREVIEW + KONVERSI PDF -> JPEG (pdf.js)
        // =====================================================
        function getPdfjs() {
            if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
                script.onload = () => resolve(window.pdfjsLib);
                script.onerror = () => reject(new Error('Gagal memuat library pdf.js. Cek koneksi internet.'));
                document.head.appendChild(script);
            });
        }

        function convertPdfToImageBuffer(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error('Gagal membaca file PDF.'));
                reader.readAsArrayBuffer(file);
            });
        }

        function resizePdfToJpeg(file) {
            return getPdfjs().then(pdfjsLib => {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
                return convertPdfToImageBuffer(file)
                    .then(buffer => pdfjsLib.getDocument({ data: buffer }).promise)
                    .then(pdf => pdf.getPage(1))
                    .then(page => {
                        const baseViewport = page.getViewport({ scale: 1 });
                        const targetWidth = 1200;
                        const scale = Math.max(1, targetWidth / baseViewport.width);
                        const viewport = page.getViewport({ scale: scale });
                        const canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        const ctx = canvas.getContext('2d');
                        return page.render({ canvasContext: ctx, viewport }).promise.then(() => canvas.toDataURL('image/jpeg', 0.9));
                    });
            });
        }

        function dataUrlToFile(dataUrl, filename) {
            const arr = dataUrl.split(',');
            const mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) u8arr[n] = bstr.charCodeAt(n);
            return new File([u8arr], filename, { type: mime });
        }

        function showResiPreview(src, name) {
            document.getElementById('resiPreviewImg').src = src;
            document.getElementById('resiPreviewWrap').style.display = 'block';
            const label = document.querySelector('#resiFileInput').closest('.custom-file').querySelector('.custom-file-label');
            if (label) label.textContent = name;
        }

        function resetResiInput() {
            modalResiFile = null;
            const input = document.getElementById('resiFileInput');
            input.value = '';
            document.getElementById('resiPreviewWrap').style.display = 'none';
            document.getElementById('resiPreviewImg').src = '';
            const label = input.closest('.custom-file').querySelector('.custom-file-label');
            if (label) label.textContent = 'Pilih file resi... (jpg, png, webp, pdf)';
        }

        document.getElementById('btnRemoveResi').addEventListener('click', resetResiInput);

        document.getElementById('resiFileInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                resetResiInput();
                return;
            }

            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            if (isPdf) {
                Swal.fire({ title: 'Mengkonversi PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                resizePdfToJpeg(file)
                    .then(dataUrl => {
                        Swal.close();
                        modalResiFile = dataUrlToFile(dataUrl, file.name.replace(/\.pdf$/i, '.jpg'));
                        showResiPreview(dataUrl, modalResiFile.name);
                    })
                    .catch(err => {
                        Swal.close();
                        resetResiInput();
                        Swal.fire('Oops!', 'Gagal mengkonversi PDF: ' + (err.message || err), 'error');
                    });
                return;
            }

            modalResiFile = file;
            const reader = new FileReader();
            reader.onload = e => showResiPreview(e.target.result, file.name);
            reader.readAsDataURL(file);
        });

        document.getElementById('btnSaveDropshipper').addEventListener('click', async function () {
            const id = activePenjualanId;
            const val = document.getElementById('dropshipperSelect').value;

            if (!val && !modalResiFile) {
                Swal.fire('Lengkapi Data', 'Pilih dropshipper atau upload file resi terlebih dahulu.', 'warning');
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Memproses...';

            const fd = new FormData();
            if (val) fd.append('dropshipper_id', val);
            if (modalResiFile) fd.append('file_resi', modalResiFile);

            try {
                const res = await fetch('/transaksi/penjualan/' + id + '/dropshipper', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: fd
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    $('#dropshipperModal').modal('hide');
                    Toast.fire({ icon: 'success', title: 'Berhasil', text: data.message || 'Data diperbarui.' });
                    setTimeout(() => location.reload(), 800);
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (err) {
                Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    </script>
@endsection
