@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">User Voucher</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user_voucher.index') }}">User Voucher</a></li>
                    <li class="breadcrumb-item active">Beri Voucher</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Beri Voucher ke User</h6>
                <div class="card-body">
                    <form action="{{ route('user_voucher.store') }}" method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Pilih Voucher <span class="text-danger">*</span></label>
                                <select name="voucher_id" class="form-control @error('voucher_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Voucher --</option>
                                    @foreach ($vouchers as $v)
                                        <option value="{{ $v->id }}" {{ old('voucher_id') == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }} ({{ $v->code }}) - {{ $v->type === 'percent' ? $v->value.'%' : 'Rp '.number_format($v->value,0,',','.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('voucher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Pilih User <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih User --</option>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                                    @endforeach
                                </select>
                                @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user_voucher.index') }}" class="btn btn-secondary"><i class="feather icon-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
