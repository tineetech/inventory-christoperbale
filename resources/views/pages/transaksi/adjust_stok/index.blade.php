@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Manajemen Stok</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item active">Manajemen Stok</li>
                </ol>
            </div>
            <div class="row">
                <!-- 1st row Start -->
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-md-12">

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

                            @if(session('error'))
                            <div class="card mb-4 border-danger">

                                <div class="card-body d-flex align-items-center justify-content-between">

                                    <div>

                                        <h5 class="mb-1 text-danger">
                                            <i class="feather icon-x-circle"></i> error
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

                        </div>

                        <div class="col-sm-12">
                            <div class="card mb-4">
                                <div style="border: none !important"
                                    class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-truck mr-2"></i> Data Manajemen Stok
                                    </h6>

                                    <div class="d-flex gap-5">

                                        <!-- Search -->
                                        <div class="d-flex mr-5 align-items-center">

                                            <input type="text" class="form-control form-control-sm mr-2" id="searchTable"
                                                placeholder="Search data riwayat stok..." style="width:150px">

                                        </div>
                                        <a href="{{ route('manage-stok.create') }}" class="btn btn-primary btn-sm">
                                            <i class="feather icon-plus"></i> Tambah Manajemen Stok
                                        </a>

                                    </div>
                                </div>
                                <div class="nav-tabs-top">
                                    <div class="tab-content d-flex justify-content-center " style="width: 100%">
                                        <div class="tab-pane fade show active pb-5" style="width: 95%" id="sale-stats">
                                            <div style="height: auto;overflow-x: auto" id="tab-table-1">
                                                <table class="table table-modern table-hover" id="table">
                                                    
<thead>
<tr>

<th width="30"></th>

<th>No</th>

<th>Kode Adjustment</th>

<th>Tanggal</th>

<th>Dibuat Oleh</th>

<th>Keterangan</th>

<th width="120">Action</th>

</tr>
</thead>


<tbody>

@foreach($adjustments as $index => $adj)

<tr class="main-row" data-id="{{ $adj->id }}">

<td>
<i class="feather icon-chevron-right"></i>
</td>

<td>{{ $index+1 }}</td>

<td>
<strong style="cursor:pointer;color:#00499b">
{{ $adj->kode_adjust }}
</strong>
</td>

<td>{{ date('d M Y', strtotime($adj->tanggal)) }}</td>

<td>{{ $adj->user->nama ?? '-' }}</td>

<td>{{ $adj->keterangan }}</td>

<td>

<a href="{{ route('adjust_stok.edit',$adj->id) }}"
class="btn btn-sm btn-warning">
<i class="feather icon-edit"></i>
</a>

<form action="{{ route('adjust_stok.destroy',$adj->id) }}"
method="POST"
style="display:inline">

@csrf
@method('DELETE')

<button class="btn btn-sm btn-danger">
<i class="feather icon-trash"></i>
</button>

</form>

</td>

</tr>


{{-- DETAIL ROW --}}

<tr class="detail-row" id="detail-{{ $adj->id }}" style="display:none;background:#f9f9f9">

<td colspan="7">

<div class="p-3">

<table class="table table-sm table-bordered mb-0">

<thead class="thead-light">

<tr>

<th>SKU</th>

<th>Nama Barang</th>

<th>Qty Sistem</th>

<th>Qty Fisik</th>

<th>Selisih</th>

</tr>

</thead>

<tbody>

@foreach($adj->detail as $d)

<tr>

<td>{{ $d->barang->sku }}</td>

<td>{{ $d->barang->nama_barang }}</td>

<td>{{ $d->qty_sistem }}</td>

<td>{{ $d->qty_fisik }}</td>

<td>

@if($d->selisih > 0)

<span class="text-success">
+{{ $d->selisih }}
</span>

@elseif($d->selisih < 0)

<span class="text-danger">
{{ $d->selisih }}
</span>

@else

<span class="text-muted">0</span>

@endif

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</td>

</tr>

@endforeach

