@extends('layouts.main')

@section('style')
    <style>
        .flash-row {
            animation: flashBg .5s;
        }

        @keyframes flashBg {
            0% {
                background: #d4edda;
            }

            100% {
                background: transparent;
            }
        }
    </style>
@endsection
@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Penjualan</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-md-12">
                    {{-- {{ dd(Auth::guard('pengguna')->user()->id) }} --}}

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

                </div>

                <div class="card col-lg-12 mb-4">

                    <h6 class="card-header">
                        <i class="feather icon-shopping-cart mr-2"></i>
                        Formulir Transaksi Penjualan
                    </h6>

                    <div class="card-body">

                        <form action="{{ route('penjualan.store') }}" method="POST">
                            @csrf

                            <div class="form-row">

                                <input type="hidden" name="items" id="items_input">
                                <input type="hidden" name="total_harga" id="total_harga_input">

                                {{-- DROPSHIPPER --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Dropshipper</label>

                                    <select name="dropshipper_id" class="form-control" required>

                                        <option value="">-- Pilih Dropshipper --</option>

                                        @foreach ($dropshippers as $ds)
                                            <option value="{{ $ds->id }}">
                                                {{ $ds->nama }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                {{-- TANGGAL --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Tanggal Penjualan</label>

                                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}"
                                        required>

                                </div>

                                {{-- KODE PENJUALAN --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Kode Penjualan</label>

                                    <input type="text" name="kode_penjualan" class="form-control"
                                        value="{{ $kode }}">

                                </div>

                                {{-- KETERANGAN --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Keterangan</label>

                                    <textarea name="keterangan" class="form-control" rows="2"></textarea>

                                </div>

                            </div>

                            <hr>
                            <div class="mt-4">

                                <hr>

                                <!-- BARCODE -->
                                <div class="row">
                                    <div class="form-group col-md-6">

                                        <label class="">
                                            Scan single Barcode (F8)
                                        </label>

                                        <div class="">

                                            <input type="text" id="barcode_scan" class="form-control"
                                                placeholder="Scan barcode / SKU">

                                        </div>

                                    </div>
                                    <div class="form-group col-md-6">

                                        <label class="">
                                            Scan 10 Barcode dalam 1x scan (F7)
                                        </label>

                                        <div class="">

                                            <input type="text" id="barcode_scan_10" class="form-control"
                                                placeholder="Scan barcode / SKU">

                                        </div>

                                    </div>

                                </div>

                                <hr>

                                <div class="form-group row">

                                    <label class="col-md-2 col-form-label">
                                        Cari Barang (F9)
                                    </label>

                                    <div class="col-md-10">

                                        <select id="product_select" class="form-control" multiple></select>

                                    </div>

                                </div>
                                {{-- INPUT RESI GLOBAL --}}
                                <div class="form-group row">

                                    <label class="col-md-2 col-form-label">
                                        Input Nomor Resi
                                    </label>

                                    <div class="col-md-10">

                                        <input type="text" id="resi_global" class="form-control"
                                            placeholder="Ketik nomor resi lalu tekan ENTER">

                                    </div>

                                </div>

                                {{-- INPUT NOMOR PESANAN GLOBAL --}}
                                <div class="form-group row">

                                    <label class="col-md-2 col-form-label">
                                        Input Nomor Pesanan
                                    </label>

                                    <div class="col-md-10">

                                        <input type="text" id="pesanan_global" class="form-control"
                                            placeholder="Ketik nomor pesanan lalu tekan ENTER">

                                    </div>

                                </div>

                                {{-- INPUT NOMOR TRANSAKSI GLOBAL --}}
                                <div class="form-group row">

                                    <label class="col-md-2 col-form-label">
                                        Input Nomor Transaksi
                                    </label>

                                    <div class="col-md-10">

                                        <input type="text" id="transaksi_global" class="form-control"
                                            placeholder="Ketik nomor transaksi lalu tekan ENTER">

                                    </div>

                                </div>

                                <hr>

                                <hr>

                                <!-- TABLE ITEM -->
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="tableItems">

                                        <thead class="thead-dark">

                                            <tr>

                                                <th>SKU</th>
                                                <th>Nama Barang</th>
                                                <th>Stok</th>
                                                <th>Harga</th>

                                                <th width="120">Qty</th>

                                                <th>Nomor Resi</th>
                                                <th>Nomor Pesanan</th>
                                                <th>Nomor Transaksi</th>

                                                <th>Subtotal</th>
                                                <th width="60"></th>

                                            </tr>

                                        </thead>

                                        <tbody></tbody>

                                    </table>

                                </div>

                                <!-- TOTAL -->

                                <div class="row mt-3">

                                    <div class="col-md-3 ml-auto">

                                        <div class="input-group">

                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Total</span>
                                            </div>

                                            <input type="text" id="grand_total" class="form-control" readonly
                                                value="0">

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="d-flex justify-content-between">

                                <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">

                                    <i class="feather icon-arrow-left"></i>
                                    Kembali

                                </a>

                                <button type="submit" class="btn btn-primary">

                                    <i class="feather icon-save"></i>
                                    Simpan Penjualan

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

                if (newQty > items[product.id].stok) {

                    Toast.fire({
                        icon: "error",
                        title: "Qty melebihi stok tersedia"
                    });

                    return;
                }

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
            addItemQty(product, 1);
        }

        function flashRow(id) {

            let row = $('#row_' + id);

            row.addClass('flash-row');

            setTimeout(() => {
                row.removeClass('flash-row');
            }, 500);

        }

        function renderRow(id) {

            let item = items[id];

            let row = `
<tr id="row_${id}">

<td>${item.sku}</td>

<td>${item.nama_barang}</td>

<td>${item.stok}</td>

<td>${formatRupiah(item.harga_1)}</td>

<td>
<input type="number"
class="form-control qty"
data-id="${id}"
value="${item.qty}"
min="1"
max="${item.stok}">
</td>

<td>
<input type="text"
class="form-control nomor_resi"
data-id="${id}">
</td>

<td>
<input type="text"
class="form-control nomor_pesanan"
data-id="${id}">
</td>

<td>
<input type="text"
class="form-control nomor_transaksi"
data-id="${id}">
</td>

<td class="total">
${formatRupiah(item.harga_1 * item.qty)}
</td>

<td>
<button class="btn btn-danger btn-sm remove"
data-id="${id}">X</button>
</td>

</tr>
`;

            $('#tableItems tbody').append(row);

            calculateTotal();

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

            if (val > stok) {

                Toast.fire({
                    icon: "error",
                    title: "Qty melebihi stok tersedia"
                });

                // kembalikan ke qty sebelumnya
                input.val(oldQty);

                return;
            }

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

            let result = [];

            $('#tableItems tbody tr').each(function() {

                let id = $(this).attr('id').replace('row_', '');

                result.push({

                    id: id,
                    qty: $(this).find('.qty').val(),
                    harga_1: items[id].harga_1,
                    nomor_resi: $(this).find('.nomor_resi').val(),
                    nomor_pesanan: $(this).find('.nomor_pesanan').val(),
                    nomor_transaksi: $(this).find('.nomor_transaksi').val()

                });

            });

            $('#items_input').val(JSON.stringify(result));

            let total = 0;

            Object.values(items).forEach(i => {
                total += i.qty * i.harga_1;
            });

            $('#total_harga_input').val(total);

        });

        $('#resi_global').on('keydown', function(e) {

            if (e.key === "Enter") {

                e.preventDefault();

                let resi = $(this).val();

                if (!resi) return;

                $('.nomor_resi').val(resi);

                $(this).val('');

            }

        });

        // GLOBAL NOMOR PESANAN
        $('#pesanan_global').on('keydown', function(e) {

            if (e.key === "Enter") {

                e.preventDefault();

                let pesanan = $(this).val();

                if (!pesanan) return;

                $('.nomor_pesanan').val(pesanan);

                $(this).val('');

            }

        });

        // GLOBAL NOMOR TRANSAKSI
        $('#transaksi_global').on('keydown', function(e) {

            if (e.key === "Enter") {

                e.preventDefault();

                let trx = $(this).val();

                if (!trx) return;

                $('.nomor_transaksi').val(trx);

                $(this).val('');

            }

        });
    </script>
@endsection
