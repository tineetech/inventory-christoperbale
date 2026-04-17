@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Dropshipper</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Dropshipper</li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
            <div class="row">

                <div class="card col-lg-12 mb-4">
                    <h6 class="card-header">Formulir Perubahan Data Dropshipper</h6>
                    <div class="card-body">
                        <form action="{{ route('dropshipper.update', $dropshipper->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-row">

                                <div class="form-group col-md-6">
                                    <label class="form-label">Nama Dropshipper</label>
                                    <input type="text" name="nama" class="form-control"
                                        value="{{ old('nama', $dropshipper->nama) }}"
                                        placeholder="Masukkan nama" required>

                                    @error('nama')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label">No Telp</label>
                                    <input type="text" name="no_telp" class="form-control"
                                        value="{{ old('no_telp', $dropshipper->no_telp) }}"
                                        placeholder="Masukkan no_telp" required>

                                    @error('no_telp')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" name="alamat" class="form-control"
                                        value="{{ old('alamat', $dropshipper->alamat) }}"
                                        placeholder="Masukkan alamat" required>

                                    @error('alamat')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)">{{ old('keterangan', $dropshipper->keterangan) }}</textarea>

                                    @error('keterangan')
                                        <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                </div>


                            </div>


                            <div class="d-flex justify-content-between">

                                <a href="{{ route('dropshipper.index') }}" class="btn btn-secondary">
                                    <i class="feather icon-arrow-left"></i> Kembali
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Update Dropshipper
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
