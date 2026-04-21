@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Hak Akses</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Hak Akses</li>
                </ol>
            </div>

            <div class="row">

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


                <div class="col-sm-12">

                    <div class="card mb-4">

                        <div style="border:none !important"
                            class="card-header d-flex justify-content-between align-items-center">

                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-lock mr-2"></i> Data Hak Akses
                            </h6>

                            <div class="d-flex gap-5">

                                <div class="d-flex mr-5 align-items-center">

                                    <input type="text" class="form-control form-control-sm mr-2" id="searchTable"
                                        placeholder="Search permission..." style="width:150px">

                                </div>

                                <a href="{{ route('hak_akses.create') }}" class="btn btn-primary btn-sm">
                                    <i class="feather icon-plus"></i> Tambah Hak Akses
                                </a>

                            </div>

                        </div>

                        <div class="nav-tabs-top">

                            <div class="tab-content d-flex justify-content-center" style="width:100%">

                                <div class="tab-pane fade show active pb-5" style="width:95%">

                                    <div style="overflow-x:auto">

                                        <table class="table table-modern table-hover" id="table">

                                            <thead>

                                                <tr>

                                                    <th class="checkbox-col">
                                                        <input type="checkbox" id="checkAll">
                                                    </th>

                                                    <th>No</th>

                                                    <th>Nama Permission</th>

                                                    <th width="140">Action</th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                @foreach ($hakAkses as $index => $akses)
                                                    <tr>

                                                        <td class="checkbox-col">
                                                            <input type="checkbox" class="row-check">
                                                        </td>

                                                        <td>{{ $index + 1 }}</td>

                                                        <td>
                                                            <strong>{{ $akses->nama_permission }}</strong>
                                                        </td>

                                                        <td>

                                                            <a href="{{ route('hak_akses.edit', $akses->id) }}"
                                                                class="btn btn-sm btn-info action-btn">

                                                                <i class="feather icon-edit"></i>

                                                            </a>

                                                            <form id="delete-form-{{ $akses->id }}"
                                                                action="{{ route('hak_akses.destroy', $akses->id) }}"
                                                                method="POST" style="display:inline">

                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="button"
                                                                    onclick="confirmDelete({{ $akses->id }})"
                                                                    class="btn btn-sm btn-danger action-btn">

                                                                    <i class="feather icon-trash"></i>

                                                                </button>

                                                            </form>

                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

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
        /* CHECK ALL */

        document.getElementById('checkAll').addEventListener('click', function() {

            let checkboxes = document.querySelectorAll('.row-check');

            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });

        });


        /* SEARCH TABLE */

        document.getElementById('searchTable').addEventListener('keyup', function() {

            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#table tbody tr");

            rows.forEach(row => {

                let text = row.textContent.toLowerCase();

                row.style.display = text.includes(value) ? "" : "none";

            });

        });


        /* CONFIRM DELETE */

        function confirmDelete(id) {

            Swal.fire({
                title: "Are you sure?",
                text: "Hak akses akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {

                if (result.isConfirmed) {
                    document.getElementById("delete-form-" + id).submit();
                }

            });

        }


        /* AUTO HIDE SUCCESS ALERT */

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
