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
                        <input type="hidden" name="input_per_page" value="{{ $filters['input_per_page'] }}">
                        <input type="hidden" name="summary_per_page" value="{{ $filters['summary_per_page'] }}">
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
                                <select class="form-control" name="barang_id" id="barang_select">
                                    <option value="">(Semua)</option>
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
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="font-weight-bold">Cari</label>
                                <input type="text" class="form-control" name="search" placeholder="SKU / Nama Barang" value="{{ $filters['search'] }}">
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
                                {{ $isKaryawan ? 'Laporan Stok Barang' : 'Laporan Stok Barang' }}
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
                                <input type="hidden" name="input_per_page" value="{{ $filters['input_per_page'] }}">
                                <input type="hidden" name="summary_per_page" value="{{ $filters['summary_per_page'] }}">

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
                                                    <td><strong>{{ \Illuminate\Support\Str::limit($item->nama_barang, 25, '...') }}</strong></td>
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
                                                <td><strong>{{ \Illuminate\Support\Str::limit($item->nama_barang, 25, '...') }}</strong></td>
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

                        {{-- Pagination Left Table --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top"
                            style="gap:8px">
                            <div class="d-flex align-items-center">
                                <span class="mr-2 text-muted small">Show</span>
                                <select class="form-control form-control-sm" name="input_per_page"
                                    form="stokFilterForm" style="width:72px"
                                    onchange="document.getElementById('stokFilterForm').submit()">
                                    <option value="10" {{ $filters['input_per_page'] == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $filters['input_per_page'] == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $filters['input_per_page'] == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $filters['input_per_page'] == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="ml-2 text-muted small">entries</span>
                            </div>
                            <div class="text-muted small" id="leftTableInfo">
                                Showing <strong>0</strong> to <strong>0</strong> of <strong>{{ $leftTableRows->count() }}</strong> entries
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="leftPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-layers mr-2"></i>
                                {{ $isSuperAdmin ? 'Laporan Stok Movement' : 'Laporan Stok Movement' }}
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
                                        <th>Stok Sebelum</th>
                                        <th>Stok Sesudah</th>
                                        {{-- <th>Status Approval</th> --}}
                                        <th>Selisih</th>
                                        <th>Keterangan</th>
                                        {{-- @if ($isSuperAdmin)
                                            <th>Aksi</th>
                                        @endif --}}
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
                                                <strong>{{ \Illuminate\Support\Str::limit($item->nama_barang, 25, '...') }}</strong>
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
                                            <td>{{ $item->stok_sebelum }}</td>
                                            <td>{{ $item->stok_sesudah }}</td>
                                            {{-- <td>
                                                @if (!$item->has_input)
                                                    <span class="badge badge-secondary">Belum Input</span>
                                                @elseif ($item->approval_status === 'confirmed')
                                                    <span class="badge badge-success">Dikonfirmasi</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td> --}}
                                            <td>
                                                <span class="
                                                    {{ $item->selisih_minimum > 0 ? 'text-success' : ($item->selisih_minimum < 0 ? 'text-danger' : 'text-muted') }}
                                                ">
                                                    {{ sprintf('%+d', $item->selisih_minimum) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->keterangan }}</td>
                                            {{-- @if ($isSuperAdmin)
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
                                            @endif --}}
                                        </tr>
                                    @empty
                                        <tr data-empty-row="true">
                                            <td colspan="{{ $isSuperAdmin ? 10 : 9 }}" class="text-center text-muted py-4">Belum ada data stok movement pada filter ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Right Table --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top"
                            style="gap:8px">
                            <div class="d-flex align-items-center">
                                <span class="mr-2 text-muted small">Show</span>
                                <select class="form-control form-control-sm" name="summary_per_page"
                                    form="stokFilterForm" style="width:72px"
                                    onchange="document.getElementById('stokFilterForm').submit()">
                                    <option value="10" {{ $filters['summary_per_page'] == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $filters['summary_per_page'] == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $filters['summary_per_page'] == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $filters['summary_per_page'] == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="ml-2 text-muted small">entries</span>
                            </div>
                            <div class="text-muted small" id="rightTableInfo">
                                Showing <strong>0</strong> to <strong>0</strong> of <strong>{{ $reportRows->count() }}</strong> entries
                            </div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="rightPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let preselectedId = '{{ $filters["barang_id"] ?? "" }}';
            let $select = $('#barang_select').select2({
                placeholder: "Cari SKU / Nama Barang",
                allowClear: true,
                width: '100%',
                ajax: {
                    url: "/api/product/search",
                    dataType: "json",
                    delay: 150,
                    cache: true,
                    data: function(params) {
                        return { q: params.term, page: params.page || 1 };
                    },
                    processResults: function(data) {
                        let results = data.map(p => ({
                            id: p.id,
                            text: "#" + p.sku + " - " + p.nama_barang
                        }));
                        if (preselectedId && !params.term) {
                            return { results: results };
                        }
                        return { results: [{ id: '', text: '(Semua)' }, ...results] };
                    }
                }
            });

            if (preselectedId) {
                $.ajax({
                    url: '/api/product/search',
                    data: { q: '' },
                    dataType: 'json',
                    success: function(data) {
                        let found = data.find(p => p.id == preselectedId);
                        if (found) {
                            let option = new Option("#" + found.sku + " - " + found.nama_barang, found.id, true, true);
                            $select.append(option).trigger('change');
                        }
                    }
                });
            }
        });

        // ── Client-side Pagination (Left & Right tables) ─────
        function initTablePagination(tableId, paginationId, infoId, perPage) {
            const table = document.getElementById(tableId);
            const pagination = document.getElementById(paginationId);
            const info = document.getElementById(infoId);
            if (!table || !pagination || !info) return;

            const rows = Array.from(table.querySelectorAll('tbody tr')).filter(r => !r.hasAttribute('data-empty-row'));
            const total = rows.length;
            let current = 1;
            let perPageVal = perPage || 10;

            function showPage() {
                const start = (current - 1) * perPageVal;
                const end = Math.min(start + perPageVal, total);
                rows.forEach((r, i) => {
                    r.style.display = (i >= start && i < end) ? '' : 'none';
                    // hide associated detail row too
                    const detail = document.getElementById('detail-' + r.getAttribute('data-id'));
                    if (detail) detail.style.display = 'none';
                });
                info.innerHTML = 'Showing <strong>' + (total ? start + 1 : 0) + '</strong> to <strong>' + end + '</strong> of <strong>' + total + '</strong> entries';
                renderPagination();
            }

            function renderPagination() {
                const pageCount = Math.ceil(total / perPageVal);
                pagination.innerHTML = '';
                if (pageCount <= 1) return;

                const add = (label, page, disabled, active) => {
                    const li = document.createElement('li');
                    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = '#';
                    a.innerHTML = label;
                    if (!disabled) a.addEventListener('click', e => { e.preventDefault(); current = page; showPage(); });
                    li.appendChild(a);
                    pagination.appendChild(li);
                };

                add('<i class="feather icon-chevrons-left"></i>', 1, current === 1, false);
                add('<i class="feather icon-chevron-left"></i>', current - 1, current === 1, false);

                let start = Math.max(1, current - 2);
                let end = Math.min(pageCount, start + 4);
                if (end - start < 4) start = Math.max(1, end - 4);

                if (pageCount > 7 && start > 1) {
                    add('1', 1, false, false);
                    if (start > 2) add('...', null, true, false);
                }

                for (let i = start; i <= end; i++) add(i, i, false, i === current);

                if (pageCount > 7 && end < pageCount) {
                    if (end < pageCount - 1) add('...', null, true, false);
                    add('' + pageCount, pageCount, false, false);
                }

                add('<i class="feather icon-chevron-right"></i>', current + 1, current === pageCount, false);
                add('<i class="feather icon-chevrons-right"></i>', pageCount, current === pageCount, false);
            }

            showPage();
        }

        initTablePagination('stokTableMain', 'leftPagination', 'leftTableInfo', {{ $filters['input_per_page'] ?? 10 }});
        initTablePagination('stokTableSummary', 'rightPagination', 'rightTableInfo', {{ $filters['summary_per_page'] ?? 10 }});
    </script>
@endsection
