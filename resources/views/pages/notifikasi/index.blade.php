@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Notifikasi</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Konfigurasi</a></li>
                    <li class="breadcrumb-item active">Notifikasi</li>
                </ol>
            </div>

            <div class="card mb-4">
                <div style="border:none !important" class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-bell mr-2"></i> Filter Notifikasi
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('notifikasi.index') }}" id="notifikasiFilterForm">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Dari Tanggal</label>
                                <input type="date" name="dari_tanggal" class="form-control"
                                    value="{{ $dariTanggal }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Sampai Tanggal</label>
                                <input type="date" name="sampai_tanggal" class="form-control"
                                    value="{{ $sampaiTanggal }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Cari</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Cari judul, isi, tipe, link, user..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end" style="gap:10px">
                            <button type="submit" class="btn btn-info">
                                <i class="feather icon-refresh-cw"></i> Proses
                            </button>
                            @if (request('search') || request('dari_tanggal') || request('sampai_tanggal'))
                                <a href="{{ route('notifikasi.index', ['per_page' => request('per_page', 10)]) }}"
                                    class="btn btn-outline-secondary">
                                    <i class="feather icon-rotate-ccw"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div style="border:none !important" class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-bell mr-2"></i> Data Notifikasi
                        <span class="badge badge-light ml-1">{{ $notifikasi->total() }} notifikasi</span>
                    </h6>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0" style="min-width:900px">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Isi</th>
                                <th>Tipe</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th style="width:80px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($notifikasi as $n)
                                <tr>
                                    <td>{{ $notifikasi->firstItem() + $loop->index }}</td>
                                    <td style="white-space:nowrap"><strong>{{ $n->judul }}</strong></td>
                                    <td style="max-width:320px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word">
                                        {{ \Illuminate\Support\Str::limit($n->isi, 120) }}
                                    </td>
                                    <td style="white-space:nowrap">
                                        @if ($n->tipe)
                                            <span class="badge badge-light">{{ $n->tipe }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">
                                        @if ($n->user)
                                            {{ $n->user->nama }}
                                        @else
                                            <span class="badge badge-info">Semua</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($n->is_read)
                                            <span class="badge badge-success">Sudah Dibaca</span>
                                        @else
                                            <span class="badge badge-warning">Belum Dibaca</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">
                                        {{ \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="white-space:nowrap">
                                        <button type="button" class="btn btn-sm btn-info"
                                            title="Lihat Detail" onclick="showNotifDetail({{ $n->id }})">
                                            <i class="feather icon-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Tidak ada data notifikasi pada filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top"
                    style="gap:8px">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 text-muted small">Show</span>
                        <select class="form-control form-control-sm" name="per_page"
                            form="notifikasiFilterForm" style="width:72px"
                            onchange="document.getElementById('notifikasiFilterForm').submit()">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ml-2 text-muted small">entries</span>
                    </div>
                    <div class="text-muted small">
                        @if ($notifikasi->total() > 0)
                            Showing <strong>{{ $notifikasi->firstItem() }}</strong>
                            to <strong>{{ $notifikasi->lastItem() }}</strong>
                            of <strong>{{ $notifikasi->total() }}</strong> entries
                        @else
                            No entries found
                        @endif
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @php
                                $currentPage = $notifikasi->currentPage();
                                $lastPage = $notifikasi->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                                if ($start <= 3) $end = min($lastPage, 5);
                                if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                            @endphp

                            <li class="page-item {{ $notifikasi->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $notifikasi->appends(['per_page' => request('per_page')])->url(1) }}">
                                    <i class="feather icon-chevrons-left"></i>
                                </a>
                            </li>
                            <li class="page-item {{ $notifikasi->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $notifikasi->previousPageUrl() }}">
                                    <i class="feather icon-chevron-left"></i>
                                </a>
                            </li>

                            @if ($lastPage > 7 && $start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifikasi->url(1) }}">1</a>
                                </li>
                                @if ($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $notifikasi->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if ($lastPage > 7 && $end < $lastPage)
                                @if ($end < $lastPage - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $notifikasi->url($lastPage) }}">{{ $lastPage }}</a>
                                </li>
                            @endif

                            <li class="page-item {{ !$notifikasi->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $notifikasi->nextPageUrl() }}">
                                    <i class="feather icon-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item {{ !$notifikasi->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $notifikasi->appends(['per_page' => request('per_page')])->url($lastPage) }}">
                                    <i class="feather icon-chevrons-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        {{-- MODAL DETAIL NOTIFIKASI --}}
        <div class="modal fade" id="notifDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="feather icon-eye mr-1"></i> Detail Notifikasi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th style="width:130px">Judul</th>
                                <td id="ndJudul" style="font-weight:600"></td>
                            </tr>
                            <tr>
                                <th>Tipe</th>
                                <td id="ndTipe"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="ndStatus"></td>
                            </tr>
                            <tr>
                                <th>Tujuan</th>
                                <td id="ndUser"></td>
                            </tr>
                            <tr>
                                <th>Dibuat Oleh</th>
                                <td id="ndCreator"></td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td id="ndDibuat"></td>
                            </tr>
                            <tr>
                                <th>Link</th>
                                <td id="ndLink"></td>
                            </tr>
                        </table>
                        <hr class="my-2">
                        <label class="font-weight-bold small">Isi</label>
                        <div id="ndIsi" class="p-2 rounded" style="background:#f8f9fa;white-space:pre-wrap;word-wrap:break-word;"></div>
                        <div id="ndPayloadWrap" style="display:none">
                            <label class="font-weight-bold small mt-2">Payload</label>
                            <pre id="ndPayload" class="p-2 rounded small mb-0" style="background:#f0f0f0;max-height:200px;overflow:auto;"></pre>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="feather icon-x"></i> Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const NOTIF_DETAIL = @json($notifDetail);

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function showNotifDetail(id) {
            const d = NOTIF_DETAIL[id];
            if (!d) return;

            document.getElementById('ndJudul').textContent = d.judul ?? '-';
            document.getElementById('ndTipe').innerHTML = d.tipe
                ? '<span class="badge badge-light">' + escapeHtml(d.tipe) + '</span>'
                : '<span class="text-muted">-</span>';
            document.getElementById('ndStatus').innerHTML = d.is_read
                ? '<span class="badge badge-success">Sudah Dibaca' + (d.read_at ? ' (' + escapeHtml(d.read_at) + ')' : '') + '</span>'
                : '<span class="badge badge-warning">Belum Dibaca</span>';
            document.getElementById('ndUser').innerHTML = d.user
                ? escapeHtml(d.user)
                : '<span class="badge badge-info">Semua</span>';
            document.getElementById('ndCreator').textContent = d.creator ?? '-';
            document.getElementById('ndDibuat').textContent = d.dibuat ?? '-';
            document.getElementById('ndLink').innerHTML = d.link
                ? '<a href="' + escapeHtml(d.link) + '" target="_blank" rel="noopener">' + escapeHtml(d.link) + '</a>'
                : '<span class="text-muted">-</span>';
            document.getElementById('ndIsi').textContent = d.isi ?? '-';

            const payloadWrap = document.getElementById('ndPayloadWrap');
            if (d.payload && Object.keys(d.payload).length > 0) {
                document.getElementById('ndPayload').textContent = JSON.stringify(d.payload, null, 2);
                payloadWrap.style.display = 'block';
            } else {
                payloadWrap.style.display = 'none';
            }

            $('#notifDetailModal').modal('show');
        }
    </script>
@endsection