</tbody>
                                                </table>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center px-3 py-2 border-top">

                                                <!-- Show Entries -->
                                                <div class="d-flex align-items-center mr-5">

                                                    <span class="mr-2 text-muted small">Show</span>

                                                    <select class="form-control form-control-sm" id="entriesSelect"
                                                        style="width:80px">

                                                        <option value="10" selected>10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>

                                                    </select>

                                                    <span class="ml-2 text-muted small">entries</span>

                                                </div>
                                                <!-- Info Entries -->
                                                <div class="text-muted small" id="tableInfo">
                                                    Showing <strong>1</strong> to <strong>10</strong> of
                                                    <strong>100</strong> entries
                                                </div>

                                                <!-- Pagination -->
                                                <nav>
                                                    <ul class="pagination pagination-sm mb-0" id="pagination">

                                                        <li class="page-item disabled">
                                                            <a class="page-link" href="#">
                                                                <i class="feather icon-chevrons-left"></i>
                                                            </a>
                                                        </li>

                                                        <li class="page-item active">
                                                            <a class="page-link" href="#">1</a>
                                                        </li>

                                                        <li class="page-item">
                                                            <a class="page-link" href="#">2</a>
                                                        </li>

                                                        <li class="page-item">
                                                            <a class="page-link" href="#">3</a>
                                                        </li>

                                                        <li class="page-item">
                                                            <a class="page-link" href="#">
                                                                <i class="feather icon-chevrons-right"></i>
                                                            </a>
                                                        </li>

                                                    </ul>
                                                </nav>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 1st row Start -->
            </div>

        </div>
        <!-- [ content ] End -->
        @include('components.footer')
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('checkAll').addEventListener('click', function() {

            let checkboxes = document.querySelectorAll('.row-check');

            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });

        });


        // let rows = document.querySelectorAll("#table tbody tr");
        let rows = document.querySelectorAll("#table tbody tr.main-row");
        let entriesSelect = document.getElementById("entriesSelect");
        let pagination = document.getElementById("pagination");
        let tableInfo = document.getElementById("tableInfo");

        let currentPage = 1;
        let rowsPerPage = parseInt(entriesSelect.value);
        let filteredRows = [...rows];

        function displayTable() {

            rowsPerPage = parseInt(entriesSelect.value);

            let start = (currentPage - 1) * rowsPerPage;
            let end = start + rowsPerPage;

            rows.forEach(row => row.style.display = "none");

            filteredRows.slice(start, end).forEach(row => {
                row.style.display = "";
            });

            updateInfo();
        }

        function setupPagination() {

            pagination.innerHTML = "";

            let pageCount = Math.ceil(filteredRows.length / rowsPerPage);

            for (let i = 1; i <= pageCount; i++) {

                let li = document.createElement("li");
                li.classList.add("page-item");

                if (i === currentPage) {
                    li.classList.add("active");
                }

                let a = document.createElement("a");
                a.classList.add("page-link");
                a.href = "#";
                a.innerText = i;

                a.addEventListener("click", function(e) {

                    e.preventDefault();

                    currentPage = i;

                    displayTable();
                    setupPagination();

                });

                li.appendChild(a);
                pagination.appendChild(li);
            }
        }

        function updateInfo() {

            let start = (currentPage - 1) * rowsPerPage + 1;
            let end = Math.min(currentPage * rowsPerPage, filteredRows.length);

            if (filteredRows.length === 0) {
                start = 0;
            }

            tableInfo.innerHTML =
                `Showing ${start} to ${end} of ${filteredRows.length} entries`;

        }

        entriesSelect.addEventListener("change", function() {

            currentPage = 1;

            displayTable();
            setupPagination();

        });

        displayTable();
        setupPagination();

        document.getElementById('checkAll').addEventListener('click', function() {

            let checkboxes = document.querySelectorAll('.row-check');

            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });

        });


        /* SEARCH TABLE */
        document.getElementById('searchTable').addEventListener('keyup', function() {

            let value = this.value.toLowerCase();

            filteredRows = [...rows].filter(row => {
                return row.textContent.toLowerCase().includes(value);
            });

            currentPage = 1;

            displayTable();
            setupPagination();

        });


        /* SHOW ENTRIES */

        let selectEntries = document.getElementById("entriesSelect");
        // let tableRows = document.querySelectorAll("#table tbody tr");
        let tableRows = document.querySelectorAll("#table tbody tr.main-row");

        function showEntries() {

            let limit = parseInt(selectEntries.value);

            tableRows.forEach((row, index) => {

                row.style.display = index < limit ? "" : "none";

            });

        }

        document.querySelectorAll(".detail-row").forEach(row => {
            row.style.display = "none";
        });

        selectEntries.addEventListener("change", showEntries);

        /* default 10 */

        showEntries();

        function confirmDelete(id) {

            Swal.fire({
                title: "Are you sure?",
                text: "Supplier akan dihapus!",
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

        setTimeout(function() {

            let alertCard = document.querySelector('.border-success');

            if (alertCard) {
                alertCard.style.transition = "0.5s";
                alertCard.style.opacity = "0";
                setTimeout(() => alertCard.remove(), 500);
            }

        }, 4000);


        let currentSortColumn = null;
        let currentSortDirection = "asc";

        function sortTable(columnIndex) {

            if (currentSortColumn === columnIndex) {
                currentSortDirection = currentSortDirection === "asc" ? "desc" : "asc";
            } else {
                currentSortColumn = columnIndex;
                currentSortDirection = "asc";
            }

            filteredRows.sort((a, b) => {

                let aText = a.children[columnIndex].innerText.toLowerCase();
                let bText = b.children[columnIndex].innerText.toLowerCase();

                if (!isNaN(aText) && !isNaN(bText)) {
                    return currentSortDirection === "asc" ?
                        aText - bText :
                        bText - aText;
                }

                return currentSortDirection === "asc" ?
                    aText.localeCompare(bText) :
                    bText.localeCompare(aText);

            });

            let tbody = document.querySelector("#table tbody");

            filteredRows.forEach(row => {
                tbody.appendChild(row);
            });

            currentPage = 1;

            displayTable();
            setupPagination();
        }
        document.querySelectorAll(".sortable").forEach(header => {

            header.addEventListener("click", function() {

                let columnIndex = this.getAttribute("data-column");

                sortTable(parseInt(columnIndex));

            });

        });
        document.querySelectorAll(".main-row").forEach(row => {

            row.addEventListener("click", function(e) {

                if (e.target.closest("button") || e.target.closest("a")) return;

                let id = this.getAttribute("data-id");

                let detailRow = document.getElementById("detail-" + id);

                if (detailRow.style.display === "table-row") {

                    detailRow.style.display = "none";

                } else {

                    detailRow.style.display = "table-row";

                }

            });

        });
    </script>
@endsection
