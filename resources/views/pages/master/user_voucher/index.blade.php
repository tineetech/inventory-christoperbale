@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">User Voucher</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item active">User Voucher</li>
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
                        <h6 class="card-header-title mb-0"><i class="feather icon-users mr-2"></i> Data User Voucher</h6>
                        <div class="d-flex flex-wrap align-items-center" style="gap:5px">
                            @if (hasPermission('tambah', 'barang'))
                                <a href="{{ route('user_voucher.create') }}" class="btn btn-sm text-white"
                                    style="background:linear-gradient(135deg,#ff8a00,#ff5b00);border:none;">
                                    <i class="feather icon-plus"></i> <span class="d-none d-sm-inline">Beri Voucher</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <form method="GET" action="{{ route('user_voucher.index') }}" id="filterForm">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="flex-grow-1" style="min-width:160px;max-width:100%;">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="feather icon-search"></i></span></div>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari voucher/user..." value="{{ request('search') }}">
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
                            @if(request('search'))<a href="{{ route('user_voucher.index',['per_page'=>request('per_page',10)]) }}" class="btn btn-sm btn-outline-secondary"><i class="feather icon-x"></i> <span class="d-none d-sm-inline">Reset</span></a>@endif
                        </div>
                    </form>
                </div>
                <div class="nav-tabs-top">
                    <div class="tab-content d-flex justify-content-center" style="width:100%">
                        <div class="tab-pane fade show active pb-3" style="width:95%">
                            <div class="d-none d-md-block" style="overflow-x:auto">
                                <table class="table table-modern table-hover mb-0" id="table">
                                    <thead>
                                        <tr>
                                            <th style="width:50px">No</th>
                                            <th>User</th>
                                            <th>Voucher</th>
                                            <th>Kode</th>
                                            <th>Nilai</th>
                                            <th>Status</th>
                                            <th>Diklaim</th>
                                            <th>Digunakan</th>
                                            <th width="100">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($userVouchers as $index => $uv)
                                            <tr>
                                                <td>{{ $userVouchers->firstItem() + $index }}</td>
                                                <td>{{ $uv->user->nama ?? '-' }}</td>
                                                <td>{{ $uv->voucher->name ?? '-' }}</td>
                                                <td><code>{{ $uv->voucher->code ?? '-' }}</code></td>
                                                <td>{{ $uv->voucher->type === 'percent' ? ($uv->voucher->value ?? 0).'%' : 'Rp '.number_format($uv->voucher->value ?? 0,0,',','.') }}</td>
                                                <td>
                                                    @if ($uv->status === 'unused')
                                                        <span class="badge badge-info">Unused</span>
                                                    @elseif ($uv->status === 'used')
                                                        <span class="badge badge-secondary">Used</span>
                                                    @elseif ($uv->status === 'expired')
                                                        <span class="badge badge-warning">Expired</span>
                                                    @else
                                                        <span class="badge badge-danger">{{ ucfirst($uv->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $uv->claimed_at ? \Carbon\Carbon::parse($uv->claimed_at)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $uv->used_at ? \Carbon\Carbon::parse($uv->used_at)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>
                                                    @if (hasPermission('hapus','barang'))
                                                        <button type="button" onclick="confirmDelete({{ $uv->id }})" class="btn btn-sm btn-danger action-btn mb-1"><i class="feather icon-trash"></i></button>
                                                        <form id="delete-form-{{ $uv->id }}" action="{{ route('user_voucher.destroy', $uv->id) }}" method="POST" style="display:none">@csrf @method('DELETE')</form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="9" class="text-center text-muted py-5"><i class="feather icon-inbox" style="font-size:2rem"></i><p class="mt-2 mb-0">Tidak ada data user voucher.</p></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">
                                <div class="text-muted small">
                                    @if ($userVouchers->total() > 0)
                                        Showing <strong>{{ $userVouchers->firstItem() }}</strong> to <strong>{{ $userVouchers->lastItem() }}</strong> of <strong>{{ $userVouchers->total() }}</strong> entries
                                        @if (request('search'))<span class="text-primary">(filtered)</span>@endif
                                    @else
                                        No entries found
                                    @endif
                                </div>
                                @if ($userVouchers->hasPages())
                                    <ul class="pagination pagination-sm mb-0 flex-wrap">
                                        <li class="page-item {{ $userVouchers->onFirstPage()?'disabled':'' }}"><a class="page-link" href="{{ $userVouchers->previousPageUrl().'&per_page='.request('per_page',10).(request('search')?'&search='.request('search'):'')??'#' }}"><i class="feather icon-chevron-left"></i></a></li>
                                        @php $cp=$userVouchers->currentPage();$lp=$userVouchers->lastPage();if($lp<=7){$s=1;$e=$lp;$ss=false;$se=false;}else{$ss=$cp>3;$se=$cp<$lp-2;$s=max(2,$cp-1);$e=min($lp-1,$cp+1);if($cp<=3){$e=min($lp-1,4);}if($cp>=$lp-2){$s=max(2,$lp-3);}}@endphp
                                        @if($lp>7)
                                            <li class="page-item {{ $cp==1?'active':'' }}"><a class="page-link" href="{{ $userVouchers->url(1).'&per_page='.request('per_page',10).(request('search')?'&search='.request('search'):'') }}">1</a></li>
                                            @if($ss)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                            @for($i=$s;$i<=$e;$i++)<li class="page-item {{ $cp==$i?'active':'' }}"><a class="page-link" href="{{ $userVouchers->url($i).'&per_page='.request('per_page',10).(request('search')?'&search='.request('search'):'') }}">{{ $i }}</a></li>@endfor
                                            @if($se)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                            <li class="page-item {{ $cp==$lp?'active':'' }}"><a class="page-link" href="{{ $userVouchers->url($lp).'&per_page='.request('per_page',10).(request('search')?'&search='.request('search'):'') }}">{{ $lp }}</a></li>
                                        @else
                                            @for($i=$s;$i<=$e;$i++)<li class="page-item {{ $cp==$i?'active':'' }}"><a class="page-link" href="{{ $userVouchers->url($i).'&per_page='.request('per_page',10).(request('search')?'&search='.request('search'):'') }}">{{ $i }}</a></li>@endfor
                                        @endif
                                        <li class="page-item {{ !$userVouchers->hasMorePages()?'disabled':'' }}"><a class="page-link" href="{{ $userVouchers->nextPageUrl().'&per_page='.request('per_page',10).(request('search')?'&search='.request('search'):'')??'#' }}"><i class="feather icon-chevron-right"></i></a></li>
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
            Swal.fire({ title:'Yakin hapus?', text:'Data user voucher akan dihapus.', icon:'warning', showCancelButton:true, confirmButtonColor:'#d33', cancelButtonColor:'#6c757d', confirmButtonText:'Ya, hapus!', cancelButtonText:'Batal' })
            .then(r => { if(r.isConfirmed) document.getElementById('delete-form-'+id).submit(); });
        }
    </script>
@endsection
