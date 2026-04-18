@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Adjustment Stok</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('manage-stok.index') }}">Manajemen Stok</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

            
            <div class="row">
                <div class="col-md-12">
                    

                        @if(session('error'))
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
                </div>
            </div>

            <div class="card">

                <h6 class="card-header">
                    <i class="feather icon-sliders mr-2"></i>
                    Form Adjustment Stok
                </h6>

                <div class="card-body">

                    <form action="{{ route('manage-stok.store') }}" method="POST">
                        @csrf

                        <input type="hidden" id="items_input" name="items">

                        <div class="row">

                            <div class="form-group col-md-4">
                                <label>Kode Adjustment</label>
                                <input type="text" name="kode_adjust" class="form-control" value="{{ $kode }}"
                                    readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Keterangan</label>
                                <input type="text" name="keterangan" class="form-control">
                            </div>

                        </div>

                        <hr>

                        <div class="row">

                            <div class="form-group col-md-6">

                                <label>Scan Barcode (F8)</label>

                                <input type="text" id="barcode_scan" class="form-control" placeholder="Scan SKU">

                            </div>

                            <div class="form-group col-md-6">

                                <label>Cari Barang (F9)</label>

                                <select id="product_select" class="form-control"></select>

                            </div>

                        </div>

                        <hr>

                        <div class="table-responsive">

                            <table class="table table-bordered" id="tableItems">

                                <thead class="thead-dark">

                                    <tr>
                                        <th>SKU</th>
                                        <th>Nama Barang</th>
                                        <th>Stok Sistem</th>
                                        <th width="140">Stok Fisik</th>
                                        <th width="120">Selisih</th>
                                        <th width="60"></th>
                                    </tr>

                                </thead>

                                <tbody></tbody>

                            </table>

                        </div>

                        <div class="d-flex justify-content-between mt-4">

                            <a href="{{ route('manage-stok.index') }}" class="btn btn-secondary">
                                Kembali
                            </a>

                            <button class="btn btn-primary">
                                Simpan Adjustment
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>
        @include('components.footer')
    </div>
