@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Supplier</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Supplier</li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
            <div class="row">

                <div class="card col-lg-12 mb-4">
                    <h6 class="card-header">Formulir Perubahan Data Supplier</h6>
                    <div class="card-body">
                        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-row">

                                <!-- Nama Supplier -->
                                <div class="form-group col-md-6">
                                    <label class="form-label">Nama Supplier</label>
                                    <input type="text" name="nama_supplier" class="form-control"
                                        value="{{ old('nama_supplier', $supplier->nama_supplier) }}"
                                        placeholder="Masukkan nama supplier" required>

                                    @error('nama_supplier')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- No Telp -->
                                <div class="form-group col-md-6">
                                    <label class="form-label">No Telepon</label>
                                    <input type="text" name="no_telp" class="form-control"
                                        value="{{ old('no_telp', $supplier->no_telp) }}" placeholder="Contoh: 08123456789">

                                    @error('no_telp')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <!-- Alamat -->
                            <div class="form-group">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat supplier">{{ old('alamat', $supplier->alamat) }}</textarea>

                                @error('alamat')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="form-group">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)">{{ old('keterangan', $supplier->keterangan) }}</textarea>

                                @error('keterangan')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">

                                <a href="{{ route('supplier.index') }}" class="btn btn-secondary">
                                    <i class="feather icon-arrow-left"></i> Kembali
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Update Supplier
                                </button>

                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
        <!-- [ content ] End -->
        @include('components.footer')
    </div>
@endsection
