@extends('layouts.main')

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
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-header-title mb-0">
                                            <i class="feather icon-truck mr-2"></i> Data Penjualan
                                            <span id="lastRefreshed"
                                                class="text-muted small ml-1 d-none d-md-inline"></span>
                                        </h6>
                                        <a href="{{ route('penjualan.create') }}" class="btn btn-primary btn-sm">
                                            <i class="feather icon-plus"></i>
                                            <span class="d-none d-sm-inline">Tambah Penjualan</span>
                                            <span class="d-inline d-sm-none">Tambah</span>
                                        </a>
                                    </div>

                                    {{-- Row 2: Scan input (full width on mobile) --}}
                                    {{-- <div class="mb-2">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white border-0">
                                                    <i class="feather icon-zap mr-1"></i>
                                                    <span class="d-none d-sm-inline small">Scan Resi</span>
                                                </span>
                                            </div>
                                            <input type="text"
                                                   class="form-control"
                                                   id="scanOutPenjualan"
                                                   placeholder="Arahkan scanner ke nomor resi..."
                                                   autocomplete="off"
                                                   inputmode="none">
                                        </div>
                                    </div> --}}

                                    {{-- Row 3: Filter tanggal + search --}}
                                    <div class="d-flex flex-wrap align-items-center" style="gap:6px">
                                        <input type="date" id="dateFrom" class="form-control form-control-sm"
                                            style="min-width:130px;flex:1 1 130px">
                                        <span class="text-muted small">s/d</span>
                                        <input type="date" id="dateTo" class="form-control form-control-sm"
                                            style="min-width:130px;flex:1 1 130px">
                                        <input type="text" class="form-control form-control-sm" id="searchTable"
                                            placeholder="Search penjualan..." style="min-width:140px;flex:2 1 160px">

                                        <input type="text" class="form-control" id="scanOutPenjualan"
                                            placeholder="Arahkan scanner ke nomor resi..." autocomplete="off"
                                            style="min-width:140px;flex:2 1 160px" inputmode="none">
                                    </div>

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
                                                            <th class="sortable" data-column="1">No</th>
                                                            <th class="sortable" data-column="2">Kode Penjualan <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th class="sortable" data-column="3">Nomor Resi <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th class="sortable d-none d-lg-table-cell" data-column="4">No.
                                                                Pesanan <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>
                                                            {{-- <th class="sortable d-none d-xl-table-cell" data-column="5">No. Transaksi <i class="feather icon-chevrons-up sort-icon"></i></th> --}}
                                                            <th class="sortable d-none d-md-table-cell" data-column="6">
                                                                Dropshipper <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th class="sortable" data-column="7">Tanggal <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th class="sortable d-none d-md-table-cell" data-column="8">
                                                                Total Harga <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th class="sortable" data-column="9">Scan Out <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th class="sortable d-none d-sm-table-cell" data-column="10">
                                                                Draft? <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>
                                                            <th class="sortable d-none d-xl-table-cell" data-column="11">
                                                                Keterangan <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>
                                                            <th style="width:90px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableBody">
                                                        <tr id="loadingRow">
                                                            <td colspan="13" class="text-center py-4">
                                                                <div class="spinner-border spinner-border-sm text-primary mr-2"
                                                                    role="status"></div>
                                                                Memuat data...
                                                            </td>
                                                        </tr>
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
                                                        <option value="10" selected>10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                    </select>
                                                    <span class="ml-2 text-muted small">entries</span>
                                                </div>
                                                <div class="text-muted small" id="tableInfo"></div>
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
        // =====================================================
        // CONFIG
        // =====================================================
        const API_BASE = '/api/penjualan';
        const REFRESH_MS = 10000;
        const CSRF_TOKEN = '{{ csrf_token() }}';

        // =====================================================
        // STATE
        // =====================================================
        let allData = [];
        let filteredData = [];
        let currentPage = 1;
        let rowsPerPage = 10;
        let currentSortCol = null;
        let currentSortDir = 'asc';
        let scanTimeout = null;

        // Simpan id baris yang sedang dibuka detailnya
        let openDetailIds = new Set();

        // =====================================================
        // HELPERS
        // =====================================================
        function focusScan() {
            const el = document.getElementById('scanOutPenjualan');
            if (el) el.focus();
        }

        /** Format angka → "50.000" (titik sebagai pemisah ribuan) */
        function formatRupiah(num) {
            return Number(num).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // =====================================================
        // FETCH DATA
        // =====================================================
        async function fetchData(silent = false) {
            try {
                if (!silent) {
                    document.getElementById('loadingRow').style.display = '';
                }
                const res = await fetch(`${API_BASE}/list`);
                const json = await res.json();
                if (json.success) {
                    const scannedIds = new Set(
                        allData.filter(p => p._justScanned).map(p => p.id)
                    );
                    allData = json.penjualan.map(p => ({
                        ...p,
                        _justScanned: scannedIds.has(p.id)
                    }));
                    // Taruh di fetchData() setelah allData di-set, hapus setelah fix
                    console.log('Sample tanggal dari API:', allData[0]?.tanggal);
                    applyFilters();
                    updateLastRefreshed();
                }
            } catch (e) {
                console.error('Gagal fetch data penjualan:', e);
            }
        }

        function updateLastRefreshed() {
            const el = document.getElementById('lastRefreshed');
            const now = new Date();
            el.textContent = `· ${now.toLocaleTimeString('id-ID')}`;
        }

        // =====================================================
        // RENDER TABLE
        // =====================================================
        function renderTable() {
            const tbody = document.getElementById('tableBody');

            if (filteredData.length === 0) {
                tbody.innerHTML = `<tr><td colspan="13" class="text-center text-muted py-4">Tidak ada data.</td></tr>`;
                updateInfo();
                renderPagination();
                return;
            }

            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const slice = filteredData.slice(start, end);

            // Simpan state detail yang sedang terbuka sebelum re-render
            // (agar tidak hilang saat auto-refresh)
            document.querySelectorAll('.detail-row').forEach(row => {
                const id = row.id.replace('detail-', '');
                if (row.style.display === 'table-row') {
                    openDetailIds.add(parseInt(id));
                }
            });

            let html = '';
            slice.forEach((pj, idx) => {
                const globalIdx = start + idx + 1;

                let scanBadge = '';
                if (pj.scan_out === 'pending') scanBadge = `<span class="badge badge-warning">Pending</span>`;
                else if (pj.scan_out === 'done') scanBadge = `<span class="badge badge-success">Done</span>`;
                else if (pj.scan_out === 'failed') scanBadge = `<span class="badge badge-danger">Failed</span>`;
                else scanBadge = `<span class="badge badge-secondary">-</span>`;

                let draftBadge = '';
                if (pj.is_draft === 'yes') draftBadge =
                    `<span class="badge text-white" style="background:#00499b">Ya</span>`;
                else if (pj.is_draft === 'no') draftBadge = `<span class="badge badge-danger">Tidak</span>`;
                else draftBadge = `<span class="badge badge-secondary">-</span>`;

                const rowBg = pj._justScanned ? 'background:#e8f5e9;' : '';
                // Restore state: buka kembali detail yang sebelumnya terbuka
                const detailShow = openDetailIds.has(pj.id) ? 'table-row' : 'none';

                html += `
        <tr class="main-row" data-id="${pj.id}" data-date="${pj.tanggal}" style="${rowBg}cursor:pointer">
            <td class="checkbox-col"><input type="checkbox" class="row-check"></td>
            <td>${globalIdx}</td>
            <td class="kode-click" style="color:#00499b;text-decoration:underline;white-space:nowrap">
                <strong>${pj.kode_penjualan}</strong>
            </td>
            <td style="white-space:nowrap">${pj.nomor_resi ?? '-'}</td>
            <td class="d-none d-lg-table-cell">${pj.nomor_pesanan ?? '-'}</td>
            <td class="d-none d-md-table-cell">${pj.dropshipper ?? '-'}</td>
            <td style="white-space:nowrap">${pj.tanggal_fmt}</td>
            <td class="d-none d-md-table-cell" style="font-weight:bold;white-space:nowrap">Rp ${formatRupiah(pj.total_harga)}</td>
            <td>${scanBadge}</td>
            <td class="d-none d-sm-table-cell">${draftBadge}</td>
            <td class="d-none d-xl-table-cell">${pj.keterangan ?? ''}</td>
            <td style="white-space:nowrap">
                <a href="/penjualan/${pj.id}/edit" class="btn btn-sm btn-danger">
                    <i class="feather icon-edit"></i> 
                    Retur
                </a>
                <a href="/penjualan/${pj.id}/edit" class="btn btn-sm btn-warning">
                    <i class="feather icon-edit"></i>
                </a>
                <form id="delete-form-${pj.id}" action="/penjualan/${pj.id}" method="POST" style="display:inline">
                    <input type="hidden" name="_token" value="${CSRF_TOKEN}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" onclick="confirmDelete(${pj.id})" class="btn btn-sm btn-danger">
                        <i class="feather icon-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        <tr class="detail-row" id="detail-${pj.id}" style="display:${detailShow};background:#f9f9f9">
            <td colspan="13">
                <div class="p-2 p-md-3" style="overflow-x:auto">
                    <table class="table table-sm table-bordered mb-0" style="min-width:500px">
                        <thead class="thead-light">
                            <tr>
                                <th>No Resi</th><th>SKU</th><th>Nama Barang</th>
                                <th>Stok</th><th>Qty</th><th>Harga</th><th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${renderDetailRows(pj.detail)}</tbody>
                    </table>
                </div>
            </td>
        </tr>`;
            });

            tbody.innerHTML = html;
            updateInfo();
            renderPagination();

            // Hilangkan highlight setelah 3 detik
            setTimeout(() => {
                allData.forEach(p => p._justScanned = false);
                document.querySelectorAll('.main-row').forEach(r => {
                    r.style.background = '';
                });
            }, 3000);
        }

        function renderDetailRows(details) {
            if (!details || details.length === 0) {
                return '<tr><td colspan="7" class="text-center text-muted">Tidak ada detail.</td></tr>';
            }

            let totalDetail = 0;
            let rows = details.map(d => {
                totalDetail += Number(d.subtotal);
                return `<tr>
            <td>${d.nomor_resi ?? '-'}</td>
            <td>${d.sku}</td>
            <td>${d.nama_barang}</td>
            <td>${d.stok}</td>
            <td>${d.qty}</td>
            <td>Rp ${formatRupiah(d.harga)}</td>
            <td>Rp ${formatRupiah(d.subtotal)}</td>
        </tr>`;
            }).join('');

            rows += `<tr style="background:#f1f1f1;font-weight:bold">
        <td colspan="6" class="text-right">Total Penjualan</td>
        <td>Rp ${formatRupiah(totalDetail)}</td>
    </tr>`;

            return rows;
        }

        // =====================================================
        // FILTER & SEARCH
        // =====================================================
        function applyFilters() {
            const keyword = (document.getElementById('searchTable').value || '').toLowerCase();
            const dateFrom = document.getElementById('dateFrom').value; // format: YYYY-MM-DD
            const dateTo = document.getElementById('dateTo').value;

            filteredData = allData.filter(pj => {
                const textMatch = [
                    pj.kode_penjualan, pj.nomor_resi, pj.nomor_pesanan,
                    pj.nomor_transaksi, pj.dropshipper, pj.keterangan, pj.scan_out
                ].filter(Boolean).some(v => v.toLowerCase().includes(keyword));

                // Normalisasi tanggal dari API → YYYY-MM-DD
                // Handle format: "2026-04-18", "2026-04-18T00:00:00.000000Z", "18-04-2026", dll
                let tanggalStr = pj.tanggal ?? '';

                // Kalau ada 'T' (ISO format), ambil bagian tanggalnya saja
                // Handle ISO (2026-04-28T00:00:00)
                if (tanggalStr.includes('T')) {
                    tanggalStr = tanggalStr.split('T')[0];
                }

                // Handle MySQL (2026-04-28 00:00:00)
                if (tanggalStr.includes(' ')) {
                    tanggalStr = tanggalStr.split(' ')[0];
                }

                // Kalau format DD-MM-YYYY atau DD/MM/YYYY, konversi ke YYYY-MM-DD
                const dmyMatch = tanggalStr.match(/^(\d{2})[-\/](\d{2})[-\/](\d{4})$/);
                if (dmyMatch) {
                    tanggalStr = `${dmyMatch[3]}-${dmyMatch[2]}-${dmyMatch[1]}`;
                }

                let dateMatch = true;
                if (dateFrom && dateFrom !== '') {
                    dateMatch = tanggalStr >= dateFrom;
                }
                if (dateTo && dateTo !== '') {
                    dateMatch = dateMatch && (tanggalStr <= dateTo);
                }

                return textMatch && dateMatch;
            });

            if (currentSortCol !== null) applySortOnFiltered();
            currentPage = 1;
            renderTable();
        }

        document.addEventListener('DOMContentLoaded', () => {
            focusScan();

            const today = new Date().toISOString().split('T')[0];

            document.getElementById('dateFrom').value = today;
            document.getElementById('dateTo').value = today;

            applyFilters(); // langsung filter ke hari ini
        });
        // =====================================================
        // SORT
        // =====================================================
        const SORT_KEYS = [
            null, 'no', 'kode_penjualan', 'nomor_resi', 'nomor_pesanan',
            'nomor_transaksi', 'dropshipper', 'tanggal', 'total_harga',
            'scan_out', 'is_draft', 'keterangan'
        ];

        function sortTable(colIndex) {
            if (currentSortCol === colIndex) {
                currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortCol = colIndex;
                currentSortDir = 'asc';
            }
            applySortOnFiltered();
            currentPage = 1;
            renderTable();
        }

        function applySortOnFiltered() {
            const key = SORT_KEYS[currentSortCol];
            if (!key) return;
            filteredData.sort((a, b) => {
                let av = a[key] ?? '';
                let bv = b[key] ?? '';
                if (!isNaN(av) && !isNaN(bv)) return currentSortDir === 'asc' ? av - bv : bv - av;
                av = String(av).toLowerCase();
                bv = String(bv).toLowerCase();
                return currentSortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
            });
        }

        // =====================================================
        // PAGINATION & INFO
        // =====================================================
        function renderPagination() {
            const ul = document.getElementById('pagination');
            const pageCount = Math.ceil(filteredData.length / rowsPerPage);
            ul.innerHTML = '';
            if (pageCount <= 1) return;

            const addPage = (label, page, disabled, active) => {
                const li = document.createElement('li');
                li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.innerHTML = label;
                if (!disabled) {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        currentPage = page;
                        renderTable();
                    });
                }
                li.appendChild(a);
                ul.appendChild(li);
            };

            addPage('<i class="feather icon-chevrons-left"></i>', 1, currentPage === 1, false);
            let startP = Math.max(1, currentPage - 2);
            let endP = Math.min(pageCount, startP + 4);
            if (endP - startP < 4) startP = Math.max(1, endP - 4);
            for (let i = startP; i <= endP; i++) addPage(i, i, false, i === currentPage);
            addPage('<i class="feather icon-chevrons-right"></i>', pageCount, currentPage === pageCount, false);
        }

        function updateInfo() {
            const el = document.getElementById('tableInfo');
            const start = filteredData.length === 0 ? 0 : (currentPage - 1) * rowsPerPage + 1;
            const end = Math.min(currentPage * rowsPerPage, filteredData.length);
            el.innerHTML =
                `Showing <strong>${start}</strong> to <strong>${end}</strong> of <strong>${filteredData.length}</strong> entries`;
        }

        // =====================================================
        // EXPAND DETAIL ROW — klik baris untuk buka/tutup
        // =====================================================
        document.getElementById('tableBody').addEventListener('click', function(e) {
            const row = e.target.closest('.main-row');
            if (!row) return;
            if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) return;

            const id = parseInt(row.getAttribute('data-id'));
            const detailRow = document.getElementById('detail-' + id);
            if (!detailRow) return;

            if (detailRow.style.display === 'table-row') {
                detailRow.style.display = 'none';
                openDetailIds.delete(id); // simpan state: tutup
            } else {
                detailRow.style.display = 'table-row';
                openDetailIds.add(id); // simpan state: buka
            }
        });

        // =====================================================
        // SCAN OUT
        // =====================================================
        const scanInput = document.getElementById('scanOutPenjualan');

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
                const res = await fetch(`${API_BASE}/scan-out`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        nomor_resi
                    }),
                });

                const json = await res.json();

                if (json.success) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Scan Out Berhasil',
                        text: json.message,
                    });
                    const found = allData.find(p => p.id === json.penjualan.id);
                    if (found) {
                        found.scan_out = 'done';
                        found._justScanned = true;
                    }
                    applyFilters();
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
        // SORTABLE HEADERS
        // =====================================================
        document.querySelectorAll('.sortable').forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(parseInt(this.getAttribute('data-column')));
            });
        });

        // =====================================================
        // ENTRIES SELECT
        // =====================================================
        document.getElementById('entriesSelect').addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });

        // =====================================================
        // SEARCH & DATE FILTER
        // =====================================================
        document.getElementById('searchTable').addEventListener('keyup', applyFilters);
        document.getElementById('dateFrom').addEventListener('change', applyFilters);
        document.getElementById('dateTo').addEventListener('change', applyFilters);

        // =====================================================
        // CHECK ALL
        // =====================================================
        document.getElementById('checkAll').addEventListener('click', function() {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
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

        fetchData();
        setInterval(() => fetchData(true), REFRESH_MS);
    </script>
@endsection