@endsection
@section('scripts')
    <script>
        let items = {};
        $('#product_select').select2({

            placeholder: "Cari SKU / Nama Barang",
            multiple: true,
            width: '100%',
            closeOnSelect: false, // ⭐ ini yang bikin dropdown tetap terbuka

            ajax: {
                url: "/api/product/search",
                dataType: "json",
                delay: 150,
                cache: true,

                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },

                processResults: function(data) {
                    return {
                        results: data.map(p => ({
                            id: p.id,
                            text: "#" + p.sku + " - " + p.nama_barang +
                                " | Stok: " + p.stok.jumlah_stok +
                                " | Rp " + formatRupiah(p.harga_1),
                            product: p
                        }))
                    };
                }
            }

        });
        $('#product_select').on('select2:select', function(e) {

            let product = e.params.data.product;

            addItem(product);

            // hapus dari select supaya bisa dipilih lagi
            let selected = $(this).val();
            selected.pop();

            $(this).val(selected).trigger('change');

        });


        function addItemQty(product, qty) {

            if (!validateStock(product)) return;

            if (items[product.id]) {

                let newQty = items[product.id].qty + qty;

                // if (newQty > items[product.id].stok) {

                //     Toast.fire({
                //         icon: "error",
                //         title: "Qty melebihi stok tersedia"
                //     });

                //     return;
                // }

                items[product.id].qty = newQty;

                $('#row_' + product.id + ' .qty').val(newQty);

                updateRow(product.id);

                flashRow(product.id);

                return;
            }

            items[product.id] = {
                id: product.id,
                sku: product.sku,
                nama_barang: product.nama_barang,
                stok: product.stok.jumlah_stok,
                harga_1: product.harga_1,
                qty: qty
            };

            renderRow(product.id);
        }

        function addItem(product) {

            if (items[product.id]) return

            items[product.id] = {

                id: product.id,
                sku: product.sku,
                nama_barang: product.nama_barang,
                stok_sistem: product.stok.jumlah_stok,
                qty_fisik: product.stok.jumlah_stok,
                selisih: 0

            }

            renderRow(product.id)

        }

        function flashRow(id) {

            let row = $('#row_' + id);

            row.addClass('flash-row');

            setTimeout(() => {
                row.removeClass('flash-row');
            }, 500);

        }



        function renderRow(id) {

            let i = items[id]

            let row = `

<tr id="row_${id}">

<td>${i.sku}</td>

<td>${i.nama_barang}</td>

<td class="stok_sistem">${i.stok_sistem}</td>

<td>
<input type="number"
class="form-control qty_fisik"
data-id="${id}"
value="${i.qty_fisik}">
</td>

<td class="selisih text-bold">0</td>

<td>
<button class="btn btn-danger btn-sm remove" data-id="${id}">X</button>
</td>

</tr>

`

            $('#tableItems tbody').append(row);

            setTimeout(() => {
                $('#row_' + id).removeClass('flash-row');
            }, 1000);

            // calculateTotal();

        }


        $(document).on('change', '.qty', function() {

            let id = $(this).data('id');
            let input = $(this);

            let val = parseInt(input.val());
            let stok = items[id].stok;
            let oldQty = items[id].qty; // simpan qty sebelumnya

            if (!val || val <= 0) {
                val = 1;
                input.val(1)
                Toast.fire({
                    icon: "error",
                    title: "Qty tidak boleh mines !"
                });
            }

            // if (val > stok) {

            //     Toast.fire({
            //         icon: "error",
            //         title: "Qty melebihi stok tersedia"
            //     });

            //     // kembalikan ke qty sebelumnya
            //     input.val(oldQty);

            //     return;
            // }

            items[id].qty = val;

            updateRow(id);

        });

        $(document).on('keydown', '.qty', function(e) {

            if (e.key === "Enter") {
                e.preventDefault();
                $(this).trigger('change');
            }

        });

        function updateRow(id) {

            let item = items[id];

            let total = item.qty * item.harga_1;

            $('#row_' + id + ' .total').text(formatRupiah(total));

            calculateTotal();

        }

        $(document).on('click', '.remove', function() {

            let id = $(this).data('id');

            delete items[id];

            $('#row_' + id).remove();

            calculateTotal();

        });

        function calculateTotal() {

            let total = 0;

            Object.values(items).forEach(i => {

                total += i.qty * i.harga_1;

            });

            $('#grand_total').val(total);

        }

        $(document).on('input change', '.qty_fisik', function () {

    let id = $(this).data('id');
    let input = $(this);

    let raw = input.val();
    let fisik = parseInt(raw);

    let sistem = items[id].stok_sistem;

    // HANDLE KOSONG / NaN
    if (raw === "" || isNaN(fisik)) {
        fisik = 0;
        input.val(0);
    }

    // HANDLE MINUS
    if (fisik < 0) {

        fisik = 0;
        input.val(0);

        Toast.fire({
            icon: "error",
            title: "Qty fisik tidak boleh minus!"
        });

    }

    // HITUNG SELISIH SETELAH VALIDASI
    let selisih = fisik - sistem;

    // UPDATE DATA
    items[id].qty_fisik = fisik;
    items[id].selisih = selisih;

    let cell = $('#row_' + id + ' .selisih');

    if (selisih > 0) {

        cell.text("+" + selisih);
        cell.removeClass().addClass('selisih text-success font-weight-bold');

    } 
    else if (selisih < 0) {

        cell.text(selisih);
        cell.removeClass().addClass('selisih text-danger font-weight-bold');

    } 
    else {

        cell.text("0");
        cell.removeClass().addClass('selisih text-muted');

    }

});
$(document).on('keydown', '.qty_fisik', function(e){

    if(e.key === "Enter"){

        e.preventDefault();
        $(this).trigger('change');

    }

});


        $('#barcode_scan').on('keydown', function(e) {

            if (e.key === "Enter") {

                e.preventDefault(); // mencegah form submit

                let sku = $(this).val();

                if (!sku) return;

                $.get('/api/product/barcode/' + sku, function(product) {

                    addItem(product);

                });

                $('#barcode_scan').val('').focus();

            }

        });

        let barcodeTimer10;

        $('#barcode_scan_10').on('input', function() {

            clearTimeout(barcodeTimer10);

            barcodeTimer10 = setTimeout(() => {

                let sku = $(this).val();

                if (!sku) return;

                $.get('/api/product/barcode/' + sku, function(product) {

                    addItemQty(product, 10);

                });

                $('#barcode_scan_10').val('').focus();

            }, 200);

        });

        let barcodeTimer;

        $('#barcode_scan').on('input', function() {

            clearTimeout(barcodeTimer);

            barcodeTimer = setTimeout(() => {

                let sku = $(this).val();

                if (!sku) return;

                $.get('/api/product/barcode/' + sku, function(product) {

                    addItem(product);

                });

                $('#barcode_scan').val('');

            }, 200);

        });
        document.addEventListener("keydown", function(e) {

            if (e.key === "F8") {
                e.preventDefault();
                $('#barcode_scan').focus();
            }

            if (e.key === "F7") {
                e.preventDefault();
                $('#barcode_scan_10').focus();
            }

            if (e.key === "F9") {
                e.preventDefault();
                $('#product_select').select2('open');
            }

        });

        function validateStock(product) {

            let stok = product.stok.jumlah_stok;
            let min = product.stok_minimum ?? 1;

            if (stok <= 0) {
                Toast.fire({
                    icon: "error",
                    title: "Produk SKU #" + product.sku + " Stok habis! Barang harus direstock."
                });
                return false;
            }

            if (stok < min) {
                Toast.fire({
                    icon: "error",
                    title: "Produk SKU #" + product.sku + " Stok dibawah minimum! Harap restock."
                });
                return false;
            }

            if (stok <= 5) {
                Toast.fire({
                    icon: "error",
                    title: "Produk SKU #" + product.sku + " Stok kritis! Segera lakukan restock."
                });
            } else if (stok <= 10) {
                Toast.fire({
                    icon: "warning",
                    title: "Produk SKU #" + product.sku + " Stok mulai menipis."
                });
            }

            return true;
        }
        $(document).ready(function() {

            $('#barcode_scan').focus();

        });
        $('form').on('submit', function() {

            $('#items_input').val(JSON.stringify(items));

            let total = 0;

            Object.values(items).forEach(i => {
                total += i.qty * i.harga_1;
            });

            $('#total_harga_input').val(total);

        });
    </script>
@endsection
