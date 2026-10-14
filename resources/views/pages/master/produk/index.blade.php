@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Produk</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item active">Produk</li>
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
                            <i class="feather icon-package mr-2"></i> Data Produk
                        </h6>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 5px">
                            @if (hasPermission('tambah', 'barang'))
                                <a href="{{ route('produk.create') }}" class="btn btn-sm text-white"
                                    style="background: linear-gradient(135deg, #ff8a00, #ff5b00); border: none;">
                                    <i class="feather icon-plus"></i>
                                    <span class="d-none d-sm-inline">Tambah Produk</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <form method="GET" action="{{ route('produk.index') }}" id="filterForm">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="flex-grow-1" style="min-width: 160px; max-width: 100%;">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="feather icon-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Cari produk..." value="{{ request('search') }}">
                                </div>
                            </div>

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

                            <button type="submit" class="btn btn-sm btn-secondary">
                                <i class="feather icon-search"></i>
                                <span class="d-none d-sm-inline">Cari</span>
                            </button>

                            @if (request('search'))
                                <a href="{{ route('produk.index', ['per_page' => request('per_page', 10)]) }}"
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
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Brand</th>
                                            <th>Slug</th>
                                            <th>Harga Normal</th>
                                            <th>Status</th>
                                            <th>Varian</th>
                                            <th>Foto</th>
                                            <th>Barang</th>
                                            <th>Dibuat</th>
                                            <th width="120">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($produk as $index => $p)
                                            <tr>
                                                <td>{{ $produk->firstItem() + $index }}</td>
                                                <td>
                                                    @if ($p->fotoUtama)
                                                        <img src="{{ asset('storage/' . $p->fotoUtama->foto) }}" alt="foto"
                                                            style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td><strong>{{ $p->nama_produk }}</strong></td>
                                                <td>{{ $p->brand->nama_brand ?? '-' }}</td>
                                                <td>{{ $p->slug }}</td>
                                                <td>Rp {{ number_format($p->harga_normal, 0, ',', '.') }}</td>
                                                <td>
                                                    @if ($p->status === 'aktif')
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>{{ $p->varian_count }}</td>
                                                <td>{{ $p->foto_count }}</td>
                                                <td>{{ $p->barang_count }}</td>
                                                <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @if (hasPermission('edit', 'barang'))
                                                        <a href="{{ route('produk.edit', $p->id) }}" class="btn btn-sm btn-info action-btn mb-1">
                                                            <i class="feather icon-edit"></i>
                                                        </a>
                                                    @endif
                                                    @if (hasPermission('hapus', 'barang'))
                                                        <button type="button" onclick="confirmDelete({{ $p->id }})"
                                                            class="btn btn-sm btn-danger action-btn mb-1">
                                                            <i class="feather icon-trash"></i>
                                                        </button>
                                                        <form id="delete-form-{{ $p->id }}"
                                                            action="{{ route('produk.destroy', $p->id) }}"
                                                            method="POST" style="display:none">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center text-muted py-5">
                                                    <i class="feather icon-inbox" style="font-size: 2rem"></i>
                                                    <p class="mt-2 mb-0">Tidak ada data produk.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">
                                <div class="text-muted small">
                                    @if ($produk->total() > 0)
                                        Showing <strong>{{ $produk->firstItem() }}</strong>
                                        to <strong>{{ $produk->lastItem() }}</strong>
                                        of <strong>{{ $produk->total() }}</strong> entries
                                        @if (request('search'))
                                            <span class="text-primary">(filtered)</span>
                                        @endif
                                    @else
                                        No entries found
                                    @endif
                                </div>

                                @if ($produk->hasPages())
                                    <ul class="pagination pagination-sm mb-0 flex-wrap">
                                        <li class="page-item {{ $produk->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link"
                                                href="{{ $produk->previousPageUrl() . '&per_page=' . request('per_page', 10) . (request('search') ? '&search=' . request('search') : '') ?? '#' }}">
                                                <i class="feather icon-chevron-left"></i>
                                            </a>
                                        </li>

                                        @php
                                            $currentPage = $produk->currentPage();
                                            $lastPage = $produk->lastPage();
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
                                            <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                                                <a class="page-link"
                                                    href="{{ $produk->url(1) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                    1
                                                </a>
                                            </li>
                                            @if ($showStartEllipsis)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            @for ($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                    <a class="page-link"
                                                        href="{{ $produk->url($i) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                        {{ $i }}
                                                    </a>
                                                </li>
                                            @endfor
                                            @if ($showEndEllipsis)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                                                <a class="page-link"
                                                    href="{{ $produk->url($lastPage) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                    {{ $lastPage }}
                                                </a>
                                            </li>
                                        @else
                                            @for ($i = $start; $i <= $end; $i++)
                                                <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                    <a class="page-link"
                                                        href="{{ $produk->url($i) }}&per_page={{ request('per_page', 10) }}{{ request('search') ? '&search=' . request('search') : '' }}">
                                                        {{ $i }}
                                                    </a>
                                                </li>
                                            @endfor
                                        @endif

                                        <li class="page-item {{ !$produk->hasMorePages() ? 'disabled' : '' }}">
                                            <a class="page-link"
                                                href="{{ $produk->nextPageUrl() . '&per_page=' . request('per_page', 10) . (request('search') ? '&search=' . request('search') : '') ?? '#' }}">
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
                text: 'Produk akan dihapus. Barang akan dipisah dari produk ini.',
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
