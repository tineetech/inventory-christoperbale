@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Tambah Pengaturan Web</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Konfigurasi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pengaturan_web.index') }}">Pengaturan Web</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </div>

            @if ($errors->any())
                <div class="card mb-4 border-danger">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 text-danger"><i class="feather icon-x-circle"></i> Error</h5>
                            @foreach ($errors->all() as $error)
                                <p class="mb-0 text-muted">{{ $error }}</p>
                            @endforeach
                        </div>
                        <div class="display-4 text-danger"><i class="feather icon-x-circle"></i></div>
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header" style="border:none!important">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-plus mr-2"></i> Form Pengaturan Baru
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengaturan_web.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Key <span class="text-danger">*</span></label>
                                <input type="text" name="key" class="form-control" value="{{ old('key') }}" required>
                                <small class="text-muted">Nama unik untuk pengaturan (contoh: api_biteship, limit_order, jam_buka).</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Value</label>
                                <input type="text" name="value" class="form-control" value="{{ old('value') }}">
                                <small class="text-muted">Nilai dari pengaturan.</small>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pengaturan_web.index') }}" class="btn btn-secondary">
                                <i class="feather icon-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather icon-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
