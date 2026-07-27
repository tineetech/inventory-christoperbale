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
                                <label class="form-label d-block">Pilih User <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select name="user_ids[]" id="selectUser" class="form-control @error('user_ids') is-invalid @enderror" multiple required style="width:100%"></select>
                                    <button type="button" id="btnSelectAllUser" class="btn btn-sm btn-outline-info text-nowrap" style="flex-shrink:0;">
                                        <i class="feather icon-check-square"></i> Semua
                                    </button>
                                    <button type="button" id="btnClearUser" class="btn btn-sm btn-outline-secondary text-nowrap" style="flex-shrink:0;">
                                        <i class="feather icon-x"></i>
                                    </button>
                                </div>
                                @error('user_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Cari & pilih user, atau klik "Semua" untuk memilih semua user.</small>
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

@section('scripts')
    <script>
        let allUsers = [];

        $('#selectUser').select2({
            placeholder: 'Cari & pilih user...',
            allowClear: true,
            ajax: {
                url: '{{ route("user_voucher.users") }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    allUsers = data;
                    return { results: data.map(function(u) { return { id: u.id, text: u.nama }; }) };
                }
            }
        });

        document.getElementById('btnSelectAllUser').addEventListener('click', function() {
            if (allUsers.length === 0) {
                $.ajax({
                    url: '{{ route("user_voucher.users") }}',
                    dataType: 'json',
                    async: false,
                    success: function(data) { allUsers = data; }
                });
            }
            const ids = allUsers.map(function(u) { return u.id; });
            $('#selectUser').val(ids).trigger('change');
        });

        document.getElementById('btnClearUser').addEventListener('click', function() {
            $('#selectUser').val(null).trigger('change');
        });
    </script>
@endsection
