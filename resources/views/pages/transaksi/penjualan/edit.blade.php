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

        /* FLOATING SCROLL BUTTONS */
        .scroll-fab {
            position: fixed;
            right: 18px;
            bottom: 100px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .scroll-fab button {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: rgba(78, 115, 223, .9);
            color: #fff;
            font-size: 1.15rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .18);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s, background .15s;
        }

        .scroll-fab button:hover {
            transform: scale(1.08);
            background: #4e73df;
        }

        .scroll-fab button:active {
            transform: scale(.95);
        }

        /* FILE RESI PREVIEW */
        .file-resi-wrap {
            position: relative;
        }

        .file-resi-wrap input[type="file"] {
            line-height: 1;
        }

        .file-resi-preview {
            display: none;
            margin-top: 8px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            max-height: 220px;
            cursor: pointer;
            position: relative;
        }

        .file-resi-preview.has-file {
            display: block;
        }

        .file-resi-preview img {
            width: 100%;
            object-fit: contain;
            max-height: 220px;
            display: block;
            background: #f8fafc;
        }

        .file-resi-preview .preview-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .15s;
        }

        .file-resi-preview:hover .preview-overlay {
            opacity: 1;
        }

        .file-resi-preview .preview-overlay span {
            color: #fff;
            font-size: .82rem;
            font-weight: 600;
        }

        .file-resi-badge {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            padding: 4px 10px;
            background: #e8f5e9;
            border-radius: 6px;
            font-size: .78rem;
            color: #28a745;
        }

        .file-resi-badge.has-file {
            display: flex;
        }

        .btn-clear-file {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 0;
            font-size: .78rem;
            margin-left: auto;
        }

        /* LIGHTBOX */
        #file-lightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, .8);
            align-items: center;
            justify-content: center;
        }

        #file-lightbox.active {
            display: flex;
        }

        #file-lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .5);
        }

        #file-lightbox .lb-close {
            position: absolute;
            top: 20px;
            right: 24px;
            color: #fff;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
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
                            <input type="hidden" name="redirect_to" value="{{ request('from') === 'web' ? 'web' : '' }}">

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

                                {{-- STATUS --}}
                                <div class="form-group col-md-6">
                                    <label class="form-label">Status Proses</label>
                                    <select name="status" class="form-control">
                                        <option value="proses" {{ $penjualan->status == 'proses' ? 'selected' : '' }}>Proses</option>
                                        <option value="packing" {{ $penjualan->status == 'packing' ? 'selected' : '' }}>Packing</option>
                                        <option value="dikirim" {{ $penjualan->status == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                        <option value="selesai" {{ $penjualan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </div>

                                
                                {{-- FILE RESI --}}
                                <div class="form-group col-md-12">
                                    <label class="form-label">
                                        File Resi
                                        <span class="badge badge-secondary ml-1" style="font-size:.7rem;font-weight:400;">Opsional</span>
                                        <span class="text-muted small font-weight-normal">(Image / PDF, otomatis jadi preview)</span>
                                    </label>
                                    <div class="file-resi-wrap">
                                        <input type="file" class="form-control" id="file_resi_edit"
                                            accept="image/jpeg,image/png,image/jpg,application/pdf,.pdf">
                                        <input type="hidden" name="file_resi_base64" id="file_resi_base64_edit">
                                        <input type="hidden" name="file_resi_name" id="file_resi_name_edit"
                                            value="{{ $penjualan->file_resi ? basename($penjualan->file_resi) : '' }}">
                                        <div class="file-resi-badge {{ $penjualan->file_resi ? 'has-file' : '' }}" id="file_resi_badge_edit">
                                            <i class="feather icon-image" style="font-size:14px;"></i>
                                            <span id="file_resi_name_span">{{ $penjualan->file_resi ? basename($penjualan->file_resi) : '' }}</span>
                                            <button type="button" class="btn-clear-file" onclick="clearFileResiEdit()" title="Hapus file">
                                                <i class="feather icon-x"></i>
                                            </button>
                                        </div>
                                        <div class="file-resi-preview {{ $penjualan->file_resi ? 'has-file' : '' }}" id="file_resi_preview_edit"
                                             onclick="openLightboxEdit()">
                                            @if($penjualan->file_resi)
                                                <img id="file_resi_img_edit"
                                                    src="{{ asset('storage/' . $penjualan->file_resi) }}"
                                                    alt="Preview Resi">
                                            @else
                                                <img id="file_resi_img_edit" src="" alt="Preview Resi">
                                            @endif
                                            <div class="preview-overlay">
                                                <span><i class="feather icon-zoom-in mr-1"></i> Perbesar</span>
                                            </div>
                                        </div>
                                    </div>
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

                                <a href="{{ request('from') === 'web' ? route('penjualan.web') : route('penjualan.index') }}" class="btn btn-secondary">

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

        {{-- FLOATING SCROLL BUTTONS --}}
        <div class="scroll-fab">
            <button type="button" id="btn-scroll-top" title="Scroll ke atas"><i class="feather icon-arrow-up"></i></button>
            <button type="button" id="btn-scroll-bottom" title="Scroll ke bawah"><i class="feather icon-arrow-down"></i></button>
        </div>

        {{-- FILE RESI LIGHTBOX --}}
        <div id="file-lightbox" onclick="closeLightboxEdit()">
            <span class="lb-close" onclick="closeLightboxEdit()">&times;</span>
            <img id="lb-img-edit" src="" alt="Preview Resi">
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

        // ── Floating scroll buttons ──────────────────────────────────────
        $('#btn-scroll-top').on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 400);
        });
        $('#btn-scroll-bottom').on('click', function() {
            $('html, body').animate({ scrollTop: $(document).height() }, 400);
        });

        // ================================================================
        // FILE RESI — preview + lightbox + PDF → image
        // ================================================================
        function getPdfjsEdit() {
            if (window.pdfjsLib) return Promise.resolve(window.pdfjsLib);
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js';
                script.onload = () => resolve(window.pdfjsLib);
                script.onerror = () => reject(new Error('Gagal memuat library pdf.js. Cek koneksi internet.'));
                document.head.appendChild(script);
            });
        }

        function resizePdfToJpegEdit(file) {
            return getPdfjsEdit().then(pdfjsLib => {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = () => reject(new Error('Gagal membaca file PDF.'));
                    reader.readAsArrayBuffer(file);
                })
                .then(buffer => pdfjsLib.getDocument({ data: buffer }).promise)
                .then(pdf => pdf.getPage(1))
                .then(page => {
                    const baseViewport = page.getViewport({ scale: 1 });
                    const targetWidth = 1200;
                    const scale = Math.max(1, targetWidth / baseViewport.width);
                    const viewport = page.getViewport({ scale: scale });
                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const ctx = canvas.getContext('2d');
                    return page.render({ canvasContext: ctx, viewport }).promise.then(() => canvas.toDataURL('image/jpeg', 0.9));
                });
            });
        }

        function showFilePreviewEdit(src, filename) {
            const wrap = $('#file_resi_preview_edit');
            const badge = $('#file_resi_badge_edit');
            const imgEl = $('#file_resi_img_edit');
            const nameEl = $('#file_resi_name_span');
            if (src) {
                const imgSrc = src.startsWith('data:') ? src : `data:image/jpeg;base64,${src}`;
                imgEl.attr('src', imgSrc);
                wrap.addClass('has-file');
                nameEl.text(filename || 'resi.jpg');
                badge.addClass('has-file');
            } else {
                imgEl.attr('src', '');
                wrap.removeClass('has-file');
                badge.removeClass('has-file');
                nameEl.text('');
            }
        }

        function clearFileResiEdit() {
            $('#file_resi_edit').val('');
            $('#file_resi_base64_edit').val('');
            $('#file_resi_name_edit').val('');
            showFilePreviewEdit(null, '');
        }

        function openLightboxEdit() {
            const src = $('#file_resi_img_edit').attr('src');
            if (!src) return;
            $('#lb-img-edit').attr('src', src);
            $('#file-lightbox').addClass('active');
        }

        function closeLightboxEdit() {
            $('#file-lightbox').removeClass('active');
            $('#lb-img-edit').attr('src', '');
        }

        $('#file_resi_edit').on('change', function() {
            const file = this.files[0];
            if (!file) {
                clearFileResiEdit();
                return;
            }

            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            if (isPdf) {
                resizePdfToJpegEdit(file)
                    .then(dataUrl => {
                        const filename = file.name.replace(/\.pdf$/i, '.jpg');
                        showFilePreviewEdit(dataUrl, filename);
                        $('#file_resi_base64_edit').val(dataUrl.split(',')[1]);
                        $('#file_resi_name_edit').val(filename);
                    })
                    .catch(err => {
                        clearFileResiEdit();
                        Swal.fire('Oops!', 'Gagal mengkonversi PDF: ' + (err.message || err), 'error');
                    });
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const dataUrl = e.target.result;
                showFilePreviewEdit(dataUrl, file.name);
                $('#file_resi_base64_edit').val(dataUrl.split(',')[1]);
                $('#file_resi_name_edit').val(file.name);
            };
            reader.readAsDataURL(file);
        });
    </script>
@endsection
