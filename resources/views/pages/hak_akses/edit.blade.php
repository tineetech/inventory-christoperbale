@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Hak Akses</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('hak_akses.index') }}">Hak Akses</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

            <div class="row">

                <div class="card col-lg-12 mb-4">

                    <h6 class="card-header">Formulir Perubahan Hak Akses</h6>

                    <div class="card-body">

                        <form action="{{ route('hak_akses.update', $hakAkses->id) }}" method="POST">

                            @csrf
                            @method('PUT')

                            <div class="form-group">

                                <label class="form-label">Nama Permission</label>

                                <input type="text" name="nama_permission" class="form-control"
                                    value="{{ old('nama_permission', $hakAkses->nama_permission) }}"
                                    placeholder="Contoh: tambah_barang" required>

                                @error('nama_permission')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="d-flex justify-content-between">

                                <a href="{{ route('hak_akses.index') }}" class="btn btn-secondary">
                                    <i class="feather icon-arrow-left"></i> Kembali
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Update Hak Akses
                                </button>

                            </div>

                        </form>

                    </div>
                </div>

            </div>

        </div>

        @include('components.footer')

    </div>
@endsection
