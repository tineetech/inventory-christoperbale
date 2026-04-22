@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Edit Profile</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item">Profile</li>
                    <li class="breadcrumb-item active">Edit Profile</li>
                </ol>
            </div>

            <div class="row justify-content-center">

                <div class="col-lg-12">

                    @if (session('success'))
                        <div class="card mb-4 border-success">

                            <div class="card-body d-flex align-items-center justify-content-between">

                                <div>
                                    <h5 class="mb-1 text-success">
                                        <i class="feather icon-check-circle"></i> Success
                                    </h5>

                                    <p class="mb-0 text-muted">
                                        {{ session('success') }}
                                    </p>
                                </div>

                                <div class="display-4 text-success">
                                    <i class="feather icon-check-circle"></i>
                                </div>

                            </div>

                        </div>
                    @endif

                </div>
                <div class="col-md-12">

                    <div class="card mb-4">

                        <div class="card-header">
                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-user mr-2"></i> Form Edit Profile
                            </h6>
                        </div>

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama</label>
                                            <input type="text" name="nama" class="form-control"
                                                value="{{ Auth::guard('pengguna')->user()->nama }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control"
                                                value="{{ Auth::guard('pengguna')->user()->email }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Password Baru</label>
                                            <input type="password" name="password" class="form-control"
                                                placeholder="Kosongkan jika tidak ingin mengganti">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Konfirmasi Password</label>
                                            <input type="password" name="password_confirmation" class="form-control"
                                                placeholder="Konfirmasi password baru">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Role</label>
                                            <input type="text" class="form-control"
                                                value="{{ Auth::guard('pengguna')->user()->role->nama_role ?? '-' }}" disabled>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="card-footer text-right">

                                <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">
                                    Batal
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Update Profile
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
