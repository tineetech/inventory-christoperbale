@extends('layouts.main')

@php
    $statusLabels = [
        'semua' => 'Semua',
        'aman' => 'Aman',
        'minimum' => 'Minimum',
        'habis' => 'Habis',
    ];

    $currentRole = Auth::guard('pengguna')->user()->role->nama_role ?? null;
    $isSuperAdmin = $currentRole === 'super_admin';
    $isKaryawan = $currentRole === 'karyawan';
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

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.stok') }}" id="stokFilterForm">
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
                            <button type="submit" class="btn btn-info">
                                <i class="feather icon-refresh-cw"></i> Proses
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-archive mr-2"></i>
                                {{ $isKaryawan ? 'Input Laporan Stok' : 'Data Laporan Stok' }}
                            </h6>
                            <div class="d-flex align-items-center flex-wrap justify-content-end" style="gap: 12px;">
                                <span class="badge badge-light">{{ $leftTableRows->count() }} barang</span>
                                <small class="text-muted">
                                    Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                                </small>
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    <a href="{{ route('laporan.stok.input.print', $filters) }}" target="_blank" class="btn btn-success btn-sm">
                                        <i class="feather icon-printer"></i> Print
                                    </a>
                                    <a href="{{ route('laporan.stok.input.pdf', $filters) }}" class="btn btn-danger btn-sm">
                                        <i class="feather icon-file-text"></i> PDF
                                    </a>
                                    <a href="{{ route('laporan.stok.input.excel', $filters) }}" class="btn btn-warning btn-sm text-white">
                                        <i class="feather icon-download"></i> Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if ($isKaryawan)
                            <form method="POST" action="{{ route('laporan.stok.store') }}">
                                @csrf
                                <input type="hidden" name="dari_tanggal" value="{{ $filters['dari_tanggal'] }}">
                                <input type="hidden" name="sampai_tanggal" value="{{ $filters['sampai_tanggal'] }}">
                                <input type="hidden" name="barang_filter_id" value="{{ $filters['barang_id'] }}">
                                <input type="hidden" name="status_filter" value="{{ $filters['status'] }}">
                                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}">

                                <div class="table-responsive px-3 pb-3">
                                    <table class="table table-modern table-hover mb-0" id="stokTableMain">
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
                                            @forelse ($leftTableRows as $index => $item)
                                                <tr>
                                                    <td>{{ $item->no }}</td>
                                                    <td>
                                                        <input type="hidden" name="items[{{ $index }}][barang_id]"
                                                            value="{{ $item->barang_id }}">
                                                        {{ $item->sku }}
                                                    </td>
                                                    <td><strong>{{ $item->nama_barang }}</strong></td>
                                                    <td>{{ $item->satuan }}</td>
                                                    <td style="min-width: 140px;">
                                                        <input type="number" min="0" class="form-control"
                                                            name="items[{{ $index }}][stok_saat_ini]"
                                                            value="{{ old('items.' . $index . '.stok_saat_ini', $item->stok_saat_ini) }}">
                                                    </td>
                                                    <td style="min-width: 140px;">
                                                        <input type="number" min="0" class="form-control"
                                                            name="items[{{ $index }}][stok_minimum]"
                                                            value="{{ old('items.' . $index . '.stok_minimum', $item->stok_minimum) }}">
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $item->stok_status === 'aman' ? 'success' : ($item->stok_status === 'minimum' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($item->stok_status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr data-empty-row="true">
                                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data stok pada filter ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($leftTableRows->count() > 0)
                                    <div class="px-3 pb-3 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather icon-save"></i> Simpan Input
                                        </button>
                                    </div>
                                @endif
                            </form>
                        @else
                            <div class="table-responsive px-3 pb-3">
                                <table class="table table-modern table-hover mb-0" id="stokTableMain">
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
                                        @forelse ($leftTableRows as $item)
                                            <tr>
                                                <td>{{ $item->no }}</td>
                                                <td>{{ $item->sku }}</td>
                                                <td><strong>{{ $item->nama_barang }}</strong></td>
                                                <td>{{ $item->satuan }}</td>
                                                <td>{{ $item->stok_saat_ini }}</td>
                                                <td>{{ $item->stok_minimum }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $item->stok_status === 'aman' ? 'success' : ($item->stok_status === 'minimum' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($item->stok_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr data-empty-row="true">
                                                <td colspan="7" class="text-center text-muted py-4">Belum ada data stok pada filter ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @include('pages.laporan.partials.pagination-controls', [
                            'prefix' => 'stok',
                            'perPage' => $filters['per_page'],
                            'totalRows' => max($leftTableRows->count(), $reportRows->count()),
                            'formId' => 'stokFilterForm',
                        ])
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-layers mr-2"></i>
                                {{ $isSuperAdmin ? 'Review Laporan Stok' : 'Ringkasan Laporan Stok' }}
                            </h6>
                            <div class="d-flex align-items-center flex-wrap justify-content-end" style="gap: 12px;">
                                <small class="text-muted">
                                    Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                                </small>
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    <a href="{{ route('laporan.stok.summary.print', $filters) }}" target="_blank" class="btn btn-success btn-sm">
                                        <i class="feather icon-printer"></i> Print
                                    </a>
                                    <a href="{{ route('laporan.stok.summary.pdf', $filters) }}" class="btn btn-danger btn-sm">
                                        <i class="feather icon-file-text"></i> PDF
                                    </a>
                                    <a href="{{ route('laporan.stok.summary.excel', $filters) }}" class="btn btn-warning btn-sm text-white">
                                        <i class="feather icon-download"></i> Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive px-3 pb-3">
                            <table class="table table-modern table-hover mb-0" id="stokTableSummary">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>SKU</th>
                                        <th>Nama Barang</th>
                                        <th>Jenis</th>
                                        <th>Qty</th>
                                        <th>Stok Sesudah</th>
                                        <th>Status Approval</th>
                                        <th>Selisih Min.</th>
                                        @if ($isSuperAdmin)
                                            <th>Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reportRows as $item)
                                        <tr>
                                            <td>{{ $item->no }}</td>
                                            <td>
                                                {{ optional($item->movement_date)->format('d M Y H:i') ?? '-' }}
                                            </td>
                                            <td>{{ $item->sku }}</td>
                                            <td>
                                                <strong>{{ $item->nama_barang }}</strong>
                                                <div class="small text-muted">{{ $item->satuan }}</div>
                                                @if ($item->keterangan)
                                                    <div class="small text-muted">{{ $item->keterangan }}</div>
                                                @endif
                                                @if ($item->input_by_name)
                                                    <div class="small text-muted">Input: {{ $item->input_by_name }}</div>
                                                @endif
                                            </td>
                                            <td class="text-capitalize">
                                                {{ str_replace('_', ' ', $item->jenis) }}
                                                @if ($item->referensi_tipe)
                                                    <div class="small text-muted">
                                                        {{ str_replace('_', ' ', $item->referensi_tipe) }}
                                                        @if ($item->referensi_id)
                                                            #{{ $item->referensi_id }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                            <td>{{ $item->stok_sesudah }}</td>
                                            <td>
                                                @if (!$item->has_input)
                                                    <span class="badge badge-secondary">Belum Input</span>
                                                @elseif ($item->approval_status === 'confirmed')
                                                    <span class="badge badge-success">Dikonfirmasi</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->selisih_minimum }}
                                                <div class="small text-muted text-capitalize">{{ $item->stok_status }}</div>
                                            </td>
                                            @if ($isSuperAdmin)
                                                <td>
                                                    @if ($item->has_input && $item->approval_status !== 'confirmed')
                                                        <form method="POST"
                                                            action="{{ route('laporan.stok.confirm', $item->report_id) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                Konfirmasi
                                                            </button>
                                                        </form>
                                                    @elseif ($item->confirmed_by_name)
                                                        <span class="small text-muted">
                                                            {{ $item->confirmed_by_name }}
                                                            @if ($item->confirmed_at)
                                                                <br>{{ $item->confirmed_at->format('d M Y H:i') }}
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr data-empty-row="true">
                                            <td colspan="{{ $isSuperAdmin ? 10 : 9 }}" class="text-center text-muted py-4">Belum ada data stok movement pada filter ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('pages.laporan.partials.pagination-script')
    <script>
        window.initReportPagination({
            tableIds: ['stokTableMain', 'stokTableSummary'],
            entriesSelectId: 'stokEntriesSelect',
            paginationId: 'stokPagination',
            tableInfoId: 'stokTableInfo',
            formId: 'stokFilterForm'
        });
    </script>
@endsection
