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
                            <div class="card mb-4">
                                <div style="border: none !important"
                                    class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-rotate-ccw mr-2"></i> Data Retur Penjualan
                                    </h6>

                                    <div class="d-flex gap-5">
                                        <div class="d-flex mr-5 align-items-center">
                                            <input type="text" class="form-control form-control-sm mr-2"
                                                id="searchTable" placeholder="Search retur..."
                                                style="width:150px">
                                        </div>
                                    </div>
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
                                                            <th width="100">Action</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach ($returs as $index => $retur)

                                                            {{-- MAIN ROW --}}
                                                            <tr class="main-row" data-id="{{ $retur->id }}">

                                                                <td class="checkbox-col">
                                                                    <input type="checkbox" class="row-check">
                                                                </td>

                                                                <td>{{ $index + 1 }}</td>

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

                                                                <td>
                                                                    <a href="{{ route('laporan.retur.show', $retur->id) }}"
                                                                        class="btn btn-sm btn-info"
                                                                        onclick="event.stopPropagation()">
                                                                        <i class="feather icon-eye"></i>
                                                                    </a>

                                                                    <a href="{{ route('laporan.retur.edit', $retur->id) }}" class="btn btn-sm btn-warning">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>
                                                                </td>

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
                                                                                    <th>Qty Beli</th>
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
                                            <div
                                                class="d-flex justify-content-between align-items-center px-3 py-2 border-top">

                                                <div class="d-flex align-items-center mr-5">
                                                    <span class="mr-2 text-muted small">Show</span>
                                                    <select class="form-control form-control-sm" id="entriesSelect"
                                                        style="width:80px">
                                                        <option value="10" selected>10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                    </select>
                                                    <span class="ml-2 text-muted small">entries</span>
                                                </div>

                                                <div class="text-muted small" id="tableInfo">
                                                    Showing <strong>1</strong> to <strong>10</strong> of
                                                    <strong>0</strong> entries
                                                </div>

                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
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
    // ============================================================
    // CHECK ALL
    // ============================================================
    document.getElementById('checkAll').addEventListener('click', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });

    // ============================================================
    // PAGINATION & SEARCH ENGINE
    // ============================================================
    let rows          = document.querySelectorAll("#table tbody tr.main-row");
    let entriesSelect = document.getElementById("entriesSelect");
    let pagination    = document.getElementById("pagination");
    let tableInfo     = document.getElementById("tableInfo");

    let currentPage  = 1;
    let rowsPerPage  = parseInt(entriesSelect.value);
    let filteredRows = [...rows];

    function displayTable() {
        rowsPerPage  = parseInt(entriesSelect.value);
        let start    = (currentPage - 1) * rowsPerPage;
        let end      = start + rowsPerPage;

        // Sembunyikan semua main-row dan detail-row
        rows.forEach(row => row.style.display = "none");
        document.querySelectorAll(".detail-row").forEach(r => r.style.display = "none");

        filteredRows.slice(start, end).forEach(row => row.style.display = "");

        updateInfo();
    }

    function setupPagination() {
        pagination.innerHTML = "";
        let pageCount = Math.ceil(filteredRows.length / rowsPerPage);

        for (let i = 1; i <= pageCount; i++) {
            let li = document.createElement("li");
            li.classList.add("page-item");
            if (i === currentPage) li.classList.add("active");

            let a = document.createElement("a");
            a.classList.add("page-link");
            a.href    = "#";
            a.innerText = i;

            a.addEventListener("click", function (e) {
                e.preventDefault();
                currentPage = i;
                displayTable();
                setupPagination();
            });

            li.appendChild(a);
            pagination.appendChild(li);
        }
    }

    function updateInfo() {
        let start = (currentPage - 1) * rowsPerPage + 1;
        let end   = Math.min(currentPage * rowsPerPage, filteredRows.length);
        if (filteredRows.length === 0) start = 0;
        tableInfo.innerHTML = `Showing ${start} to ${end} of ${filteredRows.length} entries`;
    }

    entriesSelect.addEventListener("change", function () {
        currentPage = 1;
        displayTable();
        setupPagination();
    });

    // Sembunyikan semua detail row saat pertama load
    document.querySelectorAll(".detail-row").forEach(row => row.style.display = "none");

    displayTable();
    setupPagination();

    // ============================================================
    // SEARCH
    // ============================================================
    document.getElementById('searchTable').addEventListener('keyup', function () {
        let value    = this.value.toLowerCase();
        filteredRows = [...rows].filter(row => row.textContent.toLowerCase().includes(value));
        currentPage  = 1;
        displayTable();
        setupPagination();
    });

    // ============================================================
    // SORT
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

        filteredRows.sort((a, b) => {
            let aText = a.children[columnIndex].innerText.toLowerCase();
            let bText = b.children[columnIndex].innerText.toLowerCase();

            if (!isNaN(aText) && !isNaN(bText)) {
                return currentSortDirection === "asc" ? aText - bText : bText - aText;
            }
            return currentSortDirection === "asc"
                ? aText.localeCompare(bText)
                : bText.localeCompare(aText);
        });

        let tbody = document.querySelector("#table tbody");
        filteredRows.forEach(row => tbody.appendChild(row));

        currentPage = 1;
        displayTable();
        setupPagination();
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