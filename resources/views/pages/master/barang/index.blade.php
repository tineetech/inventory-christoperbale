@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Barang</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Barang</li>
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

                            @if (session('error'))
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
                        {{-- <div class="col-md-3">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="">
                                            <h2 class="mb-2"> 256 </h2>
                                            <p class="text-muted mb-0"><span class="badge badge-primary">Supplier</span>
                                                Today</p>
                                        </div>
                                        <div class="lnr lnr-leaf display-4 text-primary"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="">
                                            <h2 class="mb-2">8451</h2>
                                            <p class="text-muted mb-0"><span class="badge badge-success">20%</span> Stock
                                            </p>
                                        </div>
                                        <div class="lnr lnr-chart-bars display-4 text-success"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="">
                                            <h2 class="mb-2"> 31% <small></small></h2>
                                            <p class="text-muted mb-0">New <span class="badge badge-danger">20%</span>
                                                Customer</p>
                                        </div>
                                        <div class="lnr lnr-rocket display-4 text-danger"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="">
                                            <h2 class="mb-2">158</h2>
                                            <p class="text-muted mb-0"><span class="badge badge-warning">$143.45</span>
                                                Profit</p>
                                        </div>
                                        <div class="lnr lnr-cart display-4 text-warning"></div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <div class="col-sm-12">
                            <div class="card mb-4">
                                <div style="border: none !important"
                                    class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="card-header-title mb-0">
                                        <i class="feather icon-truck mr-2"></i> Data Barang
                                    </h6>

                                    <div class="d-flex gap-5">

                                        <!-- Search -->
                                        <div class="d-flex mr-5 align-items-center">

                                            <input type="text" class="form-control form-control-sm mr-2" id="searchTable"
                                                placeholder="Search barang..." style="width:150px">

                                        </div>
                                        <a href="#" onclick="showImportModal()" class="btn btn-sm mr-3 text-white"
                                            style="background: linear-gradient(135deg, #667eea, #764ba2); border: none; box-shadow: 0 6px 14px rgba(102, 126, 234, 0.25);">
                                            <i class="feather icon-download"></i> Import Barang
                                        </a>
                                        <a href="{{ route('barang.create') }}" class="btn btn-sm text-white"
                                            style="background: linear-gradient(135deg, #ff8a00, #ff5b00); border: none; box-shadow: 0 6px 14px rgba(255, 107, 0, 0.25);">
                                            <i class="feather icon-plus"></i> Tambah Barang
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

                                                            <th class="checkbox-col">
                                                                <input type="checkbox" id="checkAll">
                                                            </th>

                                                            <th class="sortable" data-column="1">No <i
                                                                    class="feather icon-chevrons-up sort-icon"></i></th>

                                                            <th class="sortable" data-column="2">
                                                                SKU <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>

                                                            <th class="sortable" data-column="3">
                                                                Nama Barang <i
                                                                    class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>

                                                            <th class="sortable" data-column="4">
                                                                Satuan <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>

                                                            <th class="sortable" data-column="5">
                                                                Harga Beli <i
                                                                    class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>

                                                            <th class="sortable" data-column="6">
                                                                Harga Jual <i
                                                                    class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>

                                                            <th class="sortable" data-column="7">
                                                                Stok <i class="feather icon-chevrons-up sort-icon"></i>
                                                            </th>

                                                            <th>
                                                                Keterangan
                                                            </th>
                                                            <th width="140">Action</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach ($barang as $index => $sup)
                                                            <tr>

                                                                <td class="checkbox-col">
                                                                    <input type="checkbox" class="row-check">
                                                                </td>

                                                                <td>{{ $index + 1 }}</td>

                                                                {{-- <td>
                                                                    {{ $sup->sku }}
                                                                </td> --}}
                                                                <td>
                                                                    <div class="d-flex flex-column  align-items-center"
                                                                        style="padding-block: 20px">
                                                                        {{-- Barcode tanpa teks --}}
                                                                        <div style="transform: scaleY(1.5)">
                                                                            {!! DNS1D::getBarcodeHTML($sup->sku, 'C128', 2, 25) !!}
                                                                        </div>
                                                                        {{-- Teks SKU dari blade --}}
                                                                        <small class="mt-1"
                                                                            style="font-size:15px; letter-spacing:1px">
                                                                            {{ $sup->sku }}
                                                                        </small>
                                                                    </div>
                                                                </td>

                                                                <td>
                                                                    <strong>{{ $sup->nama_barang }}</strong>
                                                                </td>

                                                                <td>
                                                                    {{ $sup->satuan->nama_satuan }}
                                                                </td>

                                                                <td>
                                                                    Rp {{ number_format($sup->harga_1, 0, ',', '.') }}
                                                                </td>

                                                                <td>
                                                                    Rp {{ number_format($sup->harga_2, 0, ',', '.') }}
                                                                </td>

                                                                <td>
                                                                    {{ $sup->stok->jumlah_stok }}
                                                                </td>

                                                                <td>
                                                                    {{ $sup->keterangan }}
                                                                </td>

                                                                <td>


                                                                    {{-- SESUDAH --}}
                                                                    <a href="{{ route('barang.barcode.download', $sup->id) }}"
                                                                        class="btn btn-sm btn-dark action-btn"
                                                                        target="_blank">
                                                                        <i class="feather icon-download"></i>
                                                                        <span>Barcode</span>
                                                                    </a>

                                                                    <a href="{{ route('barang.edit', $sup->id) }}"
                                                                        class="btn btn-sm btn-info action-btn">
                                                                        <i class="feather icon-edit"></i>
                                                                    </a>

                                                                    <form id="delete-form-{{ $sup->id }}"
                                                                        action="{{ route('barang.destroy', $sup->id) }}"
                                                                        method="POST" style="display:inline">

                                                                        @csrf
                                                                        @method('DELETE')

                                                                        <button type="button"
                                                                            onclick="confirmDelete({{ $sup->id }})"
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


        let rows = document.querySelectorAll("#table tbody tr");
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
        let tableRows = document.querySelectorAll("#table tbody tr");

        function showEntries() {

            let limit = parseInt(selectEntries.value);

            tableRows.forEach((row, index) => {

                row.style.display = index < limit ? "" : "none";

            });

        }

        selectEntries.addEventListener("change", showEntries);

        /* default 10 */

        showEntries();

        function confirmDelete(id) {

            Swal.fire({
                title: "Are you sure?",
                text: "Data akan dihapus!",
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
        function showImportModal() {
    Swal.fire({
        title: '<strong>Import Barang</strong>',
        icon: 'info',
        html: `
            <div class="text-left">

                <a href="/api/barang/import/template" class="btn btn-outline-secondary btn-block mb-4">
                    <i class="feather icon-download"></i> Download Template
                </a>

                <div class="form-group mb-3">
                    <label class="font-weight-bold">Pilih File</label>
                    <input type="file" id="import_file" class="form-control" accept=".xlsx,.xls,.csv">
                    <small class="text-muted">Format: .xlsx, .xls, .csv</small>
                </div>
            </div>
        `,
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: '<i class="feather icon-upload"></i> Import',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#667eea',
        focusConfirm: false,
        preConfirm: async () => {
            const file = document.getElementById('import_file').files[0];

            if (!file) {
                Swal.showValidationMessage('Pilih file terlebih dahulu!');
                return false;
            }

            const ext = file.name.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls', 'csv'].includes(ext)) {
                Swal.showValidationMessage('Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
                return false;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            Swal.showLoading();

            try {
                const response = await fetch('/api/barang/import', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok) {
                    Swal.showValidationMessage(result.message ?? 'Import gagal.');
                    return false;
                }

                return result;

            } catch (err) {
                Swal.showValidationMessage('Terjadi kesalahan: ' + err.message);
                return false;
            }
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const { message, imported, skipped, details } = result.value;

            // Bangun tabel detail skip kalau ada
            let detailHtml = '';
            if (details && details.length > 0) {
                const rows = details.map(d => `
                    <tr>
                        <td class="text-left px-2"><code>${d.sku}</code></td>
                        <td class="text-left px-2 text-danger">${d.alasan}</td>
                    </tr>
                `).join('');

                detailHtml = `
                    <div class="mt-3 text-left">
                        <p class="font-weight-bold mb-1">Detail baris dilewati:</p>
                        <div style="max-height:200px; overflow-y:auto; border:1px solid #dee2e6; border-radius:4px">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left px-2">SKU</th>
                                        <th class="text-left px-2">Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                    </div>
                `;
            }

            Swal.fire({
                icon: skipped > 0 ? 'warning' : 'success',
                title: 'Import Selesai!',
                html: `
                    <p class="mb-2">✅ Berhasil diimport: <strong>${imported}</strong> barang</p>
                    ${skipped > 0 ? `<p class="mb-2 text-warning">⚠️ Dilewati: <strong>${skipped}</strong> baris</p>` : ''}
                    ${detailHtml}
                `,
                width: details?.length > 0 ? '600px' : '400px',
            }).then(() => location.reload());
        }
    });
}
    </script>
@endsection
