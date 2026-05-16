@extends('layouts.main')

@section('style')
<style>
    #modalHpp {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1054 !important;
    }
</style>
@endsection
@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Barang</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Barang</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12">

                            @if (session('success'))
                                <div class="card mb-4 border-success alert-dismissible-auto">
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

                                {{-- ===== CARD HEADER ===== --}}
                                <div class="card-header" style="border: none !important">

                                    {{-- Baris 1: Judul + Tombol Aksi --}}
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <h6 class="card-header-title mb-0">
                                            <i class="feather icon-truck mr-2"></i> Data Barang
                                        </h6>

                                        <div class="d-flex flex-wrap align-items-center" style="gap: 5px">
                                            <button id="btnBulkHpp" class="btn btn-sm btn-warning text-white d-none"
                                                onclick="openHppModal()">
                                                <i class="feather icon-percent"></i>
                                                Bentuk Harga HPP (<span id="selectedCountHpp">0</span>)
                                            </button>
                                            <button id="btnBulkDelete" class="btn btn-sm btn-danger d-none"
                                                onclick="bulkDelete()">
                                                <i class="feather icon-trash"></i>
                                                Hapus (<span id="selectedCount">0</span>)
                                            </button>

                                            @if (hasPermission('tambah', 'barang'))
                                                <a href="#" onclick="showImportModal()" class="btn btn-sm text-white"
                                                    style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;">
                                                    <i class="feather icon-download"></i>
                                                    <span class="d-none d-sm-inline">Import Barang</span>
                                                </a>
                                                <a href="{{ route('barang.create') }}" class="btn btn-sm text-white"
                                                    style="background: linear-gradient(135deg, #ff8a00, #ff5b00); border: none;">
                                                    <i class="feather icon-plus"></i>
                                                    <span class="d-none d-sm-inline">Tambah Barang</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Baris 2: Search + Per Page --}}
                                    <form method="GET" action="{{ route('barang.index') }}" id="filterForm">
                                        <div class="d-flex flex-wrap align-items-center gap-2">

                                            {{-- Search --}}
                                            <div class="flex-grow-1" style="min-width: 160px; max-width: 100%;">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="feather icon-search"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" name="search"
                                                        class="form-control form-control-sm" placeholder="Cari barang..."
                                                        value="{{ request('search') }}">
                                                </div>
                                            </div>

                                            {{-- Per Page --}}
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted small mr-2">Show</span>
                                                <select name="per_page" class="form-control form-control-sm"
                                                    style="width: 75px" onchange="this.form.submit()">
                                                    @foreach ([10, 25, 50, 100] as $n)
                                                        <option value="{{ $n }}"
                                                            {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                                                            {{ $n }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-muted small ml-2">entries</span>
                                            </div>

                                            {{-- Tombol Search --}}
                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                <i class="feather icon-search"></i>
                                                <span class="d-none d-sm-inline">Cari</span>
                                            </button>

                                            @if (request('search'))
                                                <a href="{{ route('barang.index', ['per_page' => request('per_page', 10)]) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    <i class="feather icon-x"></i>
                                                    <span class="d-none d-sm-inline">Reset</span>
                                                </a>
                                            @endif

                                        </div>
                                    </form>
                                </div>

                                {{-- ===== TABLE ===== --}}
                                <div class="nav-tabs-top">
                                    <div class="tab-content d-flex justify-content-center" style="width: 100%">
                                        <div class="tab-pane fade show active pb-3" id="sale-stats" style="width: 95%">

                                            {{-- Mobile: Card View --}}
                                            <div class="d-block d-md-none px-3">
                                                @forelse ($barang as $index => $sup)
                                                    <div class="card mb-3 border" data-id="{{ $sup->id }}">
                                                        <div class="card-body p-3">

                                                            <div
                                                                class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <strong>{{ \Illuminate\Support\Str::limit($sup->nama_barang, 30, '...') }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ $sup->sku }}</small>
                                                                </div>
                                                                <input type="checkbox" class="row-check mt-1"
                                                                    data-id="{{ $sup->id }}">
                                                            </div>

                                                            <div class="row text-sm mb-2" style="font-size: 13px">
                                                                <div class="col-6">
                                                                    <span class="text-muted">Satuan:</span>
                                                                    {{ $sup->satuan->nama_satuan }}
                                                                </div>
                                                                <div class="col-6">
                                                                    <span class="text-muted">Stok:</span>
                                                                    <strong>{{ $sup->stok->jumlah_stok }}</strong>
                                                                </div>
                                                                @if (Auth::guard('pengguna')->user()->role->nama_role === 'super_admin')
                                                                    <div class="col-6 mt-1">
                                                                        <span class="text-muted">Harga HPP:</span>
                                                                        Rp {{ number_format($sup->harga_1, 0, ',', '.') }}
                                                                    </div>
                                                                @endif
                                                                <div class="col-6 mt-1">
                                                                    <span class="text-muted">Harga Reseller:</span>
                                                                    Rp {{ number_format($sup->harga_2, 0, ',', '.') }}
                                                                </div>
                                                            </div>

                                                            @if ($sup->keterangan)
                                                                <p class="text-muted mb-2" style="font-size: 12px">
                                                                    {{ $sup->keterangan }}
                                                                </p>
                                                            @endif

                                                            <div class="d-flex flex-wrap gap-1">
                                                                <a href="{{ route('barang.barcode.download', $sup->id) }}"
                                                                    class="btn btn-sm btn-dark" target="_blank">
                                                                    <i class="feather icon-download"></i> Barcode
                                                                </a>
                                                                @if (hasPermission('edit', 'barang'))
                                                                    <a href="{{ route('barang.edit', $sup->id) }}"
                                                                        class="btn btn-sm btn-info">
                                                                        <i class="feather icon-edit"></i> Edit
                                                                    </a>
                                                                @endif
                                                                @if (hasPermission('hapus', 'barang'))
                                                                    <button type="button"
                                                                        onclick="confirmDelete({{ $sup->id }})"
                                                                        class="btn btn-sm btn-danger">
                                                                        <i class="feather icon-trash"></i>
                                                                    </button>
                                                                    <form id="delete-form-{{ $sup->id }}"
                                                                        action="{{ route('barang.destroy', $sup->id) }}"
                                                                        method="POST" style="display:none">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                @endif
                                                            </div>

                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="text-center text-muted py-5">
                                                        <i class="feather icon-inbox" style="font-size: 2rem"></i>
                                                        <p class="mt-2">Tidak ada data barang.</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            {{-- Desktop: Table View --}}
                                            <div class="d-none d-md-block" style="overflow-x: auto">
                                                <table class="table table-modern table-hover mb-0" id="table">
                                                    <thead>
                                                        <tr>
                                                            <th class="checkbox-col" style="width: 40px">
                                                                <input type="checkbox" id="checkAll">
                                                            </th>
                                                            <th style="width: 50px">No</th>
                                                            <th>SKU</th>
                                                            <th>Nama Barang</th>
                                                            <th>Kategori</th>
                                                            <th>Satuan</th>
                                                            @if (Auth::guard('pengguna')->user()->role->nama_role === 'super_admin')
                                                                <th>Harga HPP</th>
                                                            @endif
                                                            <th>Harga Reseller</th>
                                                            <th>Stok</th>
                                                            <th>Keterangan</th>
                                                            <th width="160">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($barang as $index => $sup)
                                                            <tr data-id="{{ $sup->id }}">
                                                                <td class="checkbox-col">
                                                                    <input type="checkbox" class="row-check">
                                                                </td>
                                                                <td>{{ $barang->firstItem() + $index }}</td>
                                                                <td>
                                                                    <div class="d-flex flex-column align-items-center"
                                                                        style="padding-block: 12px">
                                                                        {!! DNS2D::getBarcodeHTML($sup->sku, 'QRCODE', 3, 3) !!}
                                                                        <small class="mt-1"
                                                                            style="font-size:13px; letter-spacing:1px">
                                                                            {{ $sup->sku }}
                                                                        </small>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <strong>{{ \Illuminate\Support\Str::limit($sup->nama_barang, 25, '...') }}</strong>
                                                                </td>
                                                                <td>{{ $sup->kategori ?? '-' }}</td>
                                                                <td>{{ $sup->satuan->nama_satuan }}</td>
                                                                @if (Auth::guard('pengguna')->user()->role->nama_role === 'super_admin')
                                                                    <td>Rp {{ number_format($sup->harga_1, 0, ',', '.') }}
                                                                    </td>
                                                                @endif
                                                                <td>Rp {{ number_format($sup->harga_2, 0, ',', '.') }}</td>
                                                                <td>{{ $sup->stok->jumlah_stok }}</td>
                                                                <td>{{ $sup->keterangan ?? '-' }}</td>
                                                                <td>
                                                                    <a href="{{ route('barang.barcode.download', $sup->id) }}"
                                                                        class="btn btn-sm btn-dark action-btn mb-1"
                                                                        target="_blank">
                                                                        <i class="feather icon-download"></i>
                                                                        <span>Barcode</span>
                                                                    </a>
                                                                    @if (hasPermission('edit', 'barang'))
                                                                        <a href="{{ route('barang.edit', $sup->id) }}"
                                                                            class="btn btn-sm btn-info action-btn mb-1">
                                                                            <i class="feather icon-edit"></i>
                                                                        </a>
                                                                    @endif
                                                                    @if (hasPermission('hapus', 'barang'))
                                                                        <button type="button"
                                                                            onclick="confirmDelete({{ $sup->id }})"
                                                                            class="btn btn-sm btn-danger action-btn mb-1">
                                                                            <i class="feather icon-trash"></i>
                                                                        </button>
                                                                        <form id="delete-form-{{ $sup->id }}"
                                                                            action="{{ route('barang.destroy', $sup->id) }}"
                                                                            method="POST" style="display:inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                        </form>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="10" class="text-center text-muted py-5">
                                                                    <i class="feather icon-inbox"
                                                                        style="font-size: 2rem"></i>
                                                                    <p class="mt-2 mb-0">Tidak ada data barang.</p>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- ===== FOOTER: Info + Pagination ===== --}}
                                            <div
                                                class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">

                                                <div class="text-muted small">
                                                    @if ($barang->total() > 0)
                                                        Showing <strong>{{ $barang->firstItem() }}</strong>
                                                        to <strong>{{ $barang->lastItem() }}</strong>
                                                        of <strong>{{ $barang->total() }}</strong> entries
                                                        @if (request('search'))
                                                            <span class="text-primary">(filtered)</span>
                                                        @endif
                                                    @else
                                                        No entries found
                                                    @endif
                                                </div>

                                                @if ($barang->hasPages())
                                                    <ul class="pagination pagination-sm mb-0 flex-wrap">

                                                        {{-- Prev --}}
                                                        <li
                                                            class="page-item {{ $barang->onFirstPage() ? 'disabled' : '' }}">
                                                            <a class="page-link"
                                                                href="{{ $barang->previousPageUrl() . '&per_page=' . request('per_page', 10) . (request('search') ? '&search=' . request('search') : '') ?? '#' }}">
                                                                <i class="feather icon-chevron-left"></i>
                                                            </a>
                                                        </li>

                                                        @php
                                                            $currentPage = $barang->currentPage();
                                                            $lastPage = $barang->lastPage();

                                                            // Tentukan range halaman yang ditampilkan
                                                            if ($lastPage <= 7) {
                                                                $start = 1;
                                                                $end = $lastPage;
                                                                $showStartEllipsis = false;
                                                                $showEndEllipsis = false;
                                                            } else {
                                                                $showStartEllipsis = $currentPage > 3;
                                                                $showEndEllipsis = $currentPage < $lastPage - 2;

                                                                $start = max(2, $currentPage - 1);
                                                                $end = min($lastPage - 1, $currentPage + 1);

                                                                if ($currentPage <= 3) {
                                                                    $end = min($lastPage - 1, 4);
                                                                }
                                                                if ($currentPage >= $lastPage - 2) {
                                                                    $start = max(2, $lastPage - 3);
                                                                }
                                                            }
                                                        @endphp

                                                        @if ($lastPage > 7)
                                                            {{-- Halaman 1 selalu tampil --}}
                                                            <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                                                                <a class="page-link"
                                                                    href="{{ $barang->url(1) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                                    1
                                                                </a>
                                                            </li>

                                                            @if ($showStartEllipsis)
                                                                <li class="page-item disabled">
                                                                    <span class="page-link">...</span>
                                                                </li>
                                                            @endif

                                                            @for ($i = $start; $i <= $end; $i++)
                                                                <li
                                                                    class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                                    <a class="page-link"
                                                                        href="{{ $barang->url($i) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                                        {{ $i }}
                                                                    </a>
                                                                </li>
                                                            @endfor

                                                            @if ($showEndEllipsis)
                                                                <li class="page-item disabled">
                                                                    <span class="page-link">...</span>
                                                                </li>
                                                            @endif

                                                            {{-- Halaman terakhir selalu tampil --}}
                                                            <li
                                                                class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                                                                <a class="page-link"
                                                                    href="{{ $barang->url($lastPage) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                                    {{ $lastPage }}
                                                                </a>
                                                            </li>
                                                        @else
                                                            {{-- Tampilkan semua jika halaman ≤ 7 --}}
                                                            @for ($i = $start; $i <= $end; $i++)
                                                                <li
                                                                    class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                                    <a class="page-link"
                                                                        href="{{ $barang->url($i) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                                        {{ $i }}
                                                                    </a>
                                                                </li>
                                                            @endfor
                                                        @endif

                                                        {{-- Next --}}
                                                        <li
                                                            class="page-item {{ !$barang->hasMorePages() ? 'disabled' : '' }}">
                                                            <a class="page-link"
                                                                href="{{ $barang->nextPageUrl() . '&per_page=' . request('per_page', 10) . (request('search') ? '&search=' . request('search') : '') ?? '#' }}">
                                                                <i class="feather icon-chevron-right"></i>
                                                            </a>
                                                        </li>

                                                    </ul>
                                                @endif

                                            </div>
                                            {{-- END FOOTER --}}

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Modal Pembentukan Harga HPP --}}
        <div class="modal fade" id="modalHpp" tabindex="-1" role="dialog" aria-labelledby="modalHppLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHppLabel">
                            <i class="feather icon-percent mr-2 text-warning"></i> Pembentukan Harga HPP
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                        {{-- Info jumlah barang --}}
                        <div class="alert bg-warning text-white d-flex align-items-center mb-3" style="gap: 10px;">
                            <i class="feather icon-info" style="font-size: 1.2rem;"></i>
                            <div>
                                Harga HPP akan diterapkan ke
                                <strong id="hppSelectedCount">0</strong> barang yang dipilih.
                            </div>
                        </div>

                        {{-- Repeater detail biaya --}}
                        <div id="hppItemsWrapper">
                            {{-- Row pertama otomatis ada --}}
                            <div class="hpp-item d-flex align-items-center mb-2" style="gap: 8px;">
                                <input type="text" class="form-control hpp-nama"
                                    placeholder="Nama biaya (cth: Bahan baku)">
                                <div class="input-group" style="max-width: 180px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" class="form-control hpp-harga" placeholder="0" min="0"
                                        oninput="recalcHpp()">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-hpp"
                                    onclick="removeHppRow(this)" style="flex-shrink:0">
                                    <i class="feather icon-x"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Tombol tambah baris --}}
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1 mb-3" onclick="addHppRow()">
                            <i class="feather icon-plus mr-1"></i> Tambah Detail Biaya HPP
                        </button>

                        {{-- Infografis total --}}
                        <div class="card border-warning mt-2">
                            <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Total HPP yang akan disimpan ke</span>
                                    <strong class="text-warning"> harga hpp</strong>
                                </div>
                                <div>
                                    <span class="text-muted small mr-1">Rp</span>
                                    <strong id="hppTotalDisplay" style="font-size: 1.3rem; color: #e67e00;">0</strong>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning text-white" id="btnSimpanHpp">
                            <i class="feather icon-save mr-1"></i> Simpan ke Harga HPP
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
        // ALERT AUTO DISMISS
        // =====================================================
        setTimeout(function() {
            const alertCard = document.querySelector('.alert-dismissible-auto');
            if (alertCard) {
                alertCard.style.transition = '0.5s';
                alertCard.style.opacity = '0';
                setTimeout(() => alertCard.remove(), 500);
            }
        }, 4000);

        // =====================================================
        // CHECK ALL (desktop table)
        // =====================================================
        const checkAllEl = document.getElementById('checkAll');
        if (checkAllEl) {
            checkAllEl.addEventListener('click', function() {
                document.querySelectorAll('#table tbody .row-check')
                    .forEach(cb => cb.checked = this.checked);
                updateBulkBar();
            });
        }

        // =====================================================
        // BULK DELETE BAR
        // =====================================================
        function updateBulkBar() {
            const checked = document.querySelectorAll('.row-check:checked');
            const btn = document.getElementById('btnBulkDelete');
            const btnHpp = document.getElementById('btnBulkHpp');
            const count = checked.length;

            document.getElementById('selectedCount').textContent = count;
            document.getElementById('selectedCountHpp').textContent = count;

            if (count > 0) {
                btn.classList.remove('d-none');
                btnHpp.classList.remove('d-none');
            } else {
                btn.classList.add('d-none');
                btnHpp.classList.add('d-none');
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('row-check')) updateBulkBar();
        });

        async function bulkDelete() {
            // Ambil IDs dari semua checkbox yang tercentang
            const checked = document.querySelectorAll('.row-check:checked');
            const ids = Array.from(checked).map(cb => {
                const tr = cb.closest('tr');
                const card = cb.closest('[data-id]');
                return tr ? tr.getAttribute('data-id') : card?.getAttribute('data-id');
            }).filter(Boolean);

            if (ids.length === 0) return;

            const result = await Swal.fire({
                title: `Hapus ${ids.length} barang?`,
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus semua!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            const btn = document.getElementById('btnBulkDelete');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menghapus...';

            try {
                const res = await fetch('{{ route('barang.bulk-delete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids
                    })
                });

                const json = await res.json();

                if (json.success) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: json.message,
                            timer: 1500
                        })
                        .then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: json.message ?? 'Terjadi kesalahan.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
                console.error(e);
            } finally {
                btn.disabled = false;
                btn.innerHTML =
                    `<i class="feather icon-trash"></i> Hapus (<span id="selectedCount">${ids.length}</span>)`;
            }
        }

        // =====================================================
        // CONFIRM DELETE (single)
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
        // IMPORT MODAL
        // =====================================================
        function showImportModal() {
            Swal.fire({
                title: '<strong>Import Barang</strong>',
                icon: 'info',
                html: `
            <div class="text-left">
                <a href="/api/barang/import/template" class="btn btn-outline-secondary btn-block mb-4">
                    <i class="feather icon-download"></i> Download Template
                </a>
                <div class="form-group mb-3">
                    <label class="font-weight-bold">Pilih File</label>
                    <input type="file" id="import_file" class="form-control" accept=".xlsx,.xls,.csv">
                    <small class="text-muted">Format: .xlsx, .xls, .csv</small>
                </div>
            </div>
        `,
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="feather icon-upload"></i> Import',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#667eea',
                focusConfirm: false,
                preConfirm: async () => {
                    const file = document.getElementById('import_file').files[0];

                    if (!file) {
                        Swal.showValidationMessage('Pilih file terlebih dahulu!');
                        return false;
                    }

                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!['xlsx', 'xls', 'csv'].includes(ext)) {
                        Swal.showValidationMessage(
                        'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
                        return false;
                    }

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('role', "{{ Auth::guard('pengguna')->user()->role->nama_role }}");
                    formData.append('_token', '{{ csrf_token() }}');

                    Swal.showLoading();

                    try {
                        const response = await fetch('/api/barang/import', {
                            method: 'POST',
                            body: formData,
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            Swal.showValidationMessage(result.message ?? 'Import gagal.');
                            return false;
                        }

                        return result;

                    } catch (err) {
                        Swal.showValidationMessage('Terjadi kesalahan: ' + err.message);
                        return false;
                    }
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    const {
                        imported,
                        skipped,
                        details
                    } = result.value;

                    let detailHtml = '';
                    if (details && details.length > 0) {
                        const rows = details.map(d => `
                    <tr>
                        <td class="text-left px-2"><code>${d.sku}</code></td>
                        <td class="text-left px-2 text-danger">${d.alasan}</td>
                    </tr>
                `).join('');

                        detailHtml = `
                    <div class="mt-3 text-left">
                        <p class="font-weight-bold mb-1">Detail baris dilewati:</p>
                        <div style="max-height:200px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left px-2">SKU</th>
                                        <th class="text-left px-2">Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                    </div>
                `;
                    }

                    Swal.fire({
                        icon: skipped > 0 ? 'warning' : 'success',
                        title: 'Import Selesai!',
                        html: `
                    <p class="mb-2">✅ Berhasil diimport: <strong>${imported}</strong> barang</p>
                    ${skipped > 0 ? `<p class="mb-2 text-warning">⚠️ Dilewati: <strong>${skipped}</strong> baris</p>` : ''}
                    ${detailHtml}
                `,
                        width: details?.length > 0 ? '600px' : '400px',
                    }).then(() => location.reload());
                }
            });
        }
        // =====================================================
        // HPP MODAL
        // =====================================================
        let hppSelectedIds = [];

        function openHppModal() {
            const checked = document.querySelectorAll('.row-check:checked');
            hppSelectedIds = Array.from(checked).map(cb => {
                const tr = cb.closest('tr');
                const card = cb.closest('[data-id]');
                return tr ? tr.getAttribute('data-id') : card?.getAttribute('data-id');
            }).filter(Boolean);

            if (hppSelectedIds.length === 0) return;

            document.getElementById('hppSelectedCount').textContent = hppSelectedIds.length;

            // Reset repeater ke 1 baris kosong
            document.getElementById('hppItemsWrapper').innerHTML = '';
            addHppRow();
            recalcHpp();

            $('#modalHpp').modal('show');
        }

        function addHppRow() {
            const wrapper = document.getElementById('hppItemsWrapper');
            const div = document.createElement('div');
            div.className = 'hpp-item d-flex align-items-center mb-2';
            div.style.gap = '8px';
            div.innerHTML = `
        <input type="text"
            class="form-control hpp-nama"
            placeholder="Nama biaya (cth: Bahan baku)">
        <div class="input-group" style="max-width: 180px;">
            <div class="input-group-prepend">
                <span class="input-group-text">Rp</span>
            </div>
            <input type="number"
                class="form-control hpp-harga"
                placeholder="0"
                min="0"
                oninput="recalcHpp()">
        </div>
        <button type="button"
            class="btn btn-sm btn-outline-danger btn-remove-hpp"
            onclick="removeHppRow(this)"
            style="flex-shrink:0">
            <i class="feather icon-x"></i>
        </button>
    `;
            wrapper.appendChild(div);
            // Focus ke input nama yang baru
            div.querySelector('.hpp-nama').focus();
        }

        function removeHppRow(btn) {
            const rows = document.querySelectorAll('.hpp-item');
            if (rows.length <= 1) {
                // Kalau tinggal 1, kosongkan saja jangan dihapus
                rows[0].querySelector('.hpp-nama').value = '';
                rows[0].querySelector('.hpp-harga').value = '';
                recalcHpp();
                return;
            }
            btn.closest('.hpp-item').remove();
            recalcHpp();
        }

        function recalcHpp() {
            const inputs = document.querySelectorAll('.hpp-harga');
            let total = 0;
            inputs.forEach(inp => {
                const val = parseFloat(inp.value) || 0;
                total += val;
            });
            document.getElementById('hppTotalDisplay').textContent =
                total.toLocaleString('id-ID');
        }

        document.getElementById('btnSimpanHpp').addEventListener('click', async function() {
            const inputs = document.querySelectorAll('.hpp-harga');
            let total = 0;
            inputs.forEach(inp => total += parseFloat(inp.value) || 0);

            if (total <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Total HPP harus lebih dari 0.'
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi',
                html: `Simpan <strong>Rp ${total.toLocaleString('id-ID')}</strong> sebagai Harga HPP untuk <strong>${hppSelectedIds.length} barang</strong>?`,
                showCancelButton: true,
                confirmButtonColor: '#f6a821',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!',
                cancelButtonText: 'Batal'
            });

            if (!confirm.isConfirmed) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...';

            try {
                const res = await fetch('{{ route('barang.bulk-update-hpp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ids: hppSelectedIds,
                        harga_hpp: total
                    })
                });

                const json = await res.json();

                if (json.success) {
                    $('#modalHpp').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: json.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: json.message ?? 'Terjadi kesalahan.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="feather icon-save mr-1"></i> Simpan ke Harga HPP';
            }
        });
    </script>
@endsection
