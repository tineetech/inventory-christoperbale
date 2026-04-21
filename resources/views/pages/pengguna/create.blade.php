@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Pengguna</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pengguna.index') }}">Pengguna</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

            <div class="row">

                <div class="card col-lg-12 mb-4">

                    <h6 class="card-header">Formulir Pembuatan Data Pengguna</h6>

                    <div class="card-body">

                        <form action="{{ route('pengguna.store') }}" method="POST">

                            @csrf

                            <div class="form-row">

                                <!-- Nama -->
                                <div class="form-group col-md-6">
                                    <label class="form-label">Nama Pengguna</label>
                                    <input type="text" name="nama" class="form-control"
                                        placeholder="Masukkan nama pengguna" value="{{ old('nama') }}" required>

                                    @error('nama')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>


                                <!-- Email -->
                                <div class="form-group col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="Masukkan email pengguna" value="{{ old('email') }}" required>

                                    @error('email')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>


                            <div class="form-row">

                                <!-- Password -->
                                <div class="form-group col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Masukkan password" required>

                                    @error('password')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>


                                <!-- Role -->
                                <div class="form-group col-md-6">
                                    <label class="form-label">Role</label>

                                    <select name="role_id" class="form-control" required>

                                        <option value="">-- Pilih Role --</option>

                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">
                                                {{ $role->nama_role }}
                                            </option>
                                        @endforeach

                                    </select>

                                    @error('role_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror

                                </div>

                            </div>


                            <div class="d-flex justify-content-between">

                                <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">
                                    <i class="feather icon-arrow-left"></i> Kembali
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Simpan Pengguna
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
