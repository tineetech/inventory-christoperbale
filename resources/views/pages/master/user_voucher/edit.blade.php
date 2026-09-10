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
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

            @if (session('error'))
                <div class="alert alert-danger"><i class="feather icon-alert-circle"></i> {{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Edit User Voucher</h6>
                <div class="card-body">
                    <form action="{{ route('user_voucher.update', $userVoucher->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Pilih Voucher <span class="text-danger">*</span></label>
                                <select name="voucher_id" class="form-control @error('voucher_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Voucher --</option>
                                    @foreach ($vouchers as $v)
                                        <option value="{{ $v->id }}" {{ old('voucher_id', $userVoucher->voucher_id) == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }} ({{ $v->code }}) - {{ $v->type === 'percent' ? $v->value.'%' : 'Rp '.number_format($v->value,0,',','.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('voucher_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Pilih User <span class="text-danger">*</span></label>
                                <select name="user_id" id="selectUserEdit" class="form-control @error('user_id') is-invalid @enderror" required style="width:100%">
                                    <option value="{{ $userVoucher->user->id }}" selected>{{ $userVoucher->user->nama }}</option>
                                </select>
                                @error('user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <small class="text-muted">Jika ganti user/voucher yang sudah ada kombinasi duplikat, akan ditolak.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($userVoucher->status) }}" disabled>
                            <small class="text-muted">Status tidak diubah di sini.</small>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user_voucher.index') }}" class="btn btn-secondary"><i class="feather icon-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#selectUserEdit').select2({
            placeholder: 'Cari & pilih user...',
            allowClear: false,
            ajax: {
                url: '{{ route("user_voucher.users") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data.map(function(u) { return { id: u.id, text: u.nama }; }) };
                }
            }
        });
    </script>
@endsection
