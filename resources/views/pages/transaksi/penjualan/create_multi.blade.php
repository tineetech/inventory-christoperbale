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

        /* IMPORT ZONE */
        .import-zone {
            border: 2px dashed #c0c9d5;
            border-radius: 10px;
            padding: 28px 24px;
            background: #f8fafc;
            transition: border-color .2s, background .2s;
        }

        .import-zone:hover {
            border-color: #4e73df;
            background: #eef2ff;
        }

        .import-zone .import-icon {
            font-size: 2.5rem;
            color: #4e73df;
        }

        /* INFO PANEL */
        #import-info-panel {
            display: none;
        }

        #import-info-panel.has-content {
            display: block;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: .84rem;
            margin-bottom: 5px;
        }

        .info-item.info-warn {
            background: #fff8e1;
            border-left: 3px solid #ffc107;
        }

        .info-item.info-error {
            background: #fdecea;
            border-left: 3px solid #dc3545;
        }

        .info-item.info-ok {
            background: #e8f5e9;
            border-left: 3px solid #28a745;
        }

        .info-item i {
            margin-top: 1px;
            flex-shrink: 0;
        }

        /* REPEATER CARD */
        .repeater-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .resi-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            transition: box-shadow .2s;
        }

        .resi-card:hover {
            box-shadow: 0 4px 18px rgba(0, 0, 0, .11);
        }

        .resi-card-header {
            background: #2f3237;
            color: #fff;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: .02em;
        }

        .resi-card-header .badge-number {
            background: rgba(255, 255, 255, .25);
            border-radius: 20px;
            padding: 2px 12px;
            font-size: .85rem;
            margin-right: 8px;
        }

        .resi-card-body {
            padding: 20px;
            background: #fff;
        }

        .btn-remove-resi {
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .4);
            color: #fff;
            border-radius: 6px;
            padding: 3px 10px;
            font-size: .82rem;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-remove-resi:hover {
            background: rgba(220, 53, 69, .7);
            border-color: transparent;
        }

        .btn-add-resi {
            border: 2px dashed #4e73df;
            color: #4e73df;
            background: transparent;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: background .15s, color .15s;
            width: 100%;
        }

        .btn-add-resi:hover {
            background: #4e73df;
            color: #fff;
        }

        .resi-card .table th {
            font-size: .82rem;
            white-space: nowrap;
        }

        .resi-card .table td {
            vertical-align: middle;
            font-size: .85rem;
        }

        .section-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6c757d;
            margin-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 4px;
        }

        /* FILE RESI PREVIEW */
        .file-resi-wrap {
            position: relative;
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

        /* LOADING OVERLAY */
        #import-loading {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .35);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 14px;
        }

        #import-loading.active {
            display: flex;
        }

        #import-loading .spinner-wrap {
            background: #fff;
            border-radius: 12px;
            padding: 32px 40px;
            text-align: center;
        }

        #resi-count-badge {
            font-size: .78rem;
            background: #4e73df;
            color: #fff;
            border-radius: 20px;
            padding: 2px 10px;
            margin-left: 6px;
        }
    </style>


    {{--
    ═══════════════════════════════════════════════
    CSS — tambahkan di @section('style')
    ═══════════════════════════════════════════════
--}}
    <style>
        #import-loading .spinner-wrap {
            background: #fff;
            border-radius: 14px;
            padding: 28px 32px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .18);
        }

        #loading-log .log-line {
            padding: 2px 0;
            border-bottom: 1px solid #f0f0f0;
            line-height: 1.5;
        }

        #loading-log .log-line:last-child {
            border-bottom: none;
        }

        #loading-log .log-ok {
            color: #28a745;
        }

        #loading-log .log-skip {
            color: #ffc107;
        }

        #loading-log .log-err {
            color: #dc3545;
        }

        #loading-log .log-info {
            color: #6c757d;
        }

        #loading-log .log-active {
            color: #4e73df;
            font-weight: 600;
        }

        /* MODE SELECTOR CARDS */
        .mode-option {
            cursor: pointer;
            flex: 1;
            max-width: 200px;
        }

        .mode-card {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            background: #f8fafc;
            font-weight: 600;
            font-size: .88rem;
            transition: border-color .15s, background .15s, box-shadow .15s;
            user-select: none;
            cursor: pointer;
        }

        .mode-card .mode-check {
            display: none;
            color: #28a745;
        }

        .mode-card.active .mode-check {
            display: inline;
        }

        .mode-card--shopee.active {
            border-color: #ee4d2d;
            background: #fff5f3;
            box-shadow: 0 0 0 3px rgba(238, 77, 45, .12);
        }

        .mode-card--tiktok.active {
            border-color: #2f80ed;
            background: #f0f6ff;
            box-shadow: 0 0 0 3px rgba(47, 128, 237, .12);
        }

        .mode-card:hover {
            border-color: #adb5bd;
            background: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Penjualan Multiple Resi</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item active">Create Multiple Resi</li>
                </ol>
            </div>

            @if (session('error'))
                <div class="card mb-4 border-danger">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 text-danger"><i class="feather icon-x-circle"></i> Error</h5>
                            <p class="mb-0 text-muted">{{ session('error') }}</p>
                        </div>
                        <div class="display-4 text-danger"><i class="feather icon-x-circle"></i></div>
                    </div>
                </div>
            @endif

            @if (session('store_warnings') || session('store_errors'))
                @php $storeIssues = session('store_warnings') ?? session('store_errors') ?? []; @endphp
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark font-weight-bold">
                        <i class="feather icon-alert-triangle mr-2"></i>
                        {{ session('store_warnings') ? 'Sebagian penjualan dilewati' : 'Semua penjualan gagal disimpan' }}
                    </div>
                    <div class="card-body p-3">
                        @foreach ($storeIssues as $issue)
                            <div class="info-item {{ $issue['type'] === 'duplicate_resi' ? 'info-warn' : 'info-error' }}">
                                <i
                                    class="feather {{ $issue['type'] === 'duplicate_resi' ? 'icon-skip-forward text-warning' : 'icon-x-circle text-danger' }}"></i>
                                <div>
                                    <strong>{{ $issue['label'] }}</strong>
                                    @if ($issue['resi'] && $issue['resi'] !== '-')
                                        <span class="text-muted">({{ $issue['resi'] }})</span>
                                    @endif
                                    — {!! $issue['reason'] !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif


            {{-- STEP 1 --}}
            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">
                    <i class="feather icon-upload-cloud mr-2"></i>
                    Step 1 — Import File Multiple Resi
                </h6>
                <div class="card-body">
                    <div class="import-zone text-center">
                        <div class="import-icon mb-2"><i class="feather icon-file-text"></i></div>
                        <h5 class="mb-1">Upload File Resi (Multiple)</h5>
                        <p class="text-muted small mb-3">Pilih file PDF yang berisi banyak resi Shopee atau TikTok J&T.</p>
                        <div class="col-12 px-0">
                            <input type="file" id="file_multiple_resi" class="form-control" accept=".pdf">
                        </div>
                    </div>

                    {{-- ★ SELECT MODE (TAMBAHAN BARU) --}}
                    <div class="mt-3 w-full">
                        <label class="font-weight-bold small text-uppercase" style="letter-spacing:.06em; color:#495057;">
                            <i class="feather icon-tag mr-1"></i> Platform / Mode
                        </label>
                        <div class="d-flex gap-2" style="gap:10px;">
                            <label class="mode-option " id="mode_label_shopee">
                                <input type="radio" name="import_mode" id="import_mode_shopee" value="shopee" checked
                                    hidden>
                                <div class="mode-card mode-card--shopee active" id="mode_card_shopee">
                                    <span>Shopee</span>
                                    <i class="feather icon-check-circle ml-2 mode-check"></i>
                                </div>
                            </label>
                            <label class="mode-option " id="mode_label_tiktok">
                                <input type="radio" name="import_mode" id="import_mode_tiktok" value="tiktok" hidden>
                                <div class="mode-card mode-card--tiktok" id="mode_card_tiktok">
                                    <i class="feather icon-truck mr-1" style="font-size:1rem;"></i>
                                    <span>TikTok J&T</span>
                                    <i class="feather icon-check-circle ml-2 mode-check"></i>
                                </div>
                            </label>
                        </div>
                    </div>
                    {{-- END SELECT MODE --}}

                    <button type="button" id="btn_import_multiple" class="btn btn-primary w-100 mt-3"
                        style="font-size:1rem;padding:12px;">
                        <i class="feather icon-zap mr-2"></i>
                        IMPORT MULTIPLE RESI -
                        <span id="import_mode_badge" class="" style="">Shopee</span>
                    </button>

                    <div id="import-info-panel" class="mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="font-weight-bold small text-uppercase" style="letter-spacing:.05em">
                                <i class="feather icon-info mr-1"></i> Hasil Import
                            </span>
                            <button type="button" class="btn btn-sm btn-link text-muted p-0"
                                onclick="clearInfoPanel()">Tutup</button>
                        </div>
                        <div id="import-info-list"></div>
                    </div>

                    <hr>
                    <p class="text-muted small mb-0">
                        <i class="feather icon-info mr-1"></i>
                        Setelah import, setiap resi tampil sebagai form terpisah di bawah.
                        File gambar resi otomatis dimasukkan ke masing-masing card.
                    </p>
                </div>
            </div>

            {{-- STEP 2 --}}
            <div class="card col-lg-12 mb-4">
                <h6 class="card-header d-flex align-items-center justify-content-between">
                    <span>
                        <i class="feather icon-layers mr-2"></i>
                        Step 2 — Detail Penjualan Per Resi
                        <span id="resi-count-badge">0</span>
                    </span>
                    <button type="button" id="btn_add_resi_manual" class="btn btn-sm btn-outline-primary">
                        <i class="feather icon-plus mr-1"></i> Tambah Resi Manual
                    </button>
                </h6>
                <div class="card-body">

                    {{-- MODE HARGA --}}
                    {{-- MODE HARGA --}}
                    <div id="mode-harga-bar"
                        style="display:none; background:#f8fafc; border:1px solid #dee2e6; border-radius:8px; padding:12px 16px; margin-bottom:16px;">
                        <label class="font-weight-bold small text-uppercase mb-2 d-block"
                            style="letter-spacing:.06em; color:#495057;">
                            <i class="feather icon-tag mr-1"></i> Mode Harga Penjualan
                        </label>
                        <div class="d-flex" style="gap:10px;">
                            <label class="mode-option">
                                <input type="radio" name="mode_harga" id="mode_harga_2" value="harga_2" checked
                                    hidden>
                                <div class="mode-card mode-card--shopee active" id="mode_card_harga_2">
                                    <i class="feather icon-tag mr-1"></i>
                                    <span>Harga Reseller</span>
                                    <i class="feather icon-check-circle ml-2 mode-check"></i>
                                </div>
                            </label>
                            <label class="mode-option">
                                <input type="radio" name="mode_harga" id="mode_harga_1" value="harga_1" hidden>
                                <div class="mode-card mode-card--tiktok" id="mode_card_harga_1">
                                    <i class="feather icon-percent mr-1"></i>
                                    <span>Harga HPP</span>
                                    <i class="feather icon-check-circle ml-2 mode-check"></i>
                                </div>
                            </label>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Mengubah mode akan langsung mengupdate harga semua produk di semua resi.
                        </small>
                    </div>
                    {{-- SET ALL DROPSHIPPER --}}
                    <div id="set-all-ds-bar"
                        style="display:none; background:#f8fafc;
                        border:1px solid #dee2e6;
                        border-radius:8px; padding:12px 16px;
                        margin-bottom:16px; align-items:center; gap:10px; flex-wrap:wrap;">
                        <span class="small font-weight-600 text-muted" style="white-space:nowrap;">
                            <i class="feather icon-users mr-1"></i> Set semua dropshipper:
                        </span>
                        <select id="set_all_dropshipper" class="form-control form-control-sm" style="max-width:260px;">
                            <option value="">-- Pilih Dropshipper --</option>
                            @foreach ($dropshippers as $ds)
                                <option value="{{ $ds->id }}">{{ $ds->nama }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="btn_apply_ds_all" class="btn btn-sm btn-primary">
                            <i class="feather icon-check mr-1"></i> Terapkan ke Semua
                        </button>
                        <span id="ds_apply_feedback" class="small text-success" style="display:none;">
                            <i class="feather icon-check-circle mr-1"></i> Diterapkan!
                        </span>
                    </div>

                    <form action="{{ route('penjualan.store.multiple') }}" method="POST" id="form_multiple_resi"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="payload" id="payload_input">

                        <div class="repeater-wrapper" id="repeater_wrapper">
                            <div id="repeater_empty" class="text-center py-5 text-muted">
                                <i class="feather icon-inbox" style="font-size:3rem;"></i>
                                <p class="mt-2">Belum ada resi. Import file atau tambah manual.</p>
                            </div>
                        </div>

                        <button type="button" id="btn_add_resi_bottom" class="btn-add-resi mt-3" style="display:none;">
                            <i class="feather icon-plus mr-2"></i> Tambah Resi Baru
                        </button>

                        <hr>
                        <div class="row mt-2 mb-3">
                            <div class="col-md-3 ml-auto">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">Grand Total</span>
                                    </div>
                                    <input type="text" id="grand_total_all"
                                        class="form-control font-weight-bold text-right" readonly value="Rp 0">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">
                                <i class="feather icon-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success" id="btn_submit_all">
                                <i class="feather icon-save"></i> Simpan Semua Penjualan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        @include('components.footer')
    </div>

    {{-- LOADING OVERLAY --}}
    {{-- <div id="import-loading">
    <div class="spinner-wrap">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
        <h6 class="mb-1" id="loading-text">Sedang membaca file resi...</h6>
        <p class="text-muted small mb-0">Mohon tunggu sebentar.</p>
    </div>
</div> --}}

    <div class="modal fade" id="modal-import-progress" data-backdrop="static" data-keyboard="false" tabindex="-1"
        role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content" style="border-radius: 14px; overflow: hidden;">

                {{-- Header --}}
                <div class="modal-header"
                    style="background: #2f3237; color:#fff; border-bottom: none; padding: 16px 20px;">
                    <div class="d-flex align-items-center gap-2" style="gap: 12px;">
                        <div class="spinner-border text-light flex-shrink-0" id="modal-spinner"
                            style="width:1.6rem; height:1.6rem;" role="status"></div>
                        <div>
                            <h6 class="mb-0 font-weight-bold" id="modal-import-title">Membaca file resi...</h6>
                            <small class="text-white-50" id="modal-import-subtitle">Mohon tunggu sebentar.</small>
                        </div>
                    </div>
                    {{-- Tombol X hanya muncul kalau sudah done / error --}}
                    <button type="button" class="close text-white d-none" id="modal-close-btn" data-dismiss="modal"
                        aria-label="Close" style="opacity:.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-4">

                    {{-- Progress bar --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small id="modal-step-label" class="text-muted">Mengirim file...</small>
                        <small id="modal-pct" class="font-weight-bold text-primary">0%</small>
                    </div>
                    <div class="progress mb-3" style="height: 10px; border-radius: 6px; background: #e9ecef;">
                        <div id="modal-progress-bar"
                            class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                            style="width: 0%; transition: width .4s ease;"></div>
                    </div>

                    {{-- Halaman counter --}}
                    <div class="d-flex justify-content-between mb-3"
                        style="background: #f8fafc; border: 1px solid #dee2e6;
                            border-radius: 8px; padding: 10px 14px;">
                        <div class="text-center" style="flex:1;">
                            <div class="font-weight-bold text-primary" id="modal-stat-total" style="font-size:1.4rem;">—
                            </div>
                            <small class="text-muted">Total Halaman</small>
                        </div>
                        <div style="width:1px; background:#dee2e6;"></div>
                        <div class="text-center" style="flex:1;">
                            <div class="font-weight-bold text-success" id="modal-stat-done" style="font-size:1.4rem;">0
                            </div>
                            <small class="text-muted">Selesai</small>
                        </div>
                        <div style="width:1px; background:#dee2e6;"></div>
                        <div class="text-center" style="flex:1;">
                            <div class="font-weight-bold text-danger" id="modal-stat-failed" style="font-size:1.4rem;">0
                            </div>
                            <small class="text-muted">Gagal</small>
                        </div>
                    </div>

                    {{-- Log berjalan --}}
                    <div id="modal-log"
                        style="background: #1e1e2e; border-radius: 8px; padding: 10px 12px;
                            max-height: 160px; overflow-y: auto;
                            font-size: .75rem; font-family: 'Courier New', monospace;
                            color: #cdd6f4; line-height: 1.6;">
                        <div class="log-line" style="color:#6c757d;">Menunggu server...</div>
                    </div>

                </div>

                {{-- Footer: hanya tampil saat error atau selesai --}}
                <div class="modal-footer d-none" id="modal-footer"
                    style="border-top: 1px solid #dee2e6; padding: 12px 20px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>

            </div>
        </div>
    </div>


    {{-- LIGHTBOX --}}
    <div id="file-lightbox" onclick="closeLightbox()">
        <span class="lb-close" onclick="closeLightbox()">&times;</span>
        <img id="lb-img" src="" alt="Preview Resi">
    </div>
@endsection

@section('scripts')
    <script>
        // ================================================================
        // STATE
        // ================================================================
        let resiList = [];
        let resiIndex = 0;
        const resiImageMap = {};

        // ================================================================
        // STOCK TRACKER
        // ================================================================
        const stockUsageMap = {};


        // ================================================================
        // POLLING CONFIG
        // ================================================================
        const POLL_INTERVAL_MS = 2000; // polling tiap 2 detik
        const POLL_MAX_WAIT_MS = 300000; // timeout 5 menit
        let _pollTimer = null;
        let _pollStartTime = null;
        let _currentJobId = null;

        // Simpan data yang sudah masuk (untuk deduplikasi saat partial update)
        let _processedPages = new Set();

        // ================================================================
        // MODAL HELPERS
        // ================================================================
        function modalReset() {
            $('#modal-progress-bar')
                .css('width', '0%')
                .removeClass('bg-success bg-danger')
                .addClass('bg-primary progress-bar-animated progress-bar-striped');
            $('#modal-pct').text('0%').removeClass('text-success text-danger').addClass('text-primary');
            $('#modal-step-label').text('Mengirim file ke server...');
            $('#modal-import-title').text('Membaca file resi...');
            $('#modal-import-subtitle').text('Mohon tunggu sebentar.');
            $('#modal-stat-total').text('—');
            $('#modal-stat-done').text('0');
            $('#modal-stat-failed').text('0');
            $('#modal-log').html('<div class="log-line" style="color:#6c757d;">Menunggu server...</div>');
            $('#modal-close-btn').addClass('d-none');
            $('#modal-footer').addClass('d-none');
            $('#modal-spinner').show();
            _processedPages.clear();
            _isProcessing = false; // ← tambah ini
        }

        function modalSet(pct, label) {
            const p = Math.min(100, Math.max(0, Math.round(pct)));
            $('#modal-progress-bar').css('width', p + '%');
            $('#modal-pct').text(p + '%');
            if (label !== undefined) $('#modal-step-label').text(label);
        }

        function modalLog(msg, type = 'info') {
            const colors = {
                ok: '#a6e3a1',
                skip: '#f9e2af',
                err: '#f38ba8',
                info: '#89b4fa',
                active: '#cba6f7',
            };
            const icons = {
                ok: '✓',
                skip: '⏭',
                err: '✗',
                info: '·',
                active: '▶'
            };
            const color = colors[type] ?? '#cdd6f4';
            const icon = icons[type] ?? '·';

            const logEl = document.getElementById('modal-log');
            const line = document.createElement('div');
            line.style.cssText = `color: ${color}; padding: 1px 0; border-bottom: 1px solid rgba(255,255,255,.04);`;
            line.innerHTML = `<span style="opacity:.5; margin-right:4px;">${icon}</span>${msg}`;
            logEl.appendChild(line);
            logEl.scrollTop = logEl.scrollHeight;
        }

        function modalDone(isError = false) {
            $('#modal-spinner').hide();
            $('#modal-close-btn').removeClass('d-none');
            $('#modal-footer').removeClass('d-none');
            if (isError) {
                $('#modal-progress-bar')
                    .removeClass('bg-primary progress-bar-animated progress-bar-striped')
                    .addClass('bg-danger');
                $('#modal-pct').removeClass('text-primary').addClass('text-danger');
            } else {
                $('#modal-progress-bar')
                    .removeClass('bg-primary progress-bar-animated progress-bar-striped')
                    .addClass('bg-success');
                $('#modal-pct').removeClass('text-primary').addClass('text-success');
                modalSet(100, 'Selesai!');
            }
        }

        function modalUpdateStats(total, done, failed) {
            if (total) $('#modal-stat-total').text(total);
            $('#modal-stat-done').text(done ?? 0);
            $('#modal-stat-failed').text(failed ?? 0);
        }

        // ================================================================
        // STOP POLLING
        // ================================================================
        function stopPolling() {
            if (_pollTimer) {
                clearInterval(_pollTimer);
                _pollTimer = null;
            }
            _currentJobId = null;
        }



        async function injectResiPage(resiData, pageIndex, totalPages) {
            const resiVal = (resiData.resi ?? '').trim();
            const pesananVal = (resiData.order_id ?? '').trim();
            const pageLabel = resiVal || pesananVal || `Hal. ${resiData.page}`;

            modalLog(`[${pageIndex}/${totalPages}] ${pageLabel}`, 'active');

            // Cek duplicate
            const dup = checkDuplicateResi(resiVal, pesananVal);
            if (dup.isDuplicate) {
                const label = dup.type === 'resi' ? 'No. Resi' : 'No. Pesanan';
                modalLog(`↳ DUPLIKAT (${label}: ${dup.value}) — dilewati`, 'skip');
                return {
                    skipped: true,
                    msg: `Hal. ${resiData.page}: ${label} <strong>${dup.value}</strong> sudah ada di list, dilewati.`
                };
            }

            // Buat card
            const cardId = addResiCard(resiVal, pesananVal, true);
            if (!cardId) {
                modalLog(`↳ Gagal buat card`, 'err');
                return {
                    skipped: false,
                    error: true
                };
            }

            // Inject gambar
            if (resiData.image_base64) {
                const filename = `resi_page${resiData.page}_${resiVal || 'unknown'}.jpg`;
                storeBase64ForCard(cardId, resiData.image_base64, filename);
                showFilePreview(cardId, resiData.image_base64, filename);
                modalLog(`↳ Gambar OK`, 'ok');
            }

            // Lookup & inject produk
            const extraErrors = [];
            if (resiData.items && resiData.items.length > 0) {
                for (const ocrItem of resiData.items) {
                    const sku = ocrItem.sku;
                    if (!sku) continue;
                    modalLog(`↳ Cari SKU: ${sku}`, 'info');
                    try {
                        const res = await fetch(`/api/product/search?q=${encodeURIComponent(sku)}`);
                        const products = await res.json();
                        if (!products || products.length === 0) {
                            extraErrors.push(
                                `Hal. ${resiData.page}: SKU <strong>${sku}</strong> tidak ditemukan / stok habis.`);
                            modalLog(`↳ SKU ${sku} tidak ditemukan`, 'err');
                            continue;
                        }
                        const product = products.find(p => p.sku === sku) ?? products[0];
                        if (product.sku !== sku) {
                            extraErrors.push(
                                `Hal. ${resiData.page}: SKU <strong>${sku}</strong> tidak exact, pakai <strong>${product.sku}</strong>.`
                                );
                            modalLog(`↳ SKU ${sku} → pakai ${product.sku}`, 'skip');
                        } else {
                            modalLog(`↳ SKU ${sku} OK (qty: ${ocrItem.qty ?? 1})`, 'ok');
                        }

                        addItemToCard(cardId, product, ocrItem.qty ?? 1);

                        // ── [BARU] Log stok rendah ke modal ────────────────
                        const stok = product.stok.jumlah_stok;
                        const min = product.stok_minimum ?? 1;
                        if (stok <= 0) {
                            modalLog(`↳ ⚠ SKU ${product.sku} STOK HABIS (${stok}) — draft`, 'err');
                        } else if (stok < min) {
                            modalLog(`↳ ⚠ SKU ${product.sku} di bawah minimum (stok: ${stok}, min: ${min}) — draft`,
                                'skip');
                        }

                        setTimeout(() => {
                            if (resiVal) $(`#row_${cardId}_${product.id} .nomor_resi`).val(resiVal);
                            if (pesananVal) $(`#row_${cardId}_${product.id} .nomor_pesanan`).val(pesananVal);
                        }, 100);

                    } catch (err) {
                        extraErrors.push(
                            `Hal. ${resiData.page}: Gagal cari SKU <strong>${sku}</strong> — ${err.message}`);
                        modalLog(`↳ Error cari SKU ${sku}: ${err.message}`, 'err');
                    }
                }
            } else {
                modalLog(`↳ Tidak ada SKU (isi manual)`, 'skip');
            }

            return {
                skipped: false,
                error: false,
                extraErrors
            };
        }

        let stockWarnings = [];
        let _isProcessing = false;
        async function startPolling(jobId, totalPagesHint) {
            _currentJobId = jobId;
            _pollStartTime = Date.now();

            if (totalPagesHint) modalUpdateStats(totalPagesHint, 0, 0);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            // Akumulasi untuk info panel
            let allBackendWarnings = [];
            let allExtraErrors = [];
            let allSkippedDuplicates = [];
            let injectedCount = 0;

            // Progress: 5% submit → 15% queued/processing → 95% per page → 100%
            modalSet(15, 'Menunggu worker memproses...');

            _pollTimer = setInterval(async () => {

                if (_isProcessing) return; // ← SKIP jika masih await
                _isProcessing = true;
                // Safety timeout
                try {
                    if (Date.now() - _pollStartTime > POLL_MAX_WAIT_MS) {
                        stopPolling();
                        modalLog('⚠ Timeout — coba lagi.', 'err');
                        modalDone(true);
                        return;
                    }

                    let pollData;
                    try {
                        const res = await fetch("{{ route('api.import.multi-job.status', ':id') }}"
                            .replace(':id', jobId), {
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });
                        pollData = await res.json();
                    } catch (err) {
                        modalLog(`Poll error: ${err.message}`, 'err');
                        return; // coba lagi di interval berikutnya
                    }

                    if (!pollData.success) {
                        stopPolling();
                        modalLog(`Error: ${pollData.message}`, 'err');
                        modalDone(true);
                        return;
                    }

                    // Update stats
                    const total = pollData.total_pages ?? totalPagesHint ?? 0;
                    const done = pollData.done_pages ?? 0;
                    const failed = pollData.failed_pages ?? 0;
                    modalUpdateStats(total, done, failed);

                    // Update progress bar: 15% → 90% seiring halaman selesai
                    const pct = total > 0 ? 15 + Math.round((done + failed) / total * 75) : 20;
                    modalSet(pct, `Memproses halaman ${done + failed} / ${total}...`);
                    $('#modal-import-title').text(`${done + failed} dari ${total} halaman selesai`);

                    // ── Inject halaman baru yang masuk (parsial) ──────────────
                    const newPages = (pollData.data ?? []).filter(p => !_processedPages.has(p.page));

                    for (const page of newPages) {
                        _processedPages.add(page.page);
                        const pageIdx = _processedPages.size;
                        const r = await injectResiPage(page, pageIdx, total);
                        if (r.skipped) {
                            allSkippedDuplicates.push(r.msg);
                        } else if (!r.error) {
                            injectedCount++;
                            (r.extraErrors ?? []).forEach(e => allExtraErrors.push(e));
                        }
                    }

                    // Kumpulkan warnings dari backend (dedup)
                    if (pollData.warnings) {
                        pollData.warnings.forEach(w => {
                            const key = w.type + '_' + (w.page ?? '');
                            if (!allBackendWarnings.find(x => (x.type + '_' + (x.page ?? '')) ===
                                    key)) {
                                allBackendWarnings.push(w);
                            }
                        });
                    }

                    // ── Cek apakah sudah selesai ──────────────────────────────
                    if (pollData.status === 'done' || pollData.status === 'error') {
                        stopPolling();

                        if (pollData.status === 'error') {
                            modalLog(`Job error: ${pollData.error ?? 'unknown'}`, 'err');
                            modalDone(true);
                            return;
                        }

                        // Done!
                        modalLog(
                            `Selesai: ${injectedCount} diimport, ${allSkippedDuplicates.length} duplikat dilewati`,
                            'ok'
                        );
                        modalSet(100, `Selesai — ${injectedCount} resi diimport`);
                        $('#modal-import-title').text('Import Selesai! 🎉');
                        $('#modal-import-subtitle').text('');

                        // Tunggu sebentar supaya user lihat 100%
                        await new Promise(resolve => setTimeout(resolve, 800));

                        modalDone(false);
document.getElementById('mode_harga_2').checked = true;
$('input[name="mode_harga"]').filter('[value="harga_2"]').trigger('change');

                        // Sembunyikan modal otomatis setelah 1.5 detik jika tidak ada warning
                        const hasWarnings = allBackendWarnings.length > 0 ||
                            allExtraErrors.length > 0 ||
                            allSkippedDuplicates.length > 0;

                        if (!hasWarnings) {
                            setTimeout(() => $('#modal-import-progress').modal('hide'), 1500);
                        } else {
                            // Biarkan user tutup sendiri (tombol X & footer sudah muncul)
                            modalLog(
                                `Ada ${allBackendWarnings.length + allExtraErrors.length + allSkippedDuplicates.length} warning — lihat panel info.`,
                                'skip');
                        }

                        // Render info panel

                        const finalErrors = [...allExtraErrors, ...allSkippedDuplicates]; // gabung sekali
                        renderInfoPanel(allBackendWarnings, finalErrors, {
                            total: injectedCount,
                            skipped: (pollData.total_skipped ?? 0) + allSkippedDuplicates.length,
                        });

                        // Reset file input
                        document.getElementById('file_multiple_resi').value = '';

                        // Scroll ke info panel
                        $('html, body').animate({
                            scrollTop: $('#import-info-panel').offset().top - 80
                        }, 400);
                    }
                } finally {
                    _isProcessing = false;
                }

            }, POLL_INTERVAL_MS);
        }

        function getStockEntry(productId, stok) {
            if (!stockUsageMap[productId]) stockUsageMap[productId] = {
                stok,
                usedByCard: {}
            };
            return stockUsageMap[productId];
        }

        function getTotalUsed(productId) {
            const entry = stockUsageMap[productId];
            if (!entry) return 0;
            return Object.values(entry.usedByCard).reduce((s, q) => s + q, 0);
        }

        function updateStockUsage(cardId, productId, qty, stok) {
            const entry = getStockEntry(productId, stok);
            entry.usedByCard[cardId] = qty;
            const totalUsed = getTotalUsed(productId);
            const isConflict = totalUsed > stok;
            Object.keys(entry.usedByCard).forEach(cid => {
                const row = $(`#row_${cid}_${productId}`);
                if (!row.length) return;
                if (isConflict) {
                    row.addClass('table-danger');
                    row.find('.stock-conflict-badge').remove();
                    row.find('td:nth-child(6)').append(
                        `<span class="stock-conflict-badge badge badge-danger d-block mt-1" style="font-size:.72rem;">
                    Total: ${totalUsed}/${stok}
                </span>`
                    );
                } else {
                    row.removeClass('table-danger');
                    row.find('.stock-conflict-badge').remove();
                }
            });
            return !isConflict;
        }

        function removeStockUsage(cardId, productId) {
            const entry = stockUsageMap[productId];
            if (!entry) return;
            delete entry.usedByCard[cardId];
            const totalUsed = getTotalUsed(productId);
            const isConflict = totalUsed > entry.stok;
            Object.keys(entry.usedByCard).forEach(cid => {
                const row = $(`#row_${cid}_${productId}`);
                if (!row.length) return;
                if (isConflict) {
                    row.find('.stock-conflict-badge').remove();
                    row.find('td:nth-child(6)').append(
                        `<span class="stock-conflict-badge badge badge-danger d-block mt-1" style="font-size:.72rem;">
                    Total: ${totalUsed}/${entry.stok}
                </span>`
                    );
                } else {
                    row.removeClass('table-danger');
                    row.find('.stock-conflict-badge').remove();
                }
            });
        }

        // ================================================================
        // HELPERS
        // ================================================================
        function uid() {
            return 'resi_' + (++resiIndex);
        }

        function formatRupiah(n) {
            return 'Rp ' + parseInt(n).toLocaleString('id-ID');
        }

        function isDraftModeFor(id) {
            return $(`#draft_${id}`).val() === 'yes';
        }

        function toggleSetAllDsBar() {
            if (resiList.length > 0) {
                $('#set-all-ds-bar').css('display', 'flex');
                $('#mode-harga-bar').css('display', 'block');
            } else {
                $('#set-all-ds-bar').hide();
                $('#mode-harga-bar').hide();
            }
        }

        function updateBadge() {
            $('#resi-count-badge').text(resiList.length);
            if (resiList.length > 0) {
                $('#repeater_empty').hide();
                $('#btn_add_resi_bottom').show();
            } else {
                $('#repeater_empty').show();
                $('#btn_add_resi_bottom').hide();
            }
            recalcGrandTotal();
            toggleSetAllDsBar();
        }

        function recalcGrandTotal() {
            let total = 0;
            resiList.forEach(r => Object.values(r.items).forEach(i => total += i.qty * (i.harga_aktif ?? i.harga_2)));
            $('#grand_total_all').val(formatRupiah(total));
        }

        // ================================================================
        // DUPLICATE VALIDATOR
        // ================================================================

        function checkDuplicateResi(resiVal, pesananVal, excludeId = null) {
            for (const r of resiList) {
                if (r.uid === excludeId) continue;

                const existingResi = $(`#resi_global_${r.uid}`).val().trim();
                const existingPesanan = $(`#pesanan_global_${r.uid}`).val().trim();

                if (resiVal && existingResi && resiVal === existingResi) {
                    return {
                        isDuplicate: true,
                        type: 'resi',
                        value: resiVal,
                        existingCard: r.uid
                    };
                }
                if (pesananVal && existingPesanan && pesananVal === existingPesanan) {
                    return {
                        isDuplicate: true,
                        type: 'pesanan',
                        value: pesananVal,
                        existingCard: r.uid
                    };
                }
            }
            return {
                isDuplicate: false,
                type: null,
                value: '',
                existingCard: null
            };
        }

        /**
         * Scroll ke & highlight card yang sudah ada (kasus duplicate)
         */
        function highlightExistingCard(cardId) {
            const card = $(`#card_${cardId}`);
            if (!card.length) return;
            $('html, body').animate({
                scrollTop: card.offset().top - 80
            }, 400);
            card.css({
                outline: '3px solid #dc3545',
                borderRadius: '10px'
            });
            setTimeout(() => card.css('outline', ''), 2500);
        }

        // ================================================================
        // FILE RESI — HELPERS
        // ================================================================
        function storeBase64ForCard(cardId, base64String, filename) {
            if (!base64String) return;
            const raw = base64String.startsWith('data:') ?
                base64String.split(',')[1] :
                base64String;
            resiImageMap[cardId] = {
                raw,
                filename: filename || 'resi.jpg'
            };
            const hiddenEl = document.getElementById(`img_base64_${cardId}`);
            if (hiddenEl) hiddenEl.value = raw;
        }

        function showFilePreview(cardId, src, filename) {
            const wrap = $(`#file_resi_preview_${cardId}`);
            const badge = $(`#file_resi_badge_${cardId}`);
            const imgEl = $(`#file_resi_img_${cardId}`);
            const nameEl = $(`#file_resi_name_${cardId}`);
            if (src) {
                const imgSrc = src.startsWith('data:') ? src : `data:image/jpeg;base64,${src}`;
                imgEl.attr('src', imgSrc);
                wrap.show();
                nameEl.text(filename || 'resi.jpg');
                badge.addClass('has-file');
            } else {
                imgEl.attr('src', '');
                wrap.hide();
                badge.removeClass('has-file');
                nameEl.text('');
            }
        }

        function clearFileInput(cardId) {
            const input = document.getElementById(`file_resi_${cardId}`);
            if (input) input.value = '';
            const hiddenEl = document.getElementById(`img_base64_${cardId}`);
            if (hiddenEl) hiddenEl.value = '';
            showFilePreview(cardId, null, '');
            delete resiImageMap[cardId];
        }

        function openLightbox(src) {
            $('#lb-img').attr('src', src);
            $('#file-lightbox').addClass('active');
        }

        function closeLightbox() {
            $('#file-lightbox').removeClass('active');
            $('#lb-img').attr('src', '');
        }

        // ================================================================
        // INFO PANEL
        // ================================================================
        function clearInfoPanel() {
            $('#import-info-list').html('');
            $('#import-info-panel').removeClass('has-content');
        }

        function renderInfoPanel(warnings, extras, stats) {
            const list = $('#import-info-list');
            list.html('');

            // ── Ringkasan import ────────────────────────────────────────
            const summaryClass = stats.skipped > 0 ? 'info-warn' : 'info-ok';
            const summaryIcon = stats.skipped > 0 ? 'icon-alert-circle text-warning' : 'icon-check-circle text-success';
            list.append(`
                <div class="info-item ${summaryClass}">
                    <i class="feather ${summaryIcon}"></i>
                    <div>
                        <strong>${stats.total} resi</strong> berhasil diimport ke form.
                        ${stats.skipped > 0 ? `<strong>${stats.skipped} resi dilewati</strong> (duplikat/error).` : ''}
                    </div>
                </div>
            `);

            // ── Warning dari backend ────────────────────────────────────
            warnings.forEach(w => {
                const cls = w.type === 'duplicate_resi' ? 'info-warn' : 'info-error';
                const icon = w.type === 'duplicate_resi' ? 'icon-skip-forward text-warning' :
                    'icon-alert-triangle text-danger';
                list.append(
                    `<div class="info-item ${cls}"><i class="feather ${icon}"></i><div>${w.message}</div></div>`
                    );
            });

            // ── Extra errors (SKU tidak ditemukan, duplikat, dll.) ──────
            extras.forEach(msg => {
                const isDup = msg.includes('sudah ada di list');
                const cls = isDup ? 'info-warn' : 'info-error';
                const icon = isDup ? 'icon-skip-forward text-warning' : 'icon-x-circle text-danger';
                list.append(`<div class="info-item ${cls}"><i class="feather ${icon}"></i><div>${msg}</div></div>`);
            });

            // ── [BARU] Warning stok rendah / habis ──────────────────────
            if (stockWarnings.length > 0) {
                // Judul pemisah
                list.append(`
                    <div style="margin: 10px 0 6px; font-size:.72rem; font-weight:700;
                                text-transform:uppercase; letter-spacing:.08em;
                                color:#dc3545; border-bottom:1px solid #f5c6cb;
                                padding-bottom:4px;">
                        <i class="feather icon-alert-octagon mr-1"></i>
                        Peringatan Stok (${stockWarnings.length} item)
                    </div>
                `);

                stockWarnings.forEach(w => {
                    list.append(`
                        <div class="info-item info-error">
                            <i class="feather icon-alert-octagon text-danger"></i>
                            <div>
                                SKU <strong>#${w.sku}</strong> — ${w.nama}:
                                <span class="text-danger font-weight-bold">${w.reason}</span>.
                                Card telah otomatis diset ke mode <strong>Draft</strong>.
                                Baris ditandai <span style="background:#f8d7da;padding:1px 6px;border-radius:3px;font-size:.8em;">merah</span>.
                            </div>
                        </div>
                    `);
                });
            }

            $('#import-info-panel').addClass('has-content');
        }


        // ================================================================
        // TEMPLATE CARD
        // ================================================================
        function buildResiCard(id, resiNumber, prefillResi = '', prefillPesanan = '') {
            const kode = 'PJL-' + Date.now() + '-' + resiIndex;
            return `
<div class="resi-card" id="card_${id}">
    <div class="resi-card-header">
        <div>
            <span class="badge-number">#${resiNumber}</span>
            Form Penjualan Resi
            ${prefillResi ? `<span class="ml-2 badge badge-light text-white" style="font-weight:400;font-size:.78rem">${prefillResi}</span>` : ''}
        </div>
        <button type="button" class="btn-remove-resi" onclick="removeResiCard('${id}')">
            <i class="feather icon-trash-2 mr-1"></i> Hapus
        </button>
    </div>
    <div class="resi-card-body">

        <div class="section-label">Informasi Utama</div>
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label>Dropshipper</label>
                <select name="dropshipper_id[]" id="dropshipper_${id}" class="form-control" required>
                    <option value="">-- Pilih Dropshipper --</option>
                    @foreach ($dropshippers as $ds)
                        <option value="{{ $ds->id }}">{{ $ds->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>Tanggal Penjualan</label>
                <input type="date" id="tanggal_${id}" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group col-md-2">
                <label>Jam</label>
                <input type="time" id="jam_${id}" class="form-control" value="{{ date('H:i') }}" required>
            </div>
            <div class="form-group col-md-6">
                <label>Kode Penjualan</label>
                <input type="text" id="kode_${id}" class="form-control" value="${kode}">
            </div>
            <div class="form-group col-md-6">
                <label>Keterangan</label>
                <textarea id="keterangan_${id}" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group col-md-6">
                <label>Scan Out</label>
                <select id="scan_out_${id}" class="form-control">
                    <option value="pending" selected>Pending</option>
                    <option value="done">Done</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label>Penjualan Draft?</label>
                <select id="draft_${id}" class="form-control draft-select" data-id="${id}">
                    <option value="no" selected>Tidak</option>
                    <option value="yes">Ya</option>
                </select>
                <span class="text-muted small">Draft = tidak mengurangi stok.</span>
            </div>

            <div class="form-group col-md-12">
                <label>
                    File Resi (Image Only)
                    <span class="badge badge-secondary ml-1" style="font-size:.7rem;font-weight:400;">Opsional</span>
                </label>
                <div class="file-resi-wrap">
                    <input type="file" class="form-control file-resi-input"
                           id="file_resi_${id}" data-id="${id}"
                           accept="image/jpeg,image/png,image/jpg">
                    <input type="hidden" id="img_base64_${id}" name="img_base64_${id}">
                    <div class="file-resi-badge" id="file_resi_badge_${id}">
                        <i class="feather icon-image" style="font-size:14px;"></i>
                        <span id="file_resi_name_${id}"></span>
                        <button type="button" class="btn-clear-file"
                                onclick="clearFileInput('${id}')" title="Hapus file">
                            <i class="feather icon-x"></i>
                        </button>
                    </div>
                    <div class="file-resi-preview" id="file_resi_preview_${id}"
                         onclick="openLightbox($('#file_resi_img_${id}').attr('src'))">
                        <img id="file_resi_img_${id}" src="" alt="Preview Resi">
                        <div class="preview-overlay">
                            <span><i class="feather icon-zoom-in mr-1"></i> Perbesar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-label">Scan / Cari Barang</div>
        <div class="form-row mb-2">
            <div class="form-group col-md-6">
                <label>Scan Single Barcode</label>
                <input type="text" class="form-control barcode-scan-single"
                       id="barcode_${id}" data-id="${id}" placeholder="Scan barcode / SKU lalu Enter">
            </div>
            <div class="form-group col-md-6">
                <label>Scan 10 Barcode sekaligus</label>
                <input type="text" class="form-control barcode-scan-10"
                       id="barcode10_${id}" data-id="${id}" placeholder="Scan barcode / SKU">
            </div>
            <div class="form-group col-md-12">
                <label>Cari Barang</label>
                <select class="form-control product-select" id="pselect_${id}" data-id="${id}" multiple></select>
            </div>
        </div>

        <div class="section-label">Nomor Resi & Pesanan</div>
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label>Nomor Resi</label>
                <input type="text" class="form-control resi-global-input"
                       id="resi_global_${id}" data-id="${id}"
                       value="${prefillResi}" placeholder="Ketik lalu Enter untuk isi semua baris">
            </div>
            <div class="form-group col-md-6">
                <label>Nomor Pesanan</label>
                <input type="text" class="form-control pesanan-global-input"
                       id="pesanan_global_${id}" data-id="${id}"
                       value="${prefillPesanan}" placeholder="Ketik lalu Enter untuk isi semua baris">
            </div>
        </div>

        <div class="section-label">Daftar Barang</div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="table_${id}">
                <thead class="thead-dark">
                    <tr>
                        <th>No. Resi</th><th>No. Pesanan</th><th>No. Urut</th>
                        <th>SKU</th><th>Nama Barang</th><th>Stok</th><th>Harga</th>
                        <th width="110">Qty</th><th>Subtotal</th><th width="50"></th>
                    </tr>
                </thead>
                <tbody id="tbody_${id}"></tbody>
            </table>
        </div>
        <div class="row mt-2">
            <div class="col-md-3 ml-auto">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Subtotal</span>
                    </div>
                    <input type="text" id="subtotal_${id}" class="form-control text-right" readonly value="Rp 0">
                </div>
            </div>
        </div>
    </div>
</div>`;
        }

        // ================================================================
        // ADD / REMOVE CARD
        // ================================================================

        /**
         * Tambah card resi baru.
         * @param {string}  prefillResi       - No. resi untuk pre-fill
         * @param {string}  prefillPesanan    - No. pesanan untuk pre-fill
         * @param {boolean} skipDupCheck      - true = skip validasi (sudah dicek di luar)
         * @returns {string|null} cardId jika berhasil, null jika duplicate
         */
        function addResiCard(prefillResi = '', prefillPesanan = '', skipDupCheck = false) {
            // ── Validasi duplicate ────────────────────────────────────────
            if (!skipDupCheck && (prefillResi.trim() || prefillPesanan.trim())) {
                const dup = checkDuplicateResi(prefillResi.trim(), prefillPesanan.trim());
                if (dup.isDuplicate) {
                    const label = dup.type === 'resi' ? 'No. Resi' : 'No. Pesanan';
                    Toast.fire({
                        icon: 'warning',
                        title: `${label} sudah ada di list!`,
                        html: `<b>${dup.value}</b> sudah ada. Menuju card tersebut...`,
                    });
                    highlightExistingCard(dup.existingCard);
                    return null;
                }
            }

            const id = uid();
            const number = resiList.length + 1;
            resiList.push({
                uid: id,
                resi: prefillResi,
                pesanan: prefillPesanan,
                items: {}
            });
            $('#repeater_wrapper').append(buildResiCard(id, number, prefillResi, prefillPesanan));
            initProductSelect(id);
            initBarcodeListeners(id);
            initResiPesananListeners(id);
            initDraftListener(id);
            initFileResiListener(id);
            updateBadge();
            return id;
        }

        function removeResiCard(id) {
            const state = getResiState(id);
            if (state) Object.keys(state.items).forEach(pid => removeStockUsage(id, pid));
            delete resiImageMap[id];
            resiList = resiList.filter(r => r.uid !== id);
            $(`#card_${id}`).remove();
            renumberCards();
            updateBadge();
        }

        function renumberCards() {
            resiList.forEach((r, i) => $(`#card_${r.uid} .badge-number`).text('#' + (i + 1)));
        }

        // ================================================================
        // FILE RESI LISTENER
        // ================================================================
        function initFileResiListener(id) {
            $(`#file_resi_${id}`).on('change', function() {
                const file = this.files[0];
                if (!file) {
                    showFilePreview(id, null, '');
                    clearFileInput(id);
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const dataUrl = e.target.result;
                    showFilePreview(id, dataUrl, file.name);
                    storeBase64ForCard(id, dataUrl, file.name);
                };
                reader.readAsDataURL(file);
            });
        }

        // ================================================================
        // PRODUCT SELECT2
        // ================================================================
        function initProductSelect(id) {
            $(`#pselect_${id}`).select2({
                placeholder: "Cari SKU / Nama Barang",
                multiple: true,
                width: '100%',
                closeOnSelect: false,
                ajax: {
                    url: "/api/product/search",
                    dataType: "json",
                    delay: 150,
                    cache: true,
                    data: params => ({
                        q: params.term,
                        page: params.page || 1
                    }),
                    processResults: data => ({
                        results: data.map(p => ({
                            id: p.id,
                            text: "#" + p.sku + " - " + p.nama_barang + " | Stok: " + p.stok
                                .jumlah_stok + " | " + formatRupiah(p.harga_2),
                            product: p
                        }))
                    })
                }
            });
            $(`#pselect_${id}`).on('select2:select', function(e) {
                const product = e.params.data.product;
                addItemToCard(id, product, 1);
                let sel = $(this).val();
                sel.pop();
                $(this).val(sel).trigger('change');
            });
        }

        // ================================================================
        // BARCODE
        // ================================================================
        function initBarcodeListeners(id) {
            let timer10;
            $(`#barcode_${id}`).on('input', function() {
                clearTimeout(timer10);
                timer10 = setTimeout(() => {
                    const sku = $(this).val().trim();
                    if (!sku) return;
                    $.get('/api/product/barcode/' + sku, p => addItemToCard(id, p, 1));
                    $(this).val('').focus();
                }, 200);
            });
            $(`#barcode10_${id}`).on('input', function() {
                clearTimeout(timer10);
                timer10 = setTimeout(() => {
                    const sku = $(this).val().trim();
                    if (!sku) return;
                    $.get('/api/product/barcode/' + sku, p => addItemToCard(id, p, 10));
                    $(this).val('').focus();
                }, 200);
            });
        }

        // ================================================================
        // RESI / PESANAN GLOBAL
        // Tambahan: cek duplicate saat user edit manual lalu Enter
        // ================================================================
        function initResiPesananListeners(id) {
            $(`#resi_global_${id}`).on('keydown', function(e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                const val = $(this).val().trim();
                if (!val) return;

                // Cek duplicate dengan exclude card ini sendiri
                const dup = checkDuplicateResi(val, '', id);
                if (dup.isDuplicate) {
                    Toast.fire({
                        icon: 'warning',
                        title: 'No. Resi sudah dipakai!',
                        html: `<b>${val}</b> sudah ada di card lain.`,
                    });
                    highlightExistingCard(dup.existingCard);
                    $(this).val('').focus();
                    return;
                }

                $(`#tbody_${id} .nomor_resi`).val(val);
                getResiState(id).resi = val;
            });

            $(`#pesanan_global_${id}`).on('keydown', function(e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                const val = $(this).val().trim();
                if (!val) return;

                // Cek duplicate dengan exclude card ini sendiri
                const dup = checkDuplicateResi('', val, id);
                if (dup.isDuplicate) {
                    Toast.fire({
                        icon: 'warning',
                        title: 'No. Pesanan sudah dipakai!',
                        html: `<b>${val}</b> sudah ada di card lain.`,
                    });
                    highlightExistingCard(dup.existingCard);
                    $(this).val('').focus();
                    return;
                }

                $(`#tbody_${id} .nomor_pesanan`).val(val);
                getResiState(id).pesanan = val;
            });
        }

        // ================================================================
        // DRAFT
        // ================================================================
        function initDraftListener(id) {
            $(`#draft_${id}`).on('change', function() {
                const draft = $(this).val() === 'yes';
                const state = getResiState(id);
                Object.values(state.items).forEach(item => {
                    const row = $(`#row_${id}_${item.id}`);
                    draft ? row.removeClass('table-danger') :
                        (item.qty > item.stok && row.addClass('table-danger'));
                });
            });
        }

        function getResiState(id) {
            return resiList.find(r => r.uid === id);
        }

        // ================================================================
        // ADD ITEM TO CARD
        // ================================================================
        function addItemToCard(cardId, product, qty) {
            const state = getResiState(cardId);
            if (!state) return;

            const stok = product.stok.jumlah_stok;
            const min = product.stok_minimum ?? 1;

            // ── Auto-set draft jika stok habis atau di bawah minimum ──
            if (stok <= 0 || stok < min) {
                $(`#draft_${cardId}`).val('yes').trigger('change');
            }

            const draft = isDraftModeFor(cardId);

            if (!validateStockFor(product, draft)) return;

            if (state.items[product.id]) {
                let newQty = state.items[product.id].qty + qty;
                if (!draft && newQty > stok) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Qty melebihi stok tersedia'
                    });
                    return;
                }
                if (draft && newQty > stok)
                    Toast.fire({
                        icon: 'warning',
                        title: `Qty ${newQty} melebihi stok, dicatat draft.`
                    });
                state.items[product.id].qty = newQty;
                $(`#row_${cardId}_${product.id} .qty`).val(newQty);
                updateRowCard(cardId, product.id);
                flashRowCard(cardId, product.id);
                const ok = updateStockUsage(cardId, product.id, newQty, stok);
                if (!ok) Toast.fire({
                    icon: 'warning',
                    title: `Stok konflik lintas resi!`,
                    html: `SKU <b>#${product.sku}</b> — total: <b>${getTotalUsed(product.id)}</b>, stok: <b>${stok}</b>`
                });
                return;
            }

            const nomor_urut = Object.keys(state.items).length + 1;

            const hargaAktif = getModeHarga() === 'harga_1' ?
                (product.harga_1 ?? product.harga_2) :
                product.harga_2;

            state.items[product.id] = {
                id: product.id,
                sku: product.sku,
                nama_barang: product.nama_barang,
                stok,
                harga_1: product.harga_1 ?? 0,
                harga_2: product.harga_2,
                harga_aktif: hargaAktif,
                qty,
                nomor_urut
            };
            const ok = updateStockUsage(cardId, product.id, qty, stok);
            if (!ok) Toast.fire({
                icon: 'warning',
                title: `Stok konflik lintas resi!`,
                html: `SKU <b>#${product.sku}</b> — total: <b>${getTotalUsed(product.id)}</b>, stok: <b>${stok}</b>`
            });

            renderRowCard(cardId, product.id);

            // ── [BARU] Tandai row merah jika stok <= 0 atau < min ─────
            markLowStockRow(cardId, product.id, stok, min);

            if (stockWarnings.length > 0) {
                renderStockWarningsOnly();
            }
        }

        function markLowStockRow(cardId, productId, stok, min) {
            if (stok > 0 && stok >= min) return; // stok normal, tidak perlu ditandai

            const row = $(`#row_${cardId}_${productId}`);
            if (!row.length) return;

            // Tambah background merah ke seluruh row
            row.addClass('table-danger');

            // Buat label keterangan yang jelas
            const reasonLabel = stok <= 0 ?
                `Stok habis (${stok})` :
                `Di bawah minimum (stok: ${stok}, min: ${min})`;

            // Hapus badge lama supaya tidak dobel
            row.find('.stock-low-badge').remove();

            // Sisipkan badge merah di kolom Stok (kolom ke-6)
            row.find('td:nth-child(6)').append(
                `<span class="stock-low-badge badge badge-danger d-block mt-1"
                    style="font-size:.72rem; white-space:nowrap;">
                    ⚠ ${reasonLabel}
                </span>`
            );

            // Simpan warning ke array global (cek duplikat)
            const warnKey = `${cardId}_${productId}`;
            const item = getResiState(cardId)?.items[productId];
            if (item && !stockWarnings.find(w => w.key === warnKey)) {
                stockWarnings.push({
                    key: warnKey,
                    cardId,
                    productId,
                    sku: item.sku,
                    nama: item.nama_barang,
                    stok,
                    min,
                    reason: reasonLabel,
                });
            }
        }



        function flashRowCard(cardId, productId) {
            const row = $(`#row_${cardId}_${productId}`);
            row.addClass('flash-row');
            setTimeout(() => row.removeClass('flash-row'), 500);
        }

        function renderRowCard(cardId, productId) {
            const item = getResiState(cardId).items[productId];
            const resiVal = $(`#resi_global_${cardId}`).val();
            const pesananVal = $(`#pesanan_global_${cardId}`).val();
            const row = `
<tr id="row_${cardId}_${productId}">
    <td><input type="text" class="form-control form-control-sm nomor_resi"    data-card="${cardId}" value="${resiVal}"></td>
    <td><input type="text" class="form-control form-control-sm nomor_pesanan" data-card="${cardId}" value="${pesananVal}"></td>
    <td><input type="text" class="form-control form-control-sm nomor_transaksi" value="${item.nomor_urut}"></td>
    <td>${item.sku}</td><td>${item.nama_barang}</td><td>${item.stok}</td>
    <td>${formatRupiah(item.harga_aktif)}</td>
    <td><input type="number" class="form-control form-control-sm qty"
               data-card="${cardId}" data-id="${productId}" value="${item.qty}" min="1"></td>
    <td class="row-total">${formatRupiah(item.harga_aktif * item.qty)}</td>
    <td><button class="btn btn-danger btn-sm remove-item" data-card="${cardId}" data-id="${productId}">X</button></td>
</tr>`;
            $(`#tbody_${cardId}`).append(row);
            calcSubtotal(cardId);
        }


        function updateRowCard(cardId, productId) {
            const item = getResiState(cardId).items[productId];
            $(`#row_${cardId}_${productId} .row-total`).text(formatRupiah(item.qty * (item.harga_aktif ?? item.harga_2)));
            calcSubtotal(cardId);
        }

        function calcSubtotal(cardId) {
            const state = getResiState(cardId);
            if (!state) return;
            let sub = 0;
            Object.values(state.items).forEach(i => sub += i.qty * (i.harga_aktif ?? i.harga_2));
            $(`#subtotal_${cardId}`).val(formatRupiah(sub));
            recalcGrandTotal();
        }

        function reOrderNomorUrutCard(cardId) {
            const state = getResiState(cardId);
            if (!state) return;
            let idx = 1;
            $(`#tbody_${cardId} tr`).each(function() {
                const productId = $(this).attr('id').split('_').pop();
                state.items[productId].nomor_urut = idx;
                $(this).find('.nomor_transaksi').val(idx++);
            });
        }

        function validateStockFor(product, isDraft) {
            const stok = product.stok.jumlah_stok;
            const min = product.stok_minimum ?? 1;

            if (stok <= 0) {
                Toast.fire({
                    icon: 'warning',
                    title: `SKU #${product.sku} stok habis — otomatis draft.`
                });
            } else if (stok < min) {
                Toast.fire({
                    icon: 'warning',
                    title: `SKU #${product.sku} stok di bawah minimum — otomatis draft.`
                });
            } else if (stok <= 5) {
                Toast.fire({
                    icon: 'warning',
                    title: `SKU #${product.sku} stok kritis (${stok}).`
                });
            } else if (stok <= 10) {
                Toast.fire({
                    icon: 'warning',
                    title: `SKU #${product.sku} stok menipis (${stok}).`
                });
            }

            return true; // ✅ selalu boleh masuk
        }

        // ================================================================
        // DELEGATED EVENTS
        // ================================================================
        $(document).on('change', '.qty', function() {
            const cardId = $(this).data('card');
            const productId = $(this).data('id');
            const state = getResiState(cardId);
            const val = parseInt($(this).val());
            const item = state.items[productId];
            const draft = isDraftModeFor(cardId);
            if (!val || val <= 0) {
                $(this).val(1);
                item.qty = 1;
                Toast.fire({
                    icon: 'error',
                    title: 'Qty tidak boleh 0 atau minus!'
                });
                updateRowCard(cardId, productId);
                return;
            }
            if (!draft && val > item.stok) {
                Toast.fire({
                    icon: 'error',
                    title: `Qty melebihi stok (${item.stok})`
                });
                $(this).val(item.qty);
                return;
            }
            if (draft && val > item.stok)
                Toast.fire({
                    icon: 'warning',
                    title: `Qty ${val} melebihi stok (${item.stok}), dicatat draft.`
                });
            item.qty = val;
            updateRowCard(cardId, productId);
            const ok = updateStockUsage(cardId, productId, val, item.stok);
            if (!ok) Toast.fire({
                icon: 'warning',
                title: `Stok konflik lintas resi!`,
                html: `SKU <b>#${item.sku}</b> — total: <b>${getTotalUsed(productId)}</b>, stok: <b>${item.stok}</b>`
            });
        });

        $(document).on('keydown', '.qty', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).trigger('change');
            }
        });

        $(document).on('click', '.remove-item', function() {
            const cardId = $(this).data('card');
            const productId = $(this).data('id');
            delete getResiState(cardId).items[productId];
            $(this).closest('tr').remove();
            removeStockUsage(cardId, productId);
            reOrderNomorUrutCard(cardId);
            calcSubtotal(cardId);
        });

        $('#btn_add_resi_manual, #btn_add_resi_bottom').on('click', function() {
            addResiCard();
            $('html, body').animate({
                scrollTop: $('.resi-card').last().offset().top - 80
            }, 300);
        });

        $('#btn_apply_ds_all').on('click', function() {
            const dsId = $('#set_all_dropshipper').val();
            if (!dsId) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Pilih dropshipper dulu!'
                });
                return;
            }
            resiList.forEach(r => $(`#dropshipper_${r.uid}`).val(dsId).trigger('change'));
            $('#ds_apply_feedback').show();
            setTimeout(() => $('#ds_apply_feedback').hide(), 2000);
        });

        // ================================================================
        // MODE SELECTOR
        // ================================================================
        function getImportMode() {
            return document.querySelector('input[name="import_mode"]:checked')?.value ?? 'shopee';
        }
        $('input[name="import_mode"]').on('change', function() {
            const mode = this.value;
            $('.mode-card').removeClass('active');
            $(`#mode_card_${mode}`).addClass('active');
            $('#import_mode_badge').text(mode === 'shopee' ? 'Shopee' : 'TikTok J&T');
        });
        $('.mode-card[id^="mode_card_shopee"], .mode-card[id^="mode_card_tiktok"]').on('click', function() {
            const mode = $(this).attr('id').replace('mode_card_', '');
            $(`#import_mode_${mode}`).prop('checked', true).trigger('change');
        });

        // ================================================================
        // IMPORT MULTIPLE RESI
        // ================================================================
        // $('#btn_import_multiple').on('click', async function() {
        //     const file = document.getElementById('file_multiple_resi').files[0];
        //     if (!file) { Swal.fire('Oops!', 'Pilih file resi terlebih dahulu.', 'warning'); return; }

        //     const mode      = getImportMode();
        //     const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        //     $('#import-loading').addClass('active');
        //     $('#loading-text').text(`Mengirim file ke server OCR (${mode})...`);
        //     clearInfoPanel();

        //     const formData = new FormData();
        //     formData.append('file', file);
        //     formData.append('mode', mode);

        //     try {
        //         const response = await fetch('/api/penjualan/import/multiple-resi', {
        //             method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData,
        //         });
        //         const result = await response.json();
        //         $('#import-loading').removeClass('active');

        //         if (!response.ok || !result.success) {
        //             Swal.fire('Gagal', result.message ?? 'Terjadi kesalahan.', 'error'); return;
        //         }

        //         const backendWarnings  = result.warnings ?? [];
        //         const extraErrors      = [];
        //         const skippedDuplicates = [];

        //         if (!result.data || result.data.length === 0) {
        //             renderInfoPanel(backendWarnings, [], { total: 0, skipped: result.total_skipped ?? 0 });
        //             Swal.fire('Info', 'Tidak ada resi yang berhasil diimport.', 'info');
        //             return;
        //         }

        //         for (const resiData of result.data) {
        //             const resiVal    = (resiData.resi     ?? '').trim();
        //             const pesananVal = (resiData.order_id ?? '').trim();

        //             // ── Cek duplicate sebelum buat card ──────────────────
        //             const dup = checkDuplicateResi(resiVal, pesananVal);
        //             if (dup.isDuplicate) {
        //                 const label = dup.type === 'resi' ? 'No. Resi' : 'No. Pesanan';
        //                 skippedDuplicates.push(
        //                     `Hal. ${resiData.page}: ${label} <strong>${dup.value}</strong> sudah ada di list, dilewati.`
        //                 );
        //                 continue;
        //             }

        //             // ── Buat card (skipDupCheck=true karena sudah dicek) ──
        //             const cardId = addResiCard(resiVal, pesananVal, true);
        //             if (!cardId) continue;

        //             // Inject gambar resi
        //             if (resiData.image_base64) {
        //                 const filename = `resi_page${resiData.page}_${resiVal || 'unknown'}.jpg`;
        //                 storeBase64ForCard(cardId, resiData.image_base64, filename);
        //                 showFilePreview(cardId, resiData.image_base64, filename);
        //             }

        //             // Lookup & inject produk
        //             if (resiData.items && resiData.items.length > 0) {
        //                 for (const ocrItem of resiData.items) {
        //                     const sku = ocrItem.sku;
        //                     if (!sku) continue;
        //                     try {
        //                         const res      = await fetch(`/api/product/search?q=${encodeURIComponent(sku)}`);
        //                         const products = await res.json();
        //                         if (!products || products.length === 0) {
        //                             extraErrors.push(`Hal. ${resiData.page}: SKU <strong>${sku}</strong> tidak ditemukan.`);
        //                             continue;
        //                         }
        //                         const product = products.find(p => p.sku === sku) ?? products[0];
        //                         if (product.sku !== sku)
        //                             extraErrors.push(`Hal. ${resiData.page}: SKU <strong>${sku}</strong> tidak exact, pakai <strong>${product.sku}</strong>.`);
        //                         addItemToCard(cardId, product, ocrItem.qty ?? 1);
        //                         setTimeout(() => {
        //                             if (resiVal)    $(`#row_${cardId}_${product.id} .nomor_resi`).val(resiVal);
        //                             if (pesananVal) $(`#row_${cardId}_${product.id} .nomor_pesanan`).val(pesananVal);
        //                         }, 100);
        //                     } catch (err) {
        //                         extraErrors.push(`Hal. ${resiData.page}: Gagal cari SKU <strong>${sku}</strong> — ${err.message}`);
        //                     }
        //                 }
        //             }
        //         }

        //         // Gabungkan duplicate notices ke panel
        //         skippedDuplicates.forEach(msg => extraErrors.push(msg));

        //         renderInfoPanel(backendWarnings, extraErrors, {
        //             total  : result.total - skippedDuplicates.length,
        //             skipped: (result.total_skipped ?? 0) + skippedDuplicates.length,
        //         });
        //         document.getElementById('file_multiple_resi').value = '';
        //         $('html, body').animate({ scrollTop: $('#import-info-panel').offset().top - 80 }, 400);

        //     } catch (err) {
        //         $('#import-loading').removeClass('active');
        //         Swal.fire('Error', 'Koneksi gagal: ' + err.message, 'error');
        //     }
        // });

        // ================================================================
        // FORM SUBMIT
        // ================================================================
        $('#form_multiple_resi').on('submit', function(e) {
            e.preventDefault();
            if (resiList.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tambahkan minimal 1 resi!'
                });
                return;
            }

            let valid = true;
            let errors = [];

            Object.entries(stockUsageMap).forEach(([productId, entry]) => {
                const totalUsed = getTotalUsed(productId);
                if (totalUsed > entry.stok) {
                    // Cek apakah semua card yang memakai produk ini sudah draft
                    const allDraft = Object.keys(entry.usedByCard).every(cid => isDraftModeFor(cid));
                    if (!allDraft) {
                        valid = false;
                        errors.push(
                            `Konflik stok lintas resi: total qty <b>${totalUsed}</b> melebihi stok <b>${entry.stok}</b>. Set semua resi terkait ke <b>Draft</b> untuk tetap menyimpan.`
                        );
                    }
                }
            });

            resiList.forEach((r, idx) => {
                const draft = isDraftModeFor(r.uid);
                if (Object.keys(r.items).length === 0) {
                    valid = false;
                    errors.push(`Resi #${idx + 1}: belum ada barang.`);
                    return;
                }
                Object.values(r.items).forEach(item => {
                    if (!draft && item.qty > item.stok) {
                        valid = false;
                        errors.push(
                            `Resi #${idx + 1} SKU #${item.sku}: qty ${item.qty} melebihi stok ${item.stok}`
                        );
                    }
                });
            });

            if (!valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: errors.map(m => `<p>❌ ${m}</p>`).join('')
                });
                return;
            }

            const payload = resiList.map(r => {
                const items_arr = [];
                $(`#tbody_${r.uid} tr`).each(function() {
                    const productId = $(this).attr('id').split('_').pop();
                    const item = r.items[productId];
                    items_arr.push({
                        id: productId,
                        qty: $(this).find('.qty').val(),
                        harga_2: item.harga_aktif ?? item.harga_2,
                        nomor_resi: $(this).find('.nomor_resi').val(),
                        nomor_pesanan: $(this).find('.nomor_pesanan').val(),
                        nomor_transaksi: $(this).find('.nomor_transaksi').val()
                    });
                });
                const imgData = resiImageMap[r.uid];
                return {
                    resi: $(`#resi_global_${r.uid}`).val(),
                    dropshipper_id: $(`#dropshipper_${r.uid}`).val(),
                    tanggal: $(`#tanggal_${r.uid}`).val() + ' ' + ($(`#jam_${r.uid}`).val() || '00:00') +
                        ':00',
                    kode_penjualan: $(`#kode_${r.uid}`).val(),
                    keterangan: $(`#keterangan_${r.uid}`).val(),
                    scan_out: $(`#scan_out_${r.uid}`).val(),
                    is_draft: $(`#draft_${r.uid}`).val(),
                    items: items_arr,
                    total_harga: Object.values(r.items).reduce((s, i) => s + i.qty * i.harga_aktif ?? i
                        .harga_2, 0),
                    file_resi_base64: imgData ? imgData.raw : null,
                    file_resi_name: imgData ? imgData.filename : null,
                };
            });

            $('#payload_input').val(JSON.stringify(payload));

            Swal.fire({
                title: 'Simpan semua penjualan?',
                html: `Total <strong>${resiList.length}</strong> transaksi akan disimpan.
                <br><small class="text-muted">Resi duplikat akan dilewati secara otomatis.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then(res => {
                if (res.isConfirmed) this.submit();
            });
        });
        // ================================================================
        // REALTIME CLOCK — update semua input jam setiap detik
        // ================================================================
        function getNowTime() {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');
        }

        const dirtyJamInputs = new Set();

        // Tandai dirty saat user mulai fokus ke input (bukan setelah blur)
        $(document).on('focus', '[id^="jam_"]', function() {
            dirtyJamInputs.add(this.id);
        });

        setInterval(() => {
            const nowTime = getNowTime();
            $('[id^="jam_"]').each(function() {
                if (!dirtyJamInputs.has(this.id)) {
                    this.value = nowTime;
                }
            });
        }, 1000);

        // ── Progress helpers ─────────────────────────────────────────────

        function importProgressReset() {
            $('#import-progress-bar').css('width', '0%');
            $('#loading-pct').text('0%');
            $('#loading-step-label').text('Mengirim file ke server...');
            $('#loading-title').text('Membaca file resi...');
            $('#loading-subtitle').text('Mohon tunggu sebentar.');
            $('#loading-log').html('');
        }

        /**
         * Update progress bar
         * @param {number} pct   - 0-100
         * @param {string} label - teks di bawah bar (kiri)
         */
        function importProgressSet(pct, label) {
            const p = Math.min(100, Math.max(0, Math.round(pct)));
            $('#import-progress-bar').css('width', p + '%');
            $('#loading-pct').text(p + '%');
            if (label !== undefined) $('#loading-step-label').text(label);
            // Ubah warna saat selesai
            if (p >= 100) {
                $('#import-progress-bar')
                    .removeClass('bg-primary progress-bar-animated')
                    .addClass('bg-success');
            }
        }

        /**
         * Tambah baris ke log scroll
         * @param {string} msg
         * @param {'ok'|'skip'|'err'|'info'|'active'} type
         */
        function importProgressLog(msg, type = 'info') {
            const logEl = document.getElementById('loading-log');
            const line = document.createElement('div');
            line.className = `log-line log-${type}`;

            const icons = {
                ok: '✓',
                skip: '⏭',
                err: '✗',
                info: '·',
                active: '▶'
            };
            line.innerHTML = `${icons[type] ?? '·'} ${msg}`;
            logEl.appendChild(line);
            // Auto-scroll ke bawah
            logEl.scrollTop = logEl.scrollHeight;
        }

        // ── Ganti handler import dengan versi progress ───────────────────
        // (Ini adalah full replacement untuk $('#btn_import_multiple').on('click'))

        $('#btn_import_multiple').off('click').on('click', async function() {
            const file = document.getElementById('file_multiple_resi').files[0];
            if (!file) {
                Swal.fire('Oops!', 'Pilih file resi terlebih dahulu.', 'warning');
                return;
            }

            const mode = getImportMode();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            // ── Reset modal & tampilkan ───────────────────────────────────
            modalReset();
            $('#modal-import-progress').modal('show');

            modalLog(`File: ${file.name} (${(file.size / 1024).toFixed(0)} KB)`, 'info');
            modalLog(`Mode: ${mode === 'shopee' ? 'Shopee' : 'TikTok J&T'}`, 'info');
            modalSet(5, 'Mengirim file ke server...');
            clearInfoPanel();
            stockWarnings = [];

            // ── Step 1: Submit job (async, dapat job_id) ──────────────────
            const formData = new FormData();
            formData.append('file', file);
            formData.append('mode', mode);

            let submitResult;
            try {
                modalLog('Mengirim file ke FastAPI...', 'active');
                const res = await fetch("{{ route('api.import.multi-job') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData,
                });
                submitResult = await res.json();

                if (!res.ok || !submitResult.success) {
                    modalLog(`Gagal: ${submitResult.message ?? 'Server error'}`, 'err');
                    modalDone(true);
                    return;
                }
            } catch (err) {
                modalLog(`Koneksi gagal: ${err.message}`, 'err');
                modalDone(true);
                return;
            }

            const {
                job_id,
                total_pages
            } = submitResult;
            modalLog(`Job dibuat: ${job_id.substring(0, 8)}...`, 'ok');
            modalLog(`Estimasi: ${total_pages ?? '?'} halaman`, 'info');
            modalSet(10, 'File diterima — menunggu worker...');

            // ── Step 2: Mulai polling ─────────────────────────────────────
            await startPolling(job_id, total_pages);
        });


        function renderStockWarningsOnly() {
            if (stockWarnings.length === 0) return;

            // Hapus blok warning stok lama (jika ada) lalu sisipkan ulang
            $('#import-info-panel .stock-warn-section').remove();

            const section = $(`
        <div class="stock-warn-section">
            <div style="margin: 10px 0 6px; font-size:.72rem; font-weight:700;
                        text-transform:uppercase; letter-spacing:.08em;
                        color:#dc3545; border-bottom:1px solid #f5c6cb;
                        padding-bottom:4px;">
                <i class="feather icon-alert-octagon mr-1"></i>
                Peringatan Stok (${stockWarnings.length} item)
            </div>
        </div>
    `);

            stockWarnings.forEach(w => {
                section.append(`
            <div class="info-item info-error">
                <i class="feather icon-alert-octagon text-danger"></i>
                <div>
                    SKU <strong>#${w.sku}</strong> — ${w.nama}:
                    <span class="text-danger font-weight-bold">${w.reason}</span>.
                    Card otomatis diset ke mode <strong>Draft</strong>.
                    Baris ditandai <span style="background:#f8d7da;padding:1px 6px;border-radius:3px;font-size:.8em;">merah</span>.
                </div>
            </div>
        `);
            });

            $('#import-info-list').append(section);
            $('#import-info-panel').addClass('has-content');
        }

        // ================================================================
        // MODE HARGA
        // ================================================================
        function getModeHarga() {
            return document.querySelector('input[name="mode_harga"]:checked')?.value ?? 'harga_2';
        }

        function getHargaByMode(product) {
            return getModeHarga() === 'harga_1' ?
                (product.harga_1 ?? product.harga_2) :
                product.harga_2;
        }

        // Listener: saat mode harga berubah, update semua item di semua card
        $('input[name="mode_harga"]').on('change', function() {
            const mode = this.value;

            // Update tampilan card selector
            $('.mode-card[id^="mode_card_harga_"]').removeClass('active');
            $(`#mode_card_${mode}`).addClass('active');

            // Update semua item di semua resi
            resiList.forEach(r => {
                Object.values(r.items).forEach(item => {
                    const newHarga = mode === 'harga_1' ?
                        (item.harga_1 ?? item.harga_2) :
                        item.harga_2;

                    item.harga_aktif = newHarga;

                    // Update tampilan harga di row
                    const row = $(`#row_${r.uid}_${item.id}`);
                    row.find('td:nth-child(7)').text(formatRupiah(newHarga)); // kolom Harga
                    row.find('td.row-total').text(formatRupiah(newHarga * item.qty));
                });
                calcSubtotal(r.uid);
            });

            Toast.fire({
                icon: 'info',
                title: `Mode harga diubah ke ${mode === 'harga_1' ? 'Harga HPP' : 'Harga Reseller'}`
            });
        });

        $('.mode-card[id^="mode_card_harga_"]').on('click', function() {
            const mode = $(this).attr('id').replace('mode_card_', '');
            $(`#mode_harga_${mode.replace('harga_', '')}`).prop('checked', true).trigger('change');
        });


        // ── Tutup modal = stop polling ────────────────────────────────────
        $('#modal-import-progress').on('hide.bs.modal', function() {
            stopPolling();
        });
    </script>
@endsection
