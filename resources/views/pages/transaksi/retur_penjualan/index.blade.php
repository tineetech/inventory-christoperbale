@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Retur Penjualan</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Retur Penjualan</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="row">

                        <div class="col-md-12">

                            @if (session('success'))
                                <div class="card mb-4 border-success">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-1 text-success">
                                                <i class="feather icon-check-circle"></i> Success
                                            </h5>
                                            <p class="mb-0 text-muted">{{ session('success') }}</p>
                                        </div>
                                        <div class="display-4 text-success">
                                            <i class="feather icon-check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="card mb-4 border-danger">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-1 text-danger">
                                                <i class="feather icon-x-circle"></i> Error
                                            </h5>
                                            <p class="mb-0 text-muted">{{ session('error') }}</p>
                                        </div>
                                        <div class="display-4 text-danger">
                                            <i class="feather icon-x-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="col-sm-12">
                            {{-- ===== CARD 1: FILTER ===== --}}
                            <div class="card mb-4">
                                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-filter mr-2"></i> Filter Retur Penjualan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('laporan.retur') }}" id="returFilterForm">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label class="font-weight-bold">Dari Tanggal</label>
                                                <input type="date" class="form-control" name="dari_tanggal" value="{{ $filters['dari_tanggal'] }}">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label class="font-weight-bold">Sampai Tanggal</label>
                                                <input type="date" class="form-control" name="sampai_tanggal" value="{{ $filters['sampai_tanggal'] }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label class="font-weight-bold">Cari</label>
                                                <input type="text" class="form-control" name="search" id="searchTable" placeholder="Cari kode / dropshipper / alasan / status..." value="{{ $filters['search'] }}">
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-end align-items-center" style="gap:10px">
                                            <div class="d-flex" style="gap:10px">
                                                @if(hasPermission('edit', 'laporan_retur'))
                                                <button type="button" id="btnBulkSelesai" class="btn btn-success btn-sm d-none" onclick="bulkUpdateStatusSelesai()">
                                                    <i class="feather icon-check-circle"></i>
                                                    <span class="d-none d-sm-inline">Update Status Selesai (<span id="selectedCount">0</span>)</span>
                                                    <span class="d-inline d-sm-none">Selesai (<span class="selectedCountMobile">0</span>)</span>
                                                </button>
                                                @endif
                                                <button type="submit" class="btn btn-info">
                                                    <i class="feather icon-refresh-cw"></i> Proses
                                                </button>
                                                @if ($filters['search'])
                                                    <a href="{{ route('laporan.retur', ['dari_tanggal' => $filters['dari_tanggal'], 'sampai_tanggal' => $filters['sampai_tanggal'], 'per_page' => $filters['per_page']]) }}" class="btn btn-outline-secondary">
                                                        <i class="feather icon-rotate-ccw"></i> Reset
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="hidden" name="per_page" id="perPageInput" value="{{ $filters['per_page'] }}">
                                    </form>
                                </div>
                            </div>

                            {{-- ===== CARD 2: DATA ===== --}}
                            <div class="card mb-4">
                                <div style="border: none !important" class="card-header d-flex flex-wrap justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-rotate-ccw mr-2"></i> Data Retur Penjualan
                                        <span class="badge badge-light ml-1">{{ $returs->total() }} transaksi</span>
                                    </h6>
                                </div>
                                <div class="nav-tabs-top">
                                    <div class="tab-content d-flex justify-content-center" style="width: 100%">
                                        <div class="tab-pane fade show active pb-5" style="width: 95%"
                                            id="retur-stats">
                                            <div style="height: auto; overflow-x: auto" id="tab-table-1">
                                                <table class="table table-modern table-hover" id="table">
                                                    <thead>
                                                        <tr>
                                                            <th class="checkbox-col">
                                                                <input type="checkbox" id="checkAll">
                                                            </th>
                                                            <th class="sortable" data-column="1">No
                                                                <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>
                                                            <th class="sortable" data-column="2">Kode Penjualan
                                                                <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>
                                                            <th class="sortable" data-column="3">Dropshipper
                                                                <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>
                                                            <th class="sortable" data-column="4">Tgl Retur
                                                                <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>
                                                            <th>Alasan</th>
                                                            <th>Status</th>
                                                            <th>File</th>
                                                            <th>Dibuat Oleh</th>
                                                            @if(hasPermission('edit', 'laporan_retur') || hasPermission('hapus', 'laporan_retur'))
                                                            <th width="100">Action</th>
                                                            @endif
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach ($returs as $index => $retur)

                                                            {{-- MAIN ROW --}}
                                                            <tr class="main-row" data-id="{{ $retur->id }}">

                                                                <td class="checkbox-col">
                                                                    <input type="checkbox" class="row-check">
                                                                </td>

                                                                <td>{{ $returs->firstItem() + $index }}</td>

                                                                <td class="kode-click"
                                                                    style="color:#00499b; text-decoration:underline; cursor:pointer;">
                                                                    <strong>{{ $retur->penjualan->kode_penjualan ?? '-' }}</strong>
                                                                </td>

                                                                <td>{{ $retur->penjualan->dropshipper->nama ?? '-' }}</td>

                                                                <td>{{ date('d M Y', strtotime($retur->tanggal_retur)) }}</td>

                                                                <td>{{ Str::limit($retur->alasan_retur, 40) }}</td>

                                                                <td>
                                                                    @php
                                                                        $badgeMap = [
                                                                            'pending'  => 'badge-warning',
                                                                            'diproses' => 'badge-info',
                                                                            'selesai'  => 'badge-success',
                                                                            'ditolak'  => 'badge-danger',
                                                                        ];
                                                                        $badgeClass = $badgeMap[$retur->status] ?? 'badge-secondary';
                                                                    @endphp
                                                                    <span class="badge {{ $badgeClass }}">
                                                                        {{ ucfirst($retur->status) }}
                                                                    </span>
                                                                </td>

                                                                <td>
                                                                    @if ($retur->file_path)
                                                                        @if (str_starts_with($retur->file_mime ?? '', 'image/'))
                                                                            <a href="{{ Storage::url($retur->file_path) }}"
                                                                                target="_blank"
                                                                                onclick="event.stopPropagation()">
                                                                                <img src="{{ Storage::url($retur->file_path) }}"
                                                                                    style="height:36px; border-radius:4px;"
                                                                                    alt="bukti">
                                                                            </a>
                                                                        @elseif (str_starts_with($retur->file_mime ?? '', 'video/'))
                                                                            <a href="{{ Storage::url($retur->file_path) }}"
                                                                                target="_blank"
                                                                                class="btn btn-sm btn-outline-secondary"
                                                                                onclick="event.stopPropagation()">
                                                                                <i class="feather icon-play-circle"></i> Video
                                                                            </a>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted small">—</span>
                                                                    @endif
                                                                </td>

                                                                <td>{{ $retur->createdBy->nama ?? '-' }}</td>

                                                                @if(hasPermission('edit', 'laporan_retur') || hasPermission('hapus', 'laporan_retur'))
                                                                <td>
                                                                    {{-- <a href="{{ route('laporan.retur.show', $retur->id) }}"
                                                                        class="btn btn-sm btn-info"
                                                                        onclick="event.stopPropagation()">
                                                                        <i class="feather icon-eye"></i>
                                                                    </a> --}}

                                                                    
                                                                    @if(hasPermission('edit', 'laporan_retur'))
                                                                    <a href="{{ route('laporan.retur.edit', $retur->id) }}" class="btn btn-sm btn-warning">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>
                                                                    @endif
                                                                </td>
                                                                @endif

                                                            </tr>

                                                            {{-- DETAIL ROW --}}
                                                            <tr class="detail-row" id="detail-{{ $retur->id }}"
                                                                style="display:none; background:#f9f9f9;">
                                                                <td colspan="10">
                                                                    <div class="p-3">
                                                                        <table
                                                                            class="table table-sm table-bordered mb-0">
                                                                            <thead class="thead-light">
                                                                                <tr>
                                                                                    <th>SKU</th>
                                                                                    <th>Nama Barang</th>
                                                                                    <th>Qty Dijual</th>
                                                                                    <th>Qty Retur</th>
                                                                                    <th>No. Resi</th>
                                                                                    <th>Keterangan Item</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($retur->detail as $d)
                                                                                    <tr>
                                                                                        <td>{{ $d->barang->sku ?? '-' }}</td>
                                                                                        <td>{{ $d->barang->nama_barang ?? '-' }}</td>
                                                                                        <td>{{ $d->penjualanDetail->qty ?? '-' }}</td>
                                                                                        <td><strong>{{ $d->qty_retur }}</strong></td>
                                                                                        <td>{{ $d->penjualanDetail->nomor_resi ?? '-' }}</td>
                                                                                        <td>{{ $d->keterangan ?? '-' }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                                <tr
                                                                                    style="background:#f1f1f1; font-weight:bold;">
                                                                                    <td colspan="3"
                                                                                        class="text-right">Total Qty
                                                                                        Retur</td>
                                                                                    <td>{{ $retur->detail->sum('qty_retur') }}</td>
                                                                                    <td colspan="2"></td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </td>
                                                            </tr>

                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- PAGINATION CONTROLS --}}
                                            <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top" style="gap:8px">
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2 text-muted small">Show</span>
                                                    <select class="form-control form-control-sm" name="per_page" form="returFilterForm" style="width:72px" onchange="document.getElementById('returFilterForm').submit()">
                                                        <option value="10" {{ $filters['per_page'] == 10 ? 'selected' : '' }}>10</option>
                                                        <option value="25" {{ $filters['per_page'] == 25 ? 'selected' : '' }}>25</option>
                                                        <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50</option>
                                                        <option value="100" {{ $filters['per_page'] == 100 ? 'selected' : '' }}>100</option>
                                                    </select>
                                                    <span class="ml-2 text-muted small">entries</span>
                                                </div>
                                                <div class="text-muted small">
                                                    @if ($returs->total() > 0)
                                                        Showing <strong>{{ $returs->firstItem() }}</strong>
                                                        to <strong>{{ $returs->lastItem() }}</strong>
                                                        of <strong>{{ $returs->total() }}</strong> entries
                                                    @else
                                                        No entries found
                                                    @endif
                                                </div>
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0">
                                                        @php
                                                            $currentPage = $returs->currentPage();
                                                            $lastPage = $returs->lastPage();
                                                            $start = max(1, $currentPage - 2);
                                                            $end = min($lastPage, $currentPage + 2);
                                                            if ($start <= 3) $end = min($lastPage, 5);
                                                            if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                                                        @endphp

                                                        <li class="page-item {{ $returs->onFirstPage() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $returs->appends(['per_page' => request('per_page')])->url(1) }}">
                                                                <i class="feather icon-chevrons-left"></i>
                                                            </a>
                                                        </li>
                                                        <li class="page-item {{ $returs->onFirstPage() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $returs->previousPageUrl() }}">
                                                                <i class="feather icon-chevron-left"></i>
                                                            </a>
                                                        </li>

                                                        @if ($lastPage > 7 && $start > 1)
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $returs->url(1) }}">1</a>
                                                            </li>
                                                            @if ($start > 2)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                        @endif

                                                        @for ($i = $start; $i <= $end; $i++)
                                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                                <a class="page-link" href="{{ $returs->url($i) }}">{{ $i }}</a>
                                                            </li>
                                                        @endfor

                                                        @if ($lastPage > 7 && $end < $lastPage)
                                                            @if ($end < $lastPage - 1)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $returs->url($lastPage) }}">{{ $lastPage }}</a>
                                                            </li>
                                                        @endif

                                                        <li class="page-item {{ !$returs->hasMorePages() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $returs->nextPageUrl() }}">
                                                                <i class="feather icon-chevron-right"></i>
                                                            </a>
                                                        </li>
                                                        <li class="page-item {{ !$returs->hasMorePages() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $returs->appends(['per_page' => request('per_page')])->url($lastPage) }}">
                                                                <i class="feather icon-chevrons-right"></i>
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

    // ============================================================
    // CHECK ALL & BULK BAR
    // ============================================================
    document.getElementById('checkAll').addEventListener('click', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-check')) updateBulkBar();
    });

    function getSelectedIds() {
        return [...document.querySelectorAll('tbody .row-check:checked')].map(cb => {
            const tr = cb.closest('tr.main-row');
            return tr ? parseInt(tr.getAttribute('data-id')) : null;
        }).filter(Boolean);
    }

    function updateBulkBar() {
        const ids = getSelectedIds();
        const btn = document.getElementById('btnBulkSelesai');
        if (!btn) return;
        document.querySelectorAll('#selectedCount, .selectedCountMobile').forEach(el => el.textContent = ids.length);
        if (ids.length > 0) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
        // sync header checkbox
        const allChecks = document.querySelectorAll('tbody .row-check');
        const checked = document.querySelectorAll('tbody .row-check:checked');
        document.getElementById('checkAll').checked = allChecks.length > 0 && allChecks.length === checked.length;
        document.getElementById('checkAll').indeterminate = checked.length > 0 && checked.length < allChecks.length;
    }

    async function bulkUpdateStatusSelesai() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        const confirm = await Swal.fire({
            title: 'Update status ke Selesai?',
            html: `Akan mengupdate <strong>${ids.length}</strong> retur terpilih menjadi <span class="badge badge-success">Selesai</span> dan mengembalikan stok.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, update!',
            cancelButtonText: 'Batal'
        });
        if (!confirm.isConfirmed) return;

        const btn = document.getElementById('btnBulkSelesai');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Memproses...';

        try {
            const res = await fetch('{{ route("laporan.retur.bulk-status") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ ids, status: 'selesai' })
            });
            const json = await res.json();
            if (res.ok && json.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: json.message, timer: 1800, showConfirmButton: false }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: json.message || 'Terjadi kesalahan.' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            updateBulkBar();
        }
    }

    // Sembunyikan semua detail row saat pertama load
    document.querySelectorAll(".detail-row").forEach(row => row.style.display = "none");

    // Search: Enter untuk submit (server-side)
    document.getElementById('searchTable')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('returFilterForm').submit();
        }
    });

    // ============================================================
    // SORT (client-side, hanya halaman saat ini)
    // ============================================================
    let currentSortColumn    = null;
    let currentSortDirection = "asc";

    function sortTable(columnIndex) {
        if (currentSortColumn === columnIndex) {
            currentSortDirection = currentSortDirection === "asc" ? "desc" : "asc";
        } else {
            currentSortColumn    = columnIndex;
            currentSortDirection = "asc";
        }

        let tbody = document.querySelector("#table tbody");
        let mainRows = [...tbody.querySelectorAll("tr.main-row")];

        mainRows.sort((a, b) => {
            let aCell = a.children[columnIndex];
            let bCell = b.children[columnIndex];
            if (!aCell || !bCell) return 0;
            let aText = aCell.innerText.toLowerCase().trim();
            let bText = bCell.innerText.toLowerCase().trim();

            if (!isNaN(aText) && !isNaN(bText) && aText !== '' && bText !== '') {
                return currentSortDirection === "asc" ? aText - bText : bText - aText;
            }
            return currentSortDirection === "asc"
                ? aText.localeCompare(bText)
                : bText.localeCompare(aText);
        });

        mainRows.forEach(row => {
            let detailRow = document.getElementById("detail-" + row.getAttribute("data-id"));
            tbody.appendChild(row);
            if (detailRow) tbody.appendChild(detailRow);
        });
    }

    document.querySelectorAll(".sortable").forEach(header => {
        header.addEventListener("click", function () {
            sortTable(parseInt(this.getAttribute("data-column")));
        });
    });

    // ============================================================
    // EXPAND DETAIL ROW — klik baris tampilkan item retur di bawah
    // ============================================================
    document.querySelectorAll(".main-row").forEach(row => {
        row.addEventListener("click", function (e) {
            // Jangan trigger jika klik tombol / link / checkbox / input
            if (e.target.closest("button") || e.target.closest("a") || e.target.closest("input")) return;

            let id        = this.getAttribute("data-id");
            let detailRow = document.getElementById("detail-" + id);

            if (detailRow.style.display === "table-row") {
                detailRow.style.display = "none";
            } else {
                detailRow.style.display = "table-row";
            }
        });
    });

    // ============================================================
    // AUTO-HIDE SUCCESS CARD
    // ============================================================
    setTimeout(function () {
        let alertCard = document.querySelector('.border-success');
        if (alertCard) {
            alertCard.style.transition = "0.5s";
            alertCard.style.opacity    = "0";
            setTimeout(() => alertCard.remove(), 500);
        }
    }, 4000);
</script>
@endsection