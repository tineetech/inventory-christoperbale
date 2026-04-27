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

        .table-danger td {
            background-color: #f8d7da !important;
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

                                <div class="form-group col-md-6">
                                    <label class="form-label">Scan Out</label>
                                    <select name="scan_out" class="form-control">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="pending" selected>Pending</option>
                                        <option value="done">Done</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label">Penjualan Draft ?</label>
                                    <select name="is_draft" value="no" class="form-control">
                                        <option value="no" selected>Tidak</option>
                                        <option value="yes">Ya</option>
                                    </select>
                                    <span class="text-muted">Penjualan draft disini jika YA dapat membuat penjualan namun
                                        tidak mengurangi stok.</span>
                                </div>

                            </div>

                            <hr>
                            <div class="mt-4">


                                <!-- BARCODE -->
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Import Cepat Via File Resi Tokped JNT (Optional)</label>
                                        <input type="file" id="file_import_tokped" class="form-control"
                                            accept=".jpg,.jpeg,.png,.pdf">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Import Cepat Via File Resi Shopee (Optional)</label>
                                        <input type="file" id="file_import_shopee" class="form-control"
                                            accept=".jpg,.jpeg,.png,.pdf">
                                    </div>

                                    <div class="form-group col-md-12">
                                        <button type="button" id="btn_import" class="btn btn-primary w-100 mb-5">IMPORT
                                            CEPAT</button>
                                    </div>

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
                                {{-- <div class="form-group row">

                                    <label class="col-md-2 col-form-label">
                                        Input Nomor Urut
                                    </label>

                                    <div class="col-md-10">

                                        <input type="text" id="transaksi_global" class="form-control"
                                            placeholder="Ketik nomor transaksi lalu tekan ENTER">

                                    </div>

                                </div> --}}

                                <hr>

                                <hr>

                                <!-- TABLE ITEM -->
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="tableItems">

                                        <thead class="thead-dark">

                                            <tr>

                                                <th>Nomor Resi</th>
                                                <th>Nomor Pesanan</th>
                                                <th>Nomor Urut</th>
                                                <th>SKU</th>
                                                <th>Nama Barang</th>
                                                <th>Stok</th>
                                                <th>Harga</th>

                                                <th width="120">Qty</th>


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

                                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">

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
                                " | Rp " + formatRupiah(p.harga_2),
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
            const draft = isDraftMode();

            if (!validateStock(product, draft)) return;

            if (items[product.id]) {
                let newQty = items[product.id].qty + qty;

                // Cek qty hanya kalau bukan draft
                if (!draft && newQty > items[product.id].stok) {
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

            let nomorUrut = Object.keys(items).length + 1;
            items[product.id] = {
                id: product.id,
                sku: product.sku,
                nama_barang: product.nama_barang,
                stok: product.stok.jumlah_stok,
                harga_2: product.harga_2,
                qty: qty,
                nomor_urut: nomorUrut
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
data-id="${id}"
value="${item.nomor_urut}">
</td>

<td>${item.sku}</td>

<td>${item.nama_barang}</td>

<td>${item.stok}</td>

<td>${formatRupiah(item.harga_2)}</td>

<td>
<input type="number"
class="form-control qty"
data-id="${id}"
value="${item.qty}"
min="1">
</td>

<td class="total">
${formatRupiah(item.harga_2 * item.qty)}
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
            let oldQty = items[id].qty;
            const draft = isDraftMode();

            if (!val || val <= 0) {
                input.val(1);
                Toast.fire({
                    icon: "error",
                    title: "Qty tidak boleh minus!"
                });
                items[id].qty = 1;
                updateRow(id);
                return;
            }

            // Blok qty > stok hanya kalau bukan draft
            if (!draft && val > stok) {
                Toast.fire({
                    icon: "error",
                    title: `Qty melebihi stok tersedia (stok: ${stok})`
                });
                input.val(oldQty);
                return;
            }

            // Draft tapi qty > stok: warning saja, tetap boleh
            if (draft && val > stok) {
                Toast.fire({
                    icon: "warning",
                    title: `Qty ${val} melebihi stok (${stok}), dicatat draft.`
                });
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

            let total = item.qty * item.harga_2;

            $('#row_' + id + ' .total').text(formatRupiah(total));

            calculateTotal();

        }

        $(document).on('click', '.remove', function() {

            let id = $(this).data('id');

            delete items[id];

            $('#row_' + id).remove();
            reOrderNomorUrut();

            calculateTotal();

        });

        function calculateTotal() {

            let total = 0;

            Object.values(items).forEach(i => {

                total += i.qty * i.harga_2;

            });

            $('#grand_total').val(formatRupiah(total));

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

        function reOrderNomorUrut() {

            let index = 1;

            $('#tableItems tbody tr').each(function() {

                let id = $(this).attr('id').replace('row_', '');

                items[id].nomor_urut = index;

                $(this).find('.nomor_transaksi').val(index);

                index++;

            });

        }

        function validateStock(product, isDraft = false) {
            let stok = product.stok.jumlah_stok;
            let min = product.stok_minimum ?? 1;

            if (!isDraft) {
                // Non-draft: stok 0 = block
                if (stok <= 0) {
                    Toast.fire({
                        icon: "error",
                        title: `SKU #${product.sku} Stok habis! Harus restock dulu.`
                    });
                    return false;
                }
                if (stok < min) {
                    Toast.fire({
                        icon: "error",
                        title: `SKU #${product.sku} Stok dibawah minimum! Harap restock.`
                    });
                    return false;
                }
            } else {
                // Draft: tetap warning tapi tidak block
                if (stok <= 0) {
                    Toast.fire({
                        icon: "warning",
                        title: `SKU #${product.sku} Stok habis, dicatat sebagai draft.`
                    });
                }
            }

            // Warning stok kritis (tampil di kedua mode)
            if (stok <= 5 && stok > 0) {
                Toast.fire({
                    icon: "warning",
                    title: `SKU #${product.sku} Stok kritis (${stok}).`
                });
            } else if (stok <= 10 && stok > 0) {
                Toast.fire({
                    icon: "warning",
                    title: `SKU #${product.sku} Stok mulai menipis (${stok}).`
                });
            }

            return true;
        }

        // Helper: cek apakah mode draft aktif
        function isDraftMode() {
            return $('select[name="is_draft"]').val() === 'yes';
        }

        $(document).ready(function() {

            $('#barcode_scan').focus();

        });


        $('form').on('submit', function(e) {
            const draft = isDraftMode();
            let valid = true;
            let errorMessages = [];

            // Validasi ulang semua item
            Object.values(items).forEach(item => {
                if (!draft && item.qty > item.stok) {
                    valid = false;
                    errorMessages.push(`SKU #${item.sku}: qty ${item.qty} melebihi stok ${item.stok}`);
                }
            });

            if (!valid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errorMessages.map(m => `<p>❌ ${m}</p>`).join(''),
                });
                return false;
            }

            // Cek tabel tidak kosong
            if (Object.keys(items).length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tambahkan minimal 1 barang!'
                });
                return false;
            }

            // Kumpulkan items untuk dikirim
            let result = [];
            $('#tableItems tbody tr').each(function() {
                let id = $(this).attr('id').replace('row_', '');
                result.push({
                    id: id,
                    qty: $(this).find('.qty').val(),
                    harga_2: items[id].harga_2,
                    nomor_resi: $(this).find('.nomor_resi').val(),
                    nomor_pesanan: $(this).find('.nomor_pesanan').val(),
                    nomor_transaksi: $(this).find('.nomor_transaksi').val()
                });
            });

            $('#items_input').val(JSON.stringify(result));

            let total = 0;
            Object.values(items).forEach(i => {
                total += i.qty * i.harga_2;
            });
            $('#total_harga_input').val(total);
        });


        // Kalau user ganti mode draft, update visual warning di tabel
        $('select[name="is_draft"]').on('change', function() {
            const draft = $(this).val() === 'yes';

            Object.values(items).forEach(item => {
                const row = $(`#row_${item.id}`);
                if (draft) {
                    row.removeClass('table-danger');
                } else if (item.qty > item.stok) {
                    // Highlight merah kalau qty > stok dan bukan draft
                    row.addClass('table-danger');
                    Toast.fire({
                        icon: 'warning',
                        title: `SKU #${item.sku}: qty melebihi stok!`
                    });
                }
            });
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


    <script>
        document.getElementById('btn_import').addEventListener('click', async function() {
            const fileTokped = document.getElementById('file_import_tokped').files[0];
            const fileShopee = document.getElementById('file_import_shopee').files[0];

            if (!fileTokped && !fileShopee) {
                Swal.fire('Oops!', 'Pilih minimal satu file untuk diimport.', 'warning');
                return;
            }

            const imports = [];
            if (fileTokped) imports.push({
                file: fileTokped,
                route: '/api/penjualan/import/tokped-jnt',
                inputId: 'file_import_tokped',
                mode: 'Tokped JNT'
            });
            if (fileShopee) imports.push({
                file: fileShopee,
                route: '/api/penjualan/import/shopee',
                inputId: 'file_import_shopee',
                mode: 'Shopee'
            });

            // Tampilkan loading swal — tidak ada timer, tutup manual setelah selesai
            Swal.fire({
                title: 'Sedang memproses...',
                html: 'Membaca file resi, mohon tunggu.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const results = [];

            for (const item of imports) {
                const formData = new FormData();
                formData.append('file', item.file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    Swal.update({
                        html: `Memproses file <b>${item.mode}</b>...`
                    });

                    const response = await fetch(item.route, {
                        method: 'POST',
                        body: formData,
                    });

                    const result = await response.json();
                    console.log(`[${item.mode}] Response:`, result);

                    results.push({
                        mode: item.mode,
                        data: result
                    });

                    // Reset input
                    document.getElementById(item.inputId).value = '';

                } catch (error) {
                    console.error(`[${item.mode}] Error:`, error);
                    results.push({
                        mode: item.mode,
                        error: error.message
                    });
                }
            }

            // Semua selesai — tutup loading, tampilkan hasil
            const sukses = results.filter(r => !r.error).length;
            const gagal = results.filter(r => r.error).length;

            Swal.fire({
                icon: gagal === 0 ? 'success' : 'warning',
                title: 'Selesai!',
                html: `
            ${sukses > 0 ? `<p>✅ ${sukses} file berhasil diproses</p>` : ''}
            ${gagal  > 0 ? `<p>❌ ${gagal} file gagal</p>` : ''}
        `,
            }).then(async () => {
                console.log('Semua hasil:', results);

                for (const result of results) {
                    if (result.error || !result.data?.result) continue;

                    const {
                        resi,
                        order_id,
                        skus,
                        items: ocrItems
                    } = result.data.result;

                    $(`#resi_global`).val(resi);
                    $(`#pesanan_global`).val(order_id);


                    // Loop semua SKU, cari produk, tambah ke tabel
                    for (let i = 0; i < skus.length; i++) {
                        const sku = skus[i];

                        try {
                            const response = await fetch(
                                `/api/product/search?q=${encodeURIComponent(sku)}`);
                            const products = await response.json();

                            if (!products || products.length === 0) {
                                Toast.fire({
                                    icon: 'warning',
                                    title: `SKU "${sku}" tidak ditemukan di database`
                                });
                                continue;
                            }

                            // Ambil produk pertama yang SKU-nya exact match, fallback ke index 0
                            const product = products.find(p => p.sku === sku) ?? products[0];

                            // Tambah ke tabel dengan qty dari OCR
                            const qty = ocrItems?.[i]?.qty ?? 1;
                            addItemQty(product, qty);

                            // Isi nomor resi & pesanan di baris yang baru ditambah
                            setTimeout(() => {
                                if (resi) {
                                    $(`#row_${product.id} .nomor_resi`).val(resi);
                                    items[product.id].nomor_resi = resi;
                                }
                                if (order_id) {
                                    $(`#row_${product.id} .nomor_pesanan`).val(order_id);
                                    items[product.id].nomor_pesanan = order_id;
                                }
                            }, 100); // timeout kecil supaya row sudah ke-render

                        } catch (err) {
                            console.error(`Gagal cari SKU ${sku}:`, err);
                            Toast.fire({
                                icon: 'error',
                                title: `Gagal mencari SKU "${sku}"`
                            });
                        }
                    }
                }

                // Kalau ada 2+ file, isi resi & pesanan global ke semua baris yang belum terisi
                // (opsional, kalau mau semua baris dapat resi yang sama)
            });
        });
    </script>
@endsection
