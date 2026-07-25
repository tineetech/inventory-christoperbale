@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Brand</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('brand.index') }}">Brand</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Edit Brand: {{ $brand->nama_brand }}</h6>
                <div class="card-body">
                    <form action="{{ route('brand.update', $brand->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Nama Brand <span class="text-danger">*</span></label>
                                <input type="text" name="nama_brand" class="form-control @error('nama_brand') is-invalid @enderror"
                                    placeholder="Masukkan nama brand" value="{{ old('nama_brand', $brand->nama_brand) }}" required>
                                @error('nama_brand')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status_brand" class="form-control @error('status_brand') is-invalid @enderror">
                                    <option value="aktif" {{ (old('status_brand', $brand->status_brand) === 'aktif') ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ (old('status_brand', $brand->status_brand) === 'nonaktif') ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('status_brand')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi_brand" class="form-control @error('deskripsi_brand') is-invalid @enderror"
                                rows="3" placeholder="Deskripsi brand (opsional)">{{ old('deskripsi_brand', $brand->deskripsi_brand) }}</textarea>
                            @error('deskripsi_brand')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('brand.index') }}" class="btn btn-secondary">
                                <i class="feather icon-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather icon-save"></i> Update Brand
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
