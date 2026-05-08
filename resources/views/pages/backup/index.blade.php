@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Backup Database</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">System</a></li>
                    <li class="breadcrumb-item active">Backup Database</li>
                </ol>
            </div>

            <div class="row">

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


                    <div class="card mb-4">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-database mr-2"></i> Reset Database (Kec. data master)
                            </h6>

                        </div>

                        <div class="card-body">

                            <form action="{{ route('reset.run') }}" method="POST" id="formReset">
                                @csrf

                                <div class="text-center">

                                    <button type="button" class="btn btn-danger btn-lg" id="btnReset">
                                        <i class="feather icon-trash"></i> Reset Database
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                    <div class="card mb-4">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-database mr-2"></i> Backup Database
                            </h6>

                        </div>

                        <div class="card-body">

                            <div class="alert bg-info text-white">

                                Backup database akan membuat file <strong>.sql</strong> yang berisi seluruh data sistem POS
                                Inventory.

                            </div>

                            <form action="{{ route('backup.run') }}" method="POST">
                                @csrf

                                <div class="text-center">

                                    <button type="submit" class="btn btn-success btn-lg">

                                        <i class="feather icon-download"></i> Backup & Download Database

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
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script>
    document.getElementById('btnReset').addEventListener('click', function () {
        Swal.fire({
            title: 'Yakin ingin reset database?',
            text: 'Semua data transaksi akan dihapus permanen. Tindakan ini tidak dapat dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formReset').submit();
            }
        });
    });
</script>
@endsection