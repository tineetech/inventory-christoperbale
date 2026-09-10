@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Chatbot FAQ</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="#">Marketing</a></li>
                    <li class="breadcrumb-item active">Chatbot FAQ</li>
                </ol>
            </div>

            @if (session('success'))
                <div class="card mb-4 border-success alert-dismissible-auto">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div><h5 class="mb-1 text-success"><i class="feather icon-check-circle"></i> Success</h5><p class="mb-0 text-muted">{{ session('success') }}</p></div>
                        <div class="display-4 text-success"><i class="feather icon-check-circle"></i></div>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="card mb-4 border-danger">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div><h5 class="mb-1 text-danger"><i class="feather icon-x-circle"></i> Error</h5><p class="mb-0 text-muted">{{ session('error') }}</p></div>
                        <div class="display-4 text-danger"><i class="feather icon-x-circle"></i></div>
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header" style="border:none!important">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h6 class="card-header-title mb-0"><i class="feather icon-message-square mr-2"></i> Data Chatbot FAQ
                            <span class="badge badge-light ml-1">{{ $faqs->total() }} item</span>
                        </h6>
                        <div class="d-flex flex-wrap align-items-center" style="gap:5px">
                            @if (hasPermission('tambah', 'barang'))
                                <a href="{{ route('chatbot_faq.create') }}" class="btn btn-sm text-white"
                                    style="background:linear-gradient(135deg,#ff8a00,#ff5b00);border:none;">
                                    <i class="feather icon-plus"></i> <span class="d-none d-sm-inline">Tambah FAQ</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <form method="GET" action="{{ route('chatbot_faq.index') }}" id="filterForm">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="flex-grow-1" style="min-width:160px;max-width:100%;">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="feather icon-search"></i></span></div>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari pertanyaan, jawaban, keyword..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted small mr-2">Show</span>
                                <select name="per_page" class="form-control form-control-sm" style="width:75px" onchange="this.form.submit()">
                                    @foreach([10,25,50,100] as $n)<option value="{{ $n }}" {{ request('per_page',10)==$n?'selected':'' }}>{{ $n }}</option>@endforeach
                                </select>
                                <span class="text-muted small ml-2">entries</span>
                            </div>
                            <button type="submit" class="btn btn-sm btn-secondary"><i class="feather icon-search"></i> <span class="d-none d-sm-inline">Cari</span></button>
                            @if(request('search'))
                                <a href="{{ route('chatbot_faq.index',['per_page'=>request('per_page',10)]) }}" class="btn btn-sm btn-outline-secondary"><i class="feather icon-x"></i> <span class="d-none d-sm-inline">Reset</span></a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0" id="table">
                        <thead>
                            <tr>
                                <th style="width:50px">No</th>
                                <th>Pertanyaan</th>
                                <th>Jawaban</th>
                                <th>Quick Question</th>
                                <th>Keyword</th>
                                <th>Status</th>
                                <th>Sinkron</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($faqs as $index => $faq)
                                <tr>
                                    <td>{{ $faqs->firstItem() + $index }}</td>
                                    <td style="white-space:nowrap"><strong>{{ $faq->pertanyaan }}</strong></td>
                                    <td style="max-width:280px;white-space:normal;word-wrap:break-word;overflow-wrap:break-word">
                                        {{ \Illuminate\Support\Str::limit($faq->jawaban, 100) }}
                                    </td>
                                    <td>
                                        @if ($faq->quick_question)
                                            <span class="badge badge-success">Ya</span>
                                            @if ($faq->quick_question_order !== null)
                                                <small class="text-muted">#{{ $faq->quick_question_order }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td style="max-width:160px">
                                        @forelse (collect($faq->keywords)->take(3) as $keyword)
                                            <span class="badge badge-info mr-1">{{ $keyword }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @if ($faq->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($faq->is_sync)
                                            <span class="badge badge-primary">Tersinkron</span>
                                        @else
                                            <span class="badge badge-warning">Belum</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">
                                        @if (hasPermission('edit', 'barang'))
                                            <a href="{{ route('chatbot_faq.edit', $faq->id) }}" class="btn btn-sm btn-warning action-btn mb-1" title="Edit">
                                                <i class="feather icon-edit"></i>
                                            </a>
                                        @endif
                                        @if (hasPermission('hapus', 'barang'))
                                            <button type="button" onclick="confirmDelete({{ $faq->id }})" class="btn btn-sm btn-danger action-btn mb-1" title="Hapus">
                                                <i class="feather icon-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $faq->id }}" action="{{ route('chatbot_faq.destroy', $faq->id) }}" method="POST" style="display:none">
                                                @csrf @method('DELETE')
                                            </form>
                                        @endif
                                        @if (!$faq->is_sync)
                                            <form action="{{ route('chatbot_faq.sync', $faq->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success action-btn mb-1" title="Sinkronkan ke Website">
                                                    <i class="feather icon-refresh-cw"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5"><i class="feather icon-inbox" style="font-size:2rem"></i><p class="mt-2 mb-0">Tidak ada data chatbot FAQ.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">
                    <div class="text-muted small">
                        @if ($faqs->total() > 0)
                            Showing <strong>{{ $faqs->firstItem() }}</strong> to <strong>{{ $faqs->lastItem() }}</strong> of <strong>{{ $faqs->total() }}</strong> entries
                        @else
                            No entries found
                        @endif
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @php
                                $cp = $faqs->currentPage();
                                $lp = $faqs->lastPage();
                                $start = max(1, $cp - 2);
                                $end = min($lp, $cp + 2);
                                if ($start <= 3) $end = min($lp, 5);
                                if ($end >= $lp - 2) $start = max(1, $lp - 4);
                            @endphp

                            <li class="page-item {{ $faqs->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $faqs->appends(['per_page' => request('per_page')])->url(1) }}">
                                    <i class="feather icon-chevrons-left"></i>
                                </a>
                            </li>
                            <li class="page-item {{ $faqs->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $faqs->previousPageUrl() }}">
                                    <i class="feather icon-chevron-left"></i>
                                </a>
                            </li>

                            @if ($lp > 7 && $start > 1)
                                <li class="page-item"><a class="page-link" href="{{ $faqs->url(1) }}">1</a></li>
                                @if ($start > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $cp ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $faqs->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if ($lp > 7 && $end < $lp)
                                @if ($end < $lp - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                <li class="page-item"><a class="page-link" href="{{ $faqs->url($lp) }}">{{ $lp }}</a></li>
                            @endif

                            <li class="page-item {{ !$faqs->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $faqs->nextPageUrl() }}">
                                    <i class="feather icon-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item {{ !$faqs->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $faqs->appends(['per_page' => request('per_page')])->url($lp) }}">
                                    <i class="feather icon-chevrons-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
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
                text: 'FAQ Chatbot akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
        }
    </script>
@endsection