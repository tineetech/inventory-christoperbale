@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Brand</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Brand</li>
                </ol>
            </div>

            @if (session('success'))
                <div class="card mb-4 border-success alert-dismissible-auto">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 text-success"><i class="feather icon-check-circle"></i> Success</h5>
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

            <div class="card mb-4">
                <div class="card-header" style="border: none !important">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="card-header-title mb-0">
                            <i class="feather icon-tag mr-2"></i> Data Brand
                        </h6>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 5px">
                            @if (hasPermission('tambah', 'barang'))
                                <a href="{{ route('brand.create') }}" class="btn btn-sm text-white"
                                    style="background: linear-gradient(135deg, #ff8a00, #ff5b00); border: none;">
                                    <i class="feather icon-plus"></i>
                                    <span class="d-none d-sm-inline">Tambah Brand</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <form method="GET" action="{{ route('brand.index') }}" id="filterForm">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="flex-grow-1" style="min-width: 160px; max-width: 100%;">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="feather icon-search"></i></span>
                                    </div>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Cari brand..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted small mr-2">Show</span>
                                <select name="per_page" class="form-control form-control-sm"
                                    style="width: 75px" onchange="this.form.submit()">
                                    @foreach ([10, 25, 50, 100] as $n)
                                        <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                                <span class="text-muted small ml-2">entries</span>
                            </div>
                            <button type="submit" class="btn btn-sm btn-secondary">
                                <i class="feather icon-search"></i>
                                <span class="d-none d-sm-inline">Cari</span>
                            </button>
                            @if (request('search'))
                                <a href="{{ route('brand.index', ['per_page' => request('per_page', 10)]) }}"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="feather icon-x"></i>
                                    <span class="d-none d-sm-inline">Reset</span>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="nav-tabs-top">
                    <div class="tab-content d-flex justify-content-center" style="width: 100%">
                        <div class="tab-pane fade show active pb-3" style="width: 95%">
                            <div class="d-none d-md-block" style="overflow-x: auto">
                                <table class="table table-modern table-hover mb-0" id="table">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px">No</th>
                                            <th>Nama Brand</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Dibuat</th>
                                            <th width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($brand as $index => $b)
                                            <tr>
                                                <td>{{ $brand->firstItem() + $index }}</td>
                                                <td><strong>{{ $b->nama_brand }}</strong></td>
                                                <td>{{ Str::limit($b->deskripsi_brand, 50) ?? '-' }}</td>
                                                <td>
                                                    @if ($b->status_brand === 'aktif')
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>{{ $b->createdBy->nama ?? '-' }}</td>
                                                <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @if (hasPermission('edit', 'barang'))
                                                        <a href="{{ route('brand.edit', $b->id) }}" class="btn btn-sm btn-info action-btn mb-1">
                                                            <i class="feather icon-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if (hasPermission('hapus', 'barang'))
                                                        <button type="button" onclick="confirmDelete({{ $b->id }})"
                                                            class="btn btn-sm btn-danger action-btn mb-1">
                                                            <i class="feather icon-trash"></i>
                                                        </button>
                                                        <form id="delete-form-{{ $b->id }}"
                                                            action="{{ route('brand.destroy', $b->id) }}"
                                                            method="POST" style="display:none">
                                                            @csrf @method('DELETE')
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-5">
                                                    <i class="feather icon-inbox" style="font-size: 2rem"></i>
                                                    <p class="mt-2 mb-0">Tidak ada data brand.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">
                                <div class="text-muted small">
                                    @if ($brand->total() > 0)
                                        Showing <strong>{{ $brand->firstItem() }}</strong> to <strong>{{ $brand->lastItem() }}</strong>
                                        of <strong>{{ $brand->total() }}</strong> entries
                                        @if (request('search'))<span class="text-primary">(filtered)</span>@endif
                                    @else
                                        No entries found
                                    @endif
                                </div>
                                @if ($brand->hasPages())
                                    <ul class="pagination pagination-sm mb-0 flex-wrap">
                                        <li class="page-item {{ $brand->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $brand->previousPageUrl() . '&per_page=' . request('per_page', 10) . (request('search') ? '&search=' . request('search') : '') ?? '#' }}">
                                                <i class="feather icon-chevron-left"></i>
                                            </a>
                                        </li>
                                        @php
                                            $currentPage = $brand->currentPage();
                                            $lastPage = $brand->lastPage();
                                            if ($lastPage <= 7) {
                                                $start = 1; $end = $lastPage;
                                                $showStartEllipsis = false; $showEndEllipsis = false;
                                            } else {
                                                $showStartEllipsis = $currentPage > 3;
                                                $showEndEllipsis = $currentPage < $lastPage - 2;
                                                $start = max(2, $currentPage - 1);
                                                $end = min($lastPage - 1, $currentPage + 1);
                                                if ($currentPage <= 3) { $end = min($lastPage - 1, 4); }
                                                if ($currentPage >= $lastPage - 2) { $start = max(2, $lastPage - 3); }
                                            }
                                        @endphp
                                        @if ($lastPage > 7)
                                            <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $brand->url(1) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">1</a>
                                            </li>
                                            @if ($showStartEllipsis)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                            @for ($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $brand->url($i) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">{{ $i }}</a>
                                                </li>
                                            @endfor
                                            @if ($showEndEllipsis)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                            <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $brand->url($lastPage) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">{{ $lastPage }}</a>
                                            </li>
                                        @else
                                            @for ($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $brand->url($i) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">{{ $i }}</a>
                                                </li>
                                            @endfor
                                        @endif
                                        <li class="page-item {{ !$brand->hasMorePages() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $brand->nextPageUrl() . '&per_page=' . request('per_page', 10) . (request('search') ? '&search=' . request('search') : '') ?? '#' }}">
                                                <i class="feather icon-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Data brand akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
