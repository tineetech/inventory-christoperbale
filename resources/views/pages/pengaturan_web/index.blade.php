@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Pengaturan Web</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Konfigurasi</a></li>
                    <li class="breadcrumb-item active">Pengaturan Web</li>
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
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center" style="border:none!important">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-settings mr-2"></i> Daftar Pengaturan
                    </h6>
                    <div>
                        <a href="{{ route('pengaturan_web.create') }}" class="btn btn-sm text-white"
                            style="background:linear-gradient(135deg,#667eea,#764ba2);border:none;">
                            <i class="feather icon-plus"></i> Tambah Pengaturan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengaturan_web.update') }}" method="POST">
                        @method("PUT")
                        @csrf

                        <div class="row">
                            @forelse ($settings as $s)
                                <div class="col-md-6 col-12 mb-3">
                                    <label class="form-label mb-1" style="font-size:13px;font-weight:600;">
                                        {{ $s->key }}
                                    </label>
                                    <input type="text" name="values[{{ $s->id }}]"
                                        class="form-control form-control-sm"
                                        value="{{ $s->value }}">
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-4">
                                    <i class="feather icon-inbox" style="font-size:2rem"></i>
                                    <p class="mt-2 mb-0">Belum ada pengaturan.</p>
                                </div>
                            @endforelse
                        </div>

                        @if ($settings->isNotEmpty())
                            <hr>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Simpan Semua
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
