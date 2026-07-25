@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Voucher</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('voucher.index') }}">Voucher</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Tambah Voucher Baru</h6>
                <div class="card-body">
                    <form action="{{ route('voucher.store') }}" method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Kode Voucher <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                    placeholder="Contoh: PROMO50" value="{{ old('code') }}" required>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Nama Voucher <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Nama voucher" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="fixed" {{ old('type')==='fixed' ? 'selected' : '' }}>Fixed (Rp)</option>
                                    <option value="percent" {{ old('type')==='percent' ? 'selected' : '' }}>Percent (%)</option>
                                    <option value="shipping" {{ old('type')==='shipping' ? 'selected' : '' }}>Shipping</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Nilai <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="value" class="form-control @error('value') is-invalid @enderror"
                                    placeholder="0" value="{{ old('value') }}" required>
                                @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">Minimum Purchase</label>
                                <input type="number" step="0.01" name="minimum_purchase" class="form-control @error('minimum_purchase') is-invalid @enderror"
                                    placeholder="0" value="{{ old('minimum_purchase', 0) }}">
                                @error('minimum_purchase')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Maximum Discount</label>
                                <input type="number" step="0.01" name="maximum_discount" class="form-control @error('maximum_discount') is-invalid @enderror"
                                    placeholder="Kosongkan jika tidak ada" value="{{ old('maximum_discount') }}">
                                @error('maximum_discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Kuota</label>
                                <input type="number" name="quota" class="form-control @error('quota') is-invalid @enderror"
                                    placeholder="Kosongkan jika tidak terbatas" value="{{ old('quota') }}">
                                @error('quota')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Claim Limit Per User</label>
                                <input type="number" name="claim_limit_per_user" class="form-control @error('claim_limit_per_user') is-invalid @enderror"
                                    placeholder="1" value="{{ old('claim_limit_per_user', 1) }}">
                                @error('claim_limit_per_user')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror"
                                    value="{{ old('start_at') }}" required>
                                @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror"
                                    value="{{ old('end_at') }}" required>
                                @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status')==='active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status')==='inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                                placeholder="Deskripsi voucher (opsional)">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('voucher.index') }}" class="btn btn-secondary"><i class="feather icon-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Simpan Voucher</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
