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
            <h4 class="font-weight-bold py-3 mb-0">Penjualan Draft</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item active">Penjualan Draft</li>
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

                                    {{-- Row 1: Title --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-row sm-flex-col">
                                        <h6 class="card-header-title mb-0 text-warning">
                                            <i class="feather icon-file-text mr-2"></i> Data Penjualan Draft
                                            <small class="text-muted">Transaksi yang belum diselesaikan</small>
                                        </h6>
                                        <span class="badge badge-warning">{{ $penjualanDraft->total() }} transaksi</span>
                                    </div>

                                    {{-- Row 2: Keluarkan Draft --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-row sm-flex-col">
                                        <div class="text-muted small">
                                            <i class="feather icon-info mr-1"></i>
                                            Centang transaksi lalu klik
                                            <strong>Keluarkan Draft</strong> untuk memproses
                                            (normal &amp; konflik stok).
                                        </div>
                                        <div class="d-flex align-items-center" style="gap:8px">
                                            <span class="badge badge-primary d-none" id="selectedCountBadge">0 dipilih</span>
                                            @if(hasPermission('buat', 'penjualan'))
                                            <button type="button" id="btnReleaseDraft" class="btn btn-sm btn-success" disabled>
                                                <i class="feather icon-check-circle"></i>
                                                <span class="d-none d-sm-inline">Keluarkan Draft</span>
                                            </button>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Filter Form --}}
                                    <form method="GET" id="filterForm" class="d-flex flex-wrap align-items-center" style="gap:6px">

                                        {{-- Search fullwidth --}}
                                        <div class="input-group input-group-sm w-100">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="feather icon-search"></i></span>
                                            </div>
                                            <input type="text" name="search" class="form-control" id="searchTable"
                                                placeholder="Cari kode penjualan, nomor resi, no. pesanan, dropshipper..."
                                                value="{{ request('search') }}">
                                        </div>

                                        {{-- Dropdown filter + tombol --}}
                                        <div class="d-flex flex-wrap w-100" style="gap:6px">
                                            <select name="dropshipper" class="form-control form-control-sm auto-submit"
                                                style="min-width:140px;flex:2 1 160px">
                                                <option value="">-- Semua Dropshipper --</option>
                                                @foreach($dropshippers as $ds)
                                                    <option value="{{ $ds->nama }}" {{ request('dropshipper') == $ds->nama ? 'selected' : '' }}>{{ $ds->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="d-flex align-items-center" style="gap:6px">
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="feather icon-search"></i>
                                                <span class="d-none d-sm-inline">Cari</span>
                                            </button>
                                            @if (request('search') || request('dropshipper'))
                                                <a href="{{ route('penjualan.draft', ['per_page' => request('per_page', 10)]) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <i class="feather icon-x"></i>
                                                    <span class="d-none d-sm-inline">Reset</span>
                                                </a>
                                            @endif
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
                                                            <th style="width:40px">
                                                                <input type="checkbox" id="checkAll" title="Pilih semua">
                                                            </th>
                                                            <th class="text-center">No</th>
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
                                                            <th class="d-none d-sm-table-cell">Dibuat Oleh</th>
                                                            <th class="d-none d-sm-table-cell">
                                                                <a href="{{ sortUrl('created_at') }}" style="color:inherit">
                                                                    Dibuat
                                                                    {!! sortIcon('created_at') !!}
                                                                </a>
                                                            </th>
                                                            <th style="width:90px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableBody">
                                                        @forelse ($penjualanDraft as $pj)
                                                            <tr class="main-row" data-id="{{ $pj->id }}" style="cursor:pointer">
                                                                <td style="width:40px">
                                                                    <input type="checkbox" class="row-check" value="{{ $pj->id }}"
                                                                        title="Pilih transaksi">
                                                                </td>
                                                                <td class="text-center">{{ $penjualanDraft->firstItem() + $loop->index }}</td>
                                                                <td class="kode-click" style="color:#00499b;text-decoration:underline;white-space:nowrap">
                                                                    <strong>{{ $pj->kode_penjualan }}</strong>
                                                                </td>
                                                                <td style="white-space:nowrap">{{ $pj->nomor_resi ?? '-' }}</td>
                                                                <td class="d-none d-lg-table-cell">{{ $pj->nomor_pesanan ?? '-' }}</td>
                                                                <td class="d-none d-md-table-cell">{{ $pj->dropshipper->nama ?? '-' }}</td>
                                                                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y H:i') }}</td>
                                                                <td class="d-none d-md-table-cell" style="font-weight:bold;white-space:nowrap">Rp {{ number_format($pj->total_harga, 0, ',', '.') }}</td>
                                                                <td class="d-none d-sm-table-cell">{{ $pj->user->nama ?? '-' }}</td>
                                                                <td class="d-none d-sm-table-cell">
                                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($pj->created_at)->diffForHumans() }}</small>
                                                                </td>
                                                                <td style="white-space:nowrap">
                                                                    <a href="/transaksi/penjualan/edit/{{ $pj->id }}" class="btn btn-sm btn-warning">
                                                                        <i class="feather icon-edit"></i> Edit
                                                                    </a>
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
                                                                <td colspan="11" class="text-center text-muted py-4">
                                                                    <i class="feather icon-check-circle text-success mr-2"></i>
                                                                    Tidak ada penjualan draft
                                                                </td>
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
                                                    @if ($penjualanDraft->total() > 0)
                                                        Showing <strong>{{ $penjualanDraft->firstItem() }}</strong>
                                                        to <strong>{{ $penjualanDraft->lastItem() }}</strong>
                                                        of <strong>{{ $penjualanDraft->total() }}</strong> entries
                                                    @else
                                                        No entries found
                                                    @endif
                                                </div>
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0">
                                                        <li class="page-item {{ $penjualanDraft->onFirstPage() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $penjualanDraft->previousPageUrl() }}">
                                                                <i class="feather icon-chevron-left"></i>
                                                            </a>
                                                        </li>

                                                        @php
                                                            $currentPage = $penjualanDraft->currentPage();
                                                            $lastPage = $penjualanDraft->lastPage();
                                                            $start = max(1, $currentPage - 2);
                                                            $end = min($lastPage, $currentPage + 2);
                                                            if ($start <= 3) $end = min($lastPage, 5);
                                                            if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                                                        @endphp

                                                        @if ($lastPage > 7 && $start > 1)
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $penjualanDraft->url(1) }}">1</a>
                                                            </li>
                                                            @if ($start > 2)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                        @endif

                                                        @for ($i = $start; $i <= $end; $i++)
                                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                                <a class="page-link" href="{{ $penjualanDraft->url($i) }}">{{ $i }}</a>
                                                            </li>
                                                        @endfor

                                                        @if ($lastPage > 7 && $end < $lastPage)
                                                            @if ($end < $lastPage - 1)
                                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                                            @endif
                                                            <li class="page-item">
                                                                <a class="page-link" href="{{ $penjualanDraft->url($lastPage) }}">{{ $lastPage }}</a>
                                                            </li>
                                                        @endif

                                                        <li class="page-item {{ !$penjualanDraft->hasMorePages() ? 'disabled' : '' }}">
                                                            <a class="page-link" href="{{ $penjualanDraft->nextPageUrl() }}">
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

        {{-- MODAL: Preview Keluarkan Draft --}}
        <div class="modal fade" id="modalReleasePreview" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title"><i class="feather icon-check-circle mr-1"></i> Preview Keluarkan Draft</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="releasePreviewBody" style="max-height:65vh;overflow-y:auto">
                        {{-- Diisi oleh JS --}}
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-success px-3" id="btnConfirmRelease" disabled>
                            <i class="feather icon-check-circle"></i> Konfirmasi &amp; Keluarkan
                        </button>
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
        // AUTO-SUBMIT FILTER FORM
        // =====================================================
        document.querySelectorAll('.auto-submit').forEach(el => {
            el.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
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
                text: 'Data penjualan draft akan dihapus!',
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
        // CHECKBOX SELECTION + KELUARKAN DRAFT
        // =====================================================
        const checkAllEl = document.getElementById('checkAll');
        const btnReleaseDraft = document.getElementById('btnReleaseDraft');
        const selectedCountBadge = document.getElementById('selectedCountBadge');
        const btnConfirmRelease = document.getElementById('btnConfirmRelease');
        let previewData = null;

        function updateSelection() {
            const checks = Array.from(document.querySelectorAll('.row-check'));
            const selected = checks.filter(c => c.checked).map(c => c.value);
            const count = selected.length;

            checkAllEl.checked = checks.length > 0 && checks.every(c => c.checked);
            if (btnReleaseDraft) btnReleaseDraft.disabled = count === 0;
            selectedCountBadge.classList.toggle('d-none', count === 0);
            selectedCountBadge.textContent = count + ' dipilih';
        }

        checkAllEl.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
            updateSelection();
        });

        document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', updateSelection));

        function esc(str) {
            return String(str ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        function formatRp(n) {
            return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        }

        // ── PREVIEW ─────────────────────────────────────────
        if (btnReleaseDraft) {
            btnReleaseDraft.addEventListener('click', async function() {
                const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(c => parseInt(c.value, 10));

                if (ids.length === 0) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih minimal satu transaksi.' });
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Memproses...';

                try {
                    const res = await fetch('{{ route("penjualan.draft.release-preview") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids })
                    });

                    const json = await res.json();

                    if (!json.success) {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: json.message ?? 'Terjadi kesalahan.' });
                        return;
                    }

                    previewData = json;
                    renderPreview(json);
                    $('#modalReleasePreview').modal('show');
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
                } finally {
                    this.disabled = false;
                    this.innerHTML = '<i class="feather icon-check-circle"></i><span class="d-none d-sm-inline">Keluarkan Draft</span>';
                }
            });
        }

        function renderPreview(data) {
            let html = '';

            html += `
                <div class="alert alert-${data.konflik.length ? 'warning' : 'success'} py-2 mb-3">
                    <strong>${data.counts.normal}</strong> penjualan normal (stok cukup)
                    &bull; <strong>${data.counts.konflik}</strong> penjualan konflik (butuh penyesuaian stok)
                </div>`;

            // Barang unik yang stoknya akan ditambah
            if (data.barang_update.length) {
                // html += `
                //     <div class="mb-3">
                //         <h6 class="mb-2">Stok berikut akan ditambahkan:</h6>
                //         <table class="table table-sm table-bordered mb-0" style="min-width:480px">
                //             <thead class="thead-light">
                //                 <tr><th>SKU</th><th>Nama Barang</th><th>Qty Pesanan</th><th>Stok Saat Ini</th><th>Sumber</th></tr>
                //             </thead>
                //             <tbody>`;

                // data.barang_update.forEach(b => {
                //     html += `
                //         <tr>
                //             <td>${esc(b.sku)}</td>
                //             <td>${esc(b.nama)}</td>
                //             <td>${esc(b.qty)}</td>
                //             <td>${esc(b.stok)}</td>
                //             <td class="small">${esc(b.kode)}</td>
                //         </tr>`;
                // });

                // html += `</tbody></table></div>`;
            }

            // Penjualan NORMAL
            if (data.normal.length) {
                html += `
                    <div class="mb-3">
                        <h6 class="mb-2 text-success"><i class="feather icon-check-circle mr-1"></i> Penjualan Normal
                            <span class="badge badge-success">${data.normal.length}</span></h6>
                        <div class="d-flex flex-wrap" style="gap:6px">`;

                data.normal.forEach(s => {
                    html += `<span class="badge badge-success border" style="font-size:.8rem">${esc(s.kode)}</span>`;
                });

                html += `</div></div>`;
            }

            // Penjualan KONFLIK, grouped per sale
            if (data.konflik.length) {
                html += `
                    <h6 class="mb-2 text-warning"><i class="feather icon-alert-triangle mr-1"></i> Penyesuaian Stok (Konflik) <span class="badge badge-warning">${data.konflik.length}</span></h6>`;

                data.konflik.forEach(s => {
                    html += `
                        <div class="card mb-2 border-warning"  style="z-index:99999 !important">
                            <div class="card-header py-2 d-flex flex-wrap justify-content-between align-items-center" style="background:#fffbf0">
                                <div class="small font-weight-bold" style="color:#00499b">${esc(s.kode)}
                                    <span class="text-muted"> &bull; ${esc(s.tanggal)}</span>
                                    <span class="text-muted d-none d-md-inline"> &bull; ${esc(s.dropshipper)}</span>
                                </div>
                                <span class="text-muted small">${formatRp(s.total_harga)}</span>
                            </div>
                            <div class="card-body py-2" style="overflow-x:auto">
                                <table class="table table-sm table-bordered mb-1" style="min-width:520px">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>SKU</th><th>Nama Barang</th>
                                            <th style="width:110px">Qty Pesanan</th>
                                            <th style="width:110px">Stok Saat Ini</th>
                                            <th style="width:130px">Stok Terbaru</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                    s.items.forEach(it => {
                        if (!it.conflict) {
                            html += `
                                <tr class="text-muted" style="opacity:.7">
                                    <td>${esc(it.sku)}</td><td>${esc(it.nama)}</td>
                                    <td>${esc(it.qty)}</td><td>${esc(it.stok)}</td>
                                    <td class="small"><i class="feather icon-info"></i> Cukup</td>
                                </tr>`;
                            return;
                        }

                        html += `
                            <tr ${it.input_utama ? '' : 'class="text-muted" style="opacity:.75"'}>
                                <td>${esc(it.sku)}</td>
                                <td>${esc(it.nama)}</td>
                                <td><input type="number" min="1" value="${esc(it.qty)}"
                                    ${it.input_utama ? '' : 'readonly disabled'}
                                    class="form-control form-control-sm release-qty"
                                    data-pid="${esc(s.id)}" data-bid="${esc(it.barang_id)}"></td>
                                <td><span class="badge badge-light border">${esc(it.stok)}</span></td>
                                <td>${it.input_utama
                                        ? `<input type="number" min="1" placeholder="= qty"
                                            class="form-control form-control-sm release-jstok"
                                            data-pid="${esc(s.id)}" data-bid="${esc(it.barang_id)}"
                                            data-qty="${esc(it.qty)}">`
                                        : `<span class="small text-muted">Dihitung di transaksi lain</span>`}
                                </td>
                            </tr>`;
                    });

                    html += `</tbody></table>
                                <div class="small text-muted">
                                    <i class="feather icon-info mr-1"></i>
                                    Isi <strong>Stok Terbaru</strong> (min. sama dengan Qty) untuk barang konflik.
                                </div>
                            </div>
                        </div>`;
                });
            }

            document.getElementById('releasePreviewBody').innerHTML = html;
            btnConfirmRelease.disabled = false;
        }

        // ── KONFIRMASI / PROSES ──────────────────────────────
        btnConfirmRelease.addEventListener('click', async function() {
            if (!previewData) return;

            const items = [];
            let invalid = false;

            document.querySelectorAll('.release-jstok').forEach(el => {
                const qty = parseInt(el.dataset.qty || '0', 10);
                const val = parseInt(el.value || '0', 10);

                if (!el.value.trim() || isNaN(val) || val < qty) {
                    invalid = true;
                    el.classList.add('is-invalid');
                } else {
                    el.classList.remove('is-invalid');
                }

                items.push({
                    penjualan_id: parseInt(el.dataset.pid, 10),
                    barang_id: parseInt(el.dataset.bid, 10),
                    qty: parseInt(el.closest('tr').querySelector('.release-qty')?.value || qty, 10),
                    jumlah_stok: val
                });
            });

            if (invalid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Lengkapi Stok Terbaru (minimal senilai qty pesanan) untuk semua barang konflik.'
                });
                return;
            }

            const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(c => parseInt(c.value, 10));

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Memproses...';

            try {
                const res = await fetch('{{ route("penjualan.draft.release-process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids, items })
                });

                const json = await res.json();

                if (json.success) {
                    $('#modalReleasePreview').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        html: `<strong>${json.normal_clear}</strong> penjualan normal &amp; <strong>${json.konflik_clear}</strong> penjualan konflik dikeluarkan dari draft.`,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: json.message ?? 'Terjadi kesalahan.' });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="feather icon-check-circle"></i> Konfirmasi &amp; Keluarkan';
            }
        });

        $('#modalReleasePreview').on('hidden.bs.modal', function() {
            btnConfirmRelease.disabled = true;
        });
    </script>
@endsection