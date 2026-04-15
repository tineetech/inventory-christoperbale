@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Satuan</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Satuan</li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
            <div class="row">

                <div class="card col-lg-12 mb-4">
                    <h6 class="card-header">Formulir Perubahan Data Satuan</h6>
                    <div class="card-body">
                        <form action="{{ route('satuan.update', $satuan->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-row">

                                <div class="form-group col-md-6">
                                    <label class="form-label">Nama Satuan</label>
                                    <input type="text" name="nama_satuan" class="form-control"
                                        value="{{ old('nama_satuan', $satuan->nama_satuan) }}"
                                        placeholder="Masukkan nama satuan" required>

                                    @error('nama_satuan')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>


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
