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
                    <li class="breadcrumb-item active">Edit</li>
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
                        Formulir Perubahan data Transaksi Penjualan
                    </h6>

                    <div class="card-body">

                        <form action="{{ route('penjualan.update', $penjualan->id) }}" method="POST">
                            @csrf

                            <div class="form-row">

                                <input type="hidden" name="id" value="{{ $penjualan->id }}">
                                <input type="hidden" name="items" id="items_input">
                                <input type="hidden" name="total_harga" id="total_harga_input">

                                {{-- DROPSHIPPER --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Dropshipper</label>

                                    <select name="dropshipper_id" class="form-control" required>
                                        <option value="">-- Pilih Dropshipper --</option>

                                        @foreach ($dropshippers as $ds)
                                            <option value="{{ $ds->id }}"
                                                {{ $penjualan->dropshipper_id == $ds->id ? 'selected' : '' }}>
                                                {{ $ds->nama }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                {{-- TANGGAL --}}
                                <input type="hidden" name="tanggal_final" id="tanggal_final">
                                <div class="form-group col-md-4">
                                    <label class="form-label">Tanggal Penjualan</label>
                                    <input type="date" name="tanggal" class="form-control"
                                        value="{{ date('Y-m-d', strtotime($penjualan->tanggal)) }}" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label class="form-label">Jam</label>
                                    <input type="time" name="jam" id="jam_edit" class="form-control"
                                        value="{{ date('H:i', strtotime($penjualan->tanggal)) }}" required>
                                </div>

                                {{-- KODE PENJUALAN --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Kode Penjualan</label>

                                    <input type="text" name="kode_penjualan" class="form-control"
                                        value="{{ $penjualan->kode_penjualan }}">

                                </div>

                                {{-- KETERANGAN --}}
                                <div class="form-group col-md-6">

                                    <label class="form-label">Keterangan</label>

                                    <textarea name="keterangan" class="form-control" rows="2">{{ $penjualan->keterangan }}</textarea>

                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Scan Out</label>
                                    <select name="scan_out" class="form-control">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="pending" {{ $penjualan->scan_out == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="done" {{ $penjualan->scan_out == 'done' ? 'selected' : '' }}>Done
                                        </option>
                                        <option value="failed" {{ $penjualan->scan_out == 'failed' ? 'selected' : '' }}>
                                            Failed</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Penjualan Draft ?</label>
                                    <select name="is_draft" value="{{ $penjualan->is_draft }}" class="form-control">
                                        <option value="no" {{ $penjualan->is_draft == 'no' ? 'selected' : '' }}>Tidak
                                        </option>
                                        <option value="yes" {{ $penjualan->is_draft == 'yes' ? 'selected' : '' }}>Ya
                                        </option>
                                    </select>
                                    <span class="text-muted">Penjualan draft disini jika YA dapat membuat penjualan namun
                                        tidak mengurangi stok.</span>
                                </div>
                            </div>

                            <hr>
                            <div class="mt-4">

                                <hr>

                                {{-- MODE HARGA --}}
                                <div class="mb-3 p-3"
                                    style="background:#f8fafc; border:1px solid #dee2e6; border-radius:8px;">
                                    <label class="font-weight-bold small text-uppercase mb-2 d-block"
                                        style="letter-spacing:.06em; color:#495057;">
                                        <i class="feather icon-tag mr-1"></i> Mode Harga Penjualan
                                    </label>
                                    <div class="d-flex" style="gap:10px;">
                                        <label style="cursor:pointer; flex:1; max-width:220px;">
                                            <input type="radio" name="mode_harga_edit" id="mode_harga_edit_2"
                                                value="harga_2" checked hidden>
                                            <div class="mode-card-edit active" id="mode_card_edit_harga_2"
                                                style="display:flex; align-items:center; justify-content:center; padding:10px 16px;
                       border-radius:8px; border:2px solid #ee4d2d; background:#fff5f3;
                       font-weight:600; font-size:.88rem; cursor:pointer; user-select:none;
                       box-shadow: 0 0 0 3px rgba(238,77,45,.12); transition: all .15s;">
                                                <i class="feather icon-tag mr-1"></i>
                                                <span>Harga Reseller</span>
                                                <i class="feather icon-check-circle ml-2" style="color:#28a745;"></i>
                                            </div>
                                        </label>
                                        <label style="cursor:pointer; flex:1; max-width:220px;">
                                            <input type="radio" name="mode_harga_edit" id="mode_harga_edit_1"
                                                value="harga_1" hidden>
                                            <div class="mode-card-edit" id="mode_card_edit_harga_1"
                                                style="display:flex; align-items:center; justify-content:center; padding:10px 16px;
                       border-radius:8px; border:2px solid #dee2e6; background:#f8fafc;
                       font-weight:600; font-size:.88rem; cursor:pointer; user-select:none; transition: all .15s;">
                                                <i class="feather icon-percent mr-1"></i>
                                                <span>Harga HPP</span>
                                                <i class="feather icon-check-circle ml-2"
                                                    style="color:#28a745; display:none;"></i>
                                            </div>
                                        </label>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        Mengubah mode akan langsung mengupdate harga semua produk di tabel.
                                    </small>
                                </div>
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
                                {{-- <div class="form-group row">

                                    <label class="col-md-2 col-form-label">
                                        Input Nomor Transaksi
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
                                                <th>Nomor Transaksi</th>
                                                <th>SKU</th>
                                                <th>Nama Barang</th>
                                                <th>Stok</th>
                                                <th>Harga</th>

                                                <th width="120">Qty</th>
                                                <th width="120">Δ Stok</th>


                                                <th>Subtotal</th>
                                                <th width="60"></th>

                                            </tr>

                                        </thead>

                                        <tbody></tbody>

                                    </table>

                                    <small class="text-muted">
                                        Δ Stok menunjukkan perubahan stok akibat edit qty.
                                        Hijau (+) = stok bertambah | Merah (-) = stok berkurang
                                    </small>


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
        let existingItems = @json($penjualan->detail);

        let items = {};


        console.log(existingItems)
        // ================================================================
        // MODE HARGA EDIT
        // ================================================================
        function getModeHargaEdit() {
            return document.querySelector('input[name="mode_harga_edit"]:checked')?.value ?? 'harga_2';
        }

        function setModeCardEditActive(mode) {
            // Reset semua
            document.querySelectorAll('.mode-card-edit').forEach(el => {
                el.style.borderColor = '#dee2e6';
                el.style.background = '#f8fafc';
                el.style.boxShadow = 'none';
                el.querySelector('i.feather.icon-check-circle').style.display = 'none';
            });

            // Aktifkan yang dipilih
            const activeCard = document.getElementById(`mode_card_edit_${mode}`);
            if (!activeCard) return;

            if (mode === 'harga_2') {
                activeCard.style.borderColor = '#ee4d2d';
                activeCard.style.background = '#fff5f3';
                activeCard.style.boxShadow = '0 0 0 3px rgba(238,77,45,.12)';
            } else {
                activeCard.style.borderColor = '#2f80ed';
                activeCard.style.background = '#f0f6ff';
                activeCard.style.boxShadow = '0 0 0 3px rgba(47,128,237,.12)';
            }
            activeCard.querySelector('i.feather.icon-check-circle').style.display = 'inline';
        }

        // Listener klik card
        document.querySelectorAll('.mode-card-edit').forEach(card => {
            card.addEventListener('click', function() {
                const mode = this.id.replace('mode_card_edit_', '');
                document.getElementById(`mode_harga_edit_${mode.replace('harga_', '')}`).checked = true;
                setModeCardEditActive(mode);
                applyModeHargaToAllItems(mode);
            });
        });

        // Terapkan mode harga ke semua item di tabel
        function applyModeHargaToAllItems(mode) {
            let hasItems = Object.keys(items).length > 0;
            if (!hasItems) return;

            Object.values(items).forEach(item => {
                const newHarga = mode === 'harga_1' ?
                    (item.harga_1 ?? item.harga_2) :
                    item.harga_2;

                item.harga_aktif = newHarga;

                // Update tampilan kolom Harga (kolom ke-7)
                const row = $(`#row_${item.id}`);
                row.find('td:nth-child(7)').text(formatRupiah(newHarga));
                row.find('.total').text(formatRupiah(newHarga * item.qty));
            });

            calculateTotal();

            Toast.fire({
                icon: 'info',
                title: `Mode harga: ${mode === 'harga_1' ? 'Harga HPP' : 'Harga Reseller'}`
            });
        }

        function isDraftMode() {
            return $('select[name="is_draft"]').val() === 'yes';
        }


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
                let originalQty = items[product.id].original_qty ?? 0;
                let delta = newQty - originalQty;

                if (!draft && delta > items[product.id].stok) {
                    Toast.fire({
                        icon: "error",
                        title: `Qty melebihi stok tersedia (stok: ${items[product.id].stok})`
                    });
                    return;
                }
                if (draft && delta > items[product.id].stok) {
                    Toast.fire({
                        icon: "warning",
                        title: `Qty ${newQty} melebihi stok, dicatat draft.`
                    });
                }

                items[product.id].qty = newQty;
                $('#row_' + product.id + ' .qty').val(newQty);
                updateRow(product.id);
                flashRow(product.id);
                return;
            }

            const hargaAktif = getModeHargaEdit() === 'harga_1' ?
                (product.harga_1 ?? product.harga_2) :
                product.harga_2;

            let nomorUrut = Object.keys(items).length + 1;
            items[product.id] = {
                id: product.id,
                sku: product.sku,
                nama_barang: product.nama_barang,
                stok: product.stok.jumlah_stok,
                harga_1: product.harga_1 ?? 0,
                harga_2: product.harga_2,
                harga_aktif: hargaAktif,
                qty: qty,
                original_qty: 0,
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
            let delta = item.qty - item.original_qty;
            let harga = item.harga_aktif ?? item.harga_2;
            let deltaText = delta > 0 ?
                `<span class="text-danger">-${delta}</span>` :
                delta < 0 ?
                `<span class="text-success">+${Math.abs(delta)}</span>` :
                `<span class="text-muted">0</span>`;

            let row = `
<tr id="row_${id}">
    <td><input type="text" class="form-control nomor_resi"    data-id="${id}"></td>
    <td><input type="text" class="form-control nomor_pesanan" data-id="${id}"></td>
    <td><input type="text" class="form-control nomor_transaksi" data-id="${id}" value="${item.nomor_urut}"></td>
    <td>${item.sku}</td>
    <td>${item.nama_barang}</td>
    <td>${item.stok}</td>
    <td>${formatRupiah(harga)}</td>
    <td><input type="number" class="form-control qty" data-id="${id}" value="${item.qty}" min="1"></td>
    <td class="delta">${deltaText}</td>
    <td class="total">${formatRupiah(harga * item.qty)}</td>
    <td><button class="btn btn-danger btn-sm remove" data-id="${id}">X</button></td>
</tr>`;

            $('#tableItems tbody').append(row);
            calculateTotal();
        }

        function updateRow(id) {
            let item = items[id];
            let harga = item.harga_aktif ?? item.harga_2;
            let total = item.qty * harga;

            $('#row_' + id + ' td:nth-child(7)').text(formatRupiah(harga));
            $('#row_' + id + ' .total').text(formatRupiah(total));

            let delta = item.qty - item.original_qty;
            let deltaText = delta > 0 ?
                `<span class="text-danger">-${delta}</span>` :
                delta < 0 ?
                `<span class="text-success">+${Math.abs(delta)}</span>` :
                `<span class="text-muted">0</span>`;

            $('#row_' + id + ' .delta').html(deltaText);
            calculateTotal();
        }


        // Handler qty change dengan draft awareness
        $(document).on('change', '.qty', function() {
            let id = $(this).data('id');
            let input = $(this);
            let val = parseInt(input.val());
            let oldQty = items[id].qty;
            const draft = isDraftMode();

            let originalQty = items[id].original_qty ?? 0;
            let availableStok = items[id].stok + originalQty;

            if (!val || val <= 0) {
                input.val(1);
                items[id].qty = 1;
                Toast.fire({
                    icon: "error",
                    title: "Qty tidak boleh minus!"
                });
                updateRow(id);
                return;
            }

            let delta = val - originalQty;

            if (!draft && delta > items[id].stok) {
                Toast.fire({
                    icon: "error",
                    title: `Qty melebihi stok tersedia (stok: ${items[id].stok}, tersedia untuk tambah: ${items[id].stok})`
                });
                input.val(oldQty);
                return;
            }

            if (draft && delta > items[id].stok) {
                Toast.fire({
                    icon: "warning",
                    title: `Qty ${val} melebihi stok (${items[id].stok}), dicatat draft.`
                });
            }

            items[id].qty = val;
            updateRow(id);
        });


        // Kalau user ganti draft mode, update highlight semua baris
        $('select[name="is_draft"]').on('change', function() {
            const draft = $(this).val() === 'yes';

            Object.values(items).forEach(item => {
                const row = $(`#row_${item.id}`);
                const delta = item.qty - (item.original_qty ?? 0);
                const over = delta > item.stok;

                if (draft) {
                    row.removeClass('table-danger');
                    if (over) {
                        Toast.fire({
                            icon: "warning",
                            title: `SKU #${item.sku}: qty melebihi stok, mode draft aktif.`
                        });
                    }
                } else {
                    if (over) {
                        row.addClass('table-danger');
                        Toast.fire({
                            icon: "warning",
                            title: `SKU #${item.sku}: qty melebihi stok! Kurangi qty atau aktifkan draft.`
                        });
                    }
                }
            });
        });


        $(document).on('keydown', '.qty', function(e) {

            if (e.key === "Enter") {
                e.preventDefault();
                $(this).trigger('change');
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
        $(document).on('click', '.remove', function() {

            let id = $(this).data('id');

            delete items[id];

            $('#row_' + id).remove();
            reOrderNomorUrut()

            calculateTotal();

        });

        function calculateTotal() {

            let total = 0;

            Object.values(items).forEach(i => {

                total += i.qty * (i.harga_aktif ?? i.harga_2);

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

        function validateStock(product, isDraft = false) {
            let stok = product.stok.jumlah_stok;
            let originalQty = items[product.id]?.original_qty ?? 0;
            let availableStok = stok + originalQty; // stok real + stok yang "dikembalikan" dari qty lama

            if (!isDraft) {
                if (availableStok <= 0) {
                    Toast.fire({
                        icon: "error",
                        title: `SKU #${product.sku} Stok habis! Harus restock dulu.`
                    });
                    return false;
                }
            } else {
                if (availableStok <= 0) {
                    Toast.fire({
                        icon: "warning",
                        title: `SKU #${product.sku} Stok habis, dicatat sebagai draft.`
                    });
                }
            }

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
        $(document).ready(function() {

            $('#barcode_scan').focus();

        });


        // Form submit — validasi ulang
        $('form').on('submit', function(e) {
            const draft = isDraftMode();
            let valid = true;
            let errors = [];

            if (Object.keys(items).length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tambahkan minimal 1 barang!'
                });
                return false;
            }

            if (!draft) {
                Object.values(items).forEach(item => {
                    let delta = item.qty - (item.original_qty ?? 0);
                    if (delta > item.stok) {
                        valid = false;
                        errors.push(
                            `SKU #${item.sku}: butuh tambahan ${delta} tapi stok hanya ${item.stok}`);
                    }
                });
            }

            if (!valid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errors.map(m => `<p>❌ ${m}</p>`).join(''),
                });
                return false;
            }

            // Kumpulkan items
            let result = [];
            $('#tableItems tbody tr').each(function() {
                let id = $(this).attr('id').replace('row_', '');
                result.push({
                    id: id,
                    qty: $(this).find('.qty').val(),
                    harga_2: items[id].harga_aktif ?? items[id].harga_2,
                    nomor_resi: $(this).find('.nomor_resi').val(),
                    nomor_pesanan: $(this).find('.nomor_pesanan').val(),
                    nomor_transaksi: $(this).find('.nomor_transaksi').val()
                });
            });
            const tgl = $('input[name="tanggal"]').val();
            const jam = $('#jam_edit').val() || '00:00';
            $('#tanggal_final').val(tgl + ' ' + jam + ':00');

            $('#items_input').val(JSON.stringify(result));

            let total = 0;
            Object.values(items).forEach(i => {
                total += i.qty * (i.harga_aktif ?? i.harga_2);
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

        $(document).ready(function() {

            if (existingItems.length) {


                existingItems.forEach(function(d) {

                    const harga1 = d.barang.harga_1 ?? 0;
                    const harga2 = d.barang.harga_2 ?? 0;
                    const hargaDetail = d.harga; // harga yang tersimpan di penjualan_detail

                    // Cocokkan harga detail dengan harga barang
                    let detectedMode;
                    if (hargaDetail == harga1) {
                        detectedMode = 'harga_1'; // cocok dengan HPP (termasuk sama-sama 0)
                    } else if (hargaDetail == harga2) {
                        detectedMode = 'harga_2'; // cocok dengan Reseller
                    } else {
                        detectedMode = 'harga_2'; // fallback: harga sudah berubah di master barang
                    }

                    const modeAktif = getModeHargaEdit(); // mode yang dipilih user di UI
                    const hargaAktif = modeAktif === 'harga_1' ? (harga1 || harga2) : harga2;

                    items[d.barang.id] = {
                        id: d.barang.id,
                        sku: d.barang.sku,
                        nama_barang: d.barang.nama_barang,
                        stok: d.barang.stok.jumlah_stok,
                        harga_1: harga1,
                        harga_2: harga2,
                        harga_aktif: hargaAktif,
                        harga_detail_asli: hargaDetail, // simpan harga asli untuk referensi
                        detected_mode: detectedMode, // mode yang terdeteksi dari detail
                        qty: d.qty,
                        original_qty: d.qty
                    };


                    renderRow(d.barang.id);

                    $('#row_' + d.barang.id + ' .qty').val(d.qty);

                    $('#row_' + d.barang.id + ' .nomor_resi')
                        .val(d.nomor_resi ?? '');

                    $('#row_' + d.barang.id + ' .nomor_pesanan')
                        .val(d.nomor_pesanan ?? '');

                    $('#row_' + d.barang.id + ' .nomor_transaksi')
                        .val(d.nomor_transaksi ?? '');

                    updateRow(d.barang.id);

                });

                if (existingItems.length > 0) {
                    let countHarga1 = 0;
                    let countHarga2 = 0;

                    Object.values(items).forEach(item => {
                        if (item.detected_mode === 'harga_1') countHarga1++;
                        else countHarga2++;
                    });

                    const dominantMode = countHarga1 >= countHarga2 ? 'harga_1' : 'harga_2';

                    // Set radio button
                    const radioId = dominantMode === 'harga_1' ? 'mode_harga_edit_1' : 'mode_harga_edit_2';
                    document.getElementById(radioId).checked = true;
                    setModeCardEditActive(dominantMode);

                    // Update harga_aktif semua item sesuai mode dominan
                    applyModeHargaToAllItems(dominantMode);
                }
            }

        });
    </script>
@endsection
