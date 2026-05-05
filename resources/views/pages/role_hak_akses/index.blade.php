@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Role Hak Akses</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Konfigurasi</a></li>
                    <li class="breadcrumb-item active">Role Hak Akses</li>
                </ol>
            </div>

            <div class="row">

                {{-- FORM TAMBAH ROLE BARU --}}
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="feather icon-plus-circle mr-2"></i>Tambah Role Baru</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('role.store') }}" method="POST">
                                @csrf
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-8 mb-0">
                                        <label class="form-label">Nama Role</label>
                                        <input type="text" name="nama_role" class="form-control"
                                            placeholder="Contoh: supervisor, gudang, kasir..."
                                            value="{{ old('nama_role') }}" required>
                                        @error('nama_role')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-4 mb-0">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="feather icon-save mr-1"></i> Simpan Role
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Daftar role yang sudah ada + tombol hapus --}}
                            <hr>
                            <h6 class="mb-3">Role yang Sudah Ada</h6>
                            <div class="d-flex flex-wrap" style="gap:8px">
                                @foreach ($roles as $r)
                                    <div class="d-flex align-items-center border rounded px-3 py-1" style="gap:8px; background:#f8fafc">
                                        <span class="font-weight-bold small">{{ $r->nama_role }}</span>
                                        <form action="{{ route('role.destroy', $r->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus role {{ $r->nama_role }}? Semua pengguna dengan role ini akan terpengaruh.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm p-0 px-1"
                                                    style="line-height:1.4; font-size:.75rem;">
                                                <i class="feather icon-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">

                    @if (session('error'))
                        <div class="card mb-4 border-danger">

                            <div class="card-body d-flex align-items-center justify-content-between">

                                <div>
                                    <h5 class="mb-1 text-danger">
                                        <i class="feather icon-x-circle"></i> Error
                                    </h5>

                                    <p class="mb-0 text-muted">
                                        {{ session('error') }}
                                    </p>
                                </div>

                                <div class="display-4 text-danger">
                                    <i class="feather icon-x-circle"></i>
                                </div>

                            </div>

                        </div>
                    @endif
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

                <div class="col-lg-12">

                    <div class="card mb-4">

                        <div class="card-header">
                            <h6 class="mb-0">Mapping Hak Akses ke Role</h6>
                        </div>

                        <div class="card-body">

                            <form action="{{ route('role_hak_akses.update', $role->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- SELECT ROLE -->
                                <div class="form-group">

                                    <label class="form-label">Pilih Role</label>

                                    <select class="form-control" onchange="window.location='?role='+this.value">

                                        @foreach ($roles as $r)
                                            <option value="{{ $r->id }}" {{ $role->id == $r->id ? 'selected' : '' }}>

                                                {{ $r->nama_role }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>


                                <hr>

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <h6 class="mb-0">Daftar Hak Akses</h6>

                                    <label class="mb-0">
                                        <input type="checkbox" id="checkAllPermissions">
                                        Pilih Semua
                                    </label>

                                </div>
                                {{-- @php
                                    $lihat = $permissions->filter(
                                        fn($p) => str_starts_with($p->nama_permission, 'lihat'),
                                    );
                                    $tambah = $permissions->filter(
                                        fn($p) => str_starts_with($p->nama_permission, 'tambah'),
                                    );
                                    $edit = $permissions->filter(
                                        fn($p) => str_starts_with($p->nama_permission, 'edit'),
                                    );
                                    $hapus = $permissions->filter(
                                        fn($p) => str_starts_with($p->nama_permission, 'hapus'),
                                    );
                                    $buat = $permissions->filter(
                                        fn($p) => str_starts_with($p->nama_permission, 'buat'),
                                    );
                                    $export = $permissions->filter(
                                        fn($p) => str_starts_with($p->nama_permission, 'export'),
                                    );
                                @endphp --}}


                                <div class="row">

                                    @foreach (['lihat', 'tambah', 'edit', 'hapus', 'buat', 'export'] as $group)
                                        <div class="col-md-2">
                                            <h6 class="mb-3 text-capitalize">{{ $group }}</h6>

                                            @foreach ($groupedPermissions[$group] ?? [] as $permission)
                                                <div class="mb-2">
                                                    <label>
                                                        <input type="checkbox" class="permission-checkbox"
                                                            name="permissions[]" value="{{ $permission->id }}"
                                                            {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                        {{ $permission->nama_permission }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach

                                </div>

                                <hr>

                                <div class="text-right">

                                    <button class="btn btn-primary">

                                        <i class="feather icon-save"></i>
                                        Simpan Hak Akses

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        @include('components.footer')

    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('checkAllPermissions').addEventListener('change', function() {

            let permissions = document.querySelectorAll('.permission-checkbox');

            permissions.forEach(function(permission) {
                permission.checked = event.target.checked;
            });

        });

        setTimeout(function() {

            let alertCard = document.querySelector('.border-success');

            if (alertCard) {

                alertCard.style.transition = "0.5s";
                alertCard.style.opacity = "0";

                setTimeout(() => {
                    alertCard.remove();
                }, 500);

            }

        }, 4000);
    </script>
@endsection
