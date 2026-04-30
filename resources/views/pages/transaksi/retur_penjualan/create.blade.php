@extends('layouts.main')

@section('style')
<style>
    .retur-header-card {
        background: linear-gradient(135deg, #fff5f5 0%, #fff 60%);
        border-left: 4px solid #dc3545;
    }

    .item-retur-row { transition: background .2s; }
    .item-retur-row:hover { background: #fff8f8; }
    .item-retur-row.selected { background: #fff3cd; }
    .item-retur-row.disabled td { opacity: .45; pointer-events: none; }

    .qty-input { width: 80px; }
    .check-col { width: 40px; text-align: center; }
    .info-penjualan span { font-weight: 600; }

    /* ── LAMPIRAN TABS ── */
    .bukti-tabs {
        display: flex;
        border-bottom: 2px solid #f0f0f0;
        margin-bottom: 14px;
        gap: 4px;
    }
    .bukti-tab-btn {
        flex: 1;
        padding: 8px 6px;
        border: none;
        background: #f8f8f8;
        border-radius: 8px 8px 0 0;
        font-size: .8rem;
        font-weight: 600;
        color: #888;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .bukti-tab-btn.active { background: #dc3545; color: #fff; }
    .bukti-tab-btn:hover:not(.active) { background: #ffe5e5; color: #dc3545; }
    .bukti-tab-panel { display: none; }
    .bukti-tab-panel.active { display: block; }

    /* ── FILE DROP AREA ── */
    .file-drop-area {
        border: 2px dashed #dc3545;
        border-radius: 10px;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        background: #fff5f5;
        transition: background .2s, border-color .2s;
        position: relative;
    }
    .file-drop-area:hover, .file-drop-area.dragover { background: #ffe5e5; border-color: #b21c2c; }
    .file-drop-area input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }

    /* ── KAMERA ── */
    .camera-wrap {
        border-radius: 10px; overflow: hidden; background: #111; position: relative; min-height: 40px;
    }
    .camera-controls { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .camera-controls .btn { flex: 1; font-size: .78rem; }

    .rec-dot {
        display: inline-block; width: 10px; height: 10px;
        background: red; border-radius: 50%; margin-right: 4px;
        animation: blink 1s infinite;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

    /* ── PREVIEW RESULT ── */
    .result-preview {
        margin-top: 10px; border-radius: 8px; overflow: hidden;
        border: 1px solid #ddd; position: relative;
    }
    .result-preview img, .result-preview video {
        width: 100%; max-height: 220px; object-fit: cover; display: block;
    }
    .result-preview .btn-remove-result {
        position: absolute; top: 6px; right: 6px;
        background: rgba(0,0,0,.55); color: #fff; border: none;
        border-radius: 50%; width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: .8rem;
    }
    .result-preview .btn-remove-result:hover { background: #dc3545; }

    #file-preview-container img, #file-preview-container video {
        max-width: 100%; max-height: 220px;
        border-radius: 8px; border: 1px solid #ddd; margin-top: 8px;
    }

    .camera-status {
        font-size: .75rem; text-align: center;
        padding: 4px 0; color: #888; min-height: 22px;
    }
</style>
@endsection

@section('content')
<div class="layout-content">
    <div class="container-fluid flex-grow-1 container-p-y">

        <h4 class="font-weight-bold py-3 mb-0">Retur Penjualan</h4>
        <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('penjualan.index') }}">Penjualan</a></li>
                <li class="breadcrumb-item active">Form Retur</li>
            </ol>
        </div>

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="feather icon-x-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <form action="{{ route('penjualan.retur.store', $penjualan->id) }}" method="POST"
            enctype="multipart/form-data" id="formRetur">
            @csrf

            {{-- SATU file input untuk semua sumber (upload, foto, video) --}}
            {{-- Kamera dan foto akan inject blob ke sini via DataTransfer --}}
            <input type="file" name="file" id="masterFileInput" style="display:none" accept="image/*,video/*">

            <div class="row">

                <!-- ============ KIRI ============ -->
                <div class="col-lg-8">

                    {{-- INFO PENJUALAN --}}
                    <div class="card mb-4 retur-header-card">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-3">
                                <i class="feather icon-shopping-bag text-danger mr-1"></i>
                                Informasi Penjualan
                            </h6>
                            <div class="row info-penjualan small">
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Kode Penjualan</div>
                                    <span>{{ $penjualan->kode_penjualan ?? '-' }}</span>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Tanggal</div>
                                    <span>{{ date('d M Y', strtotime($penjualan->tanggal)) }}</span>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Dropshipper</div>
                                    <span>{{ $penjualan->dropshipper->nama ?? '-' }}</span>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Total Harga</div>
                                    <span class="text-danger">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FORM DATA RETUR --}}
                    <div class="card mb-4">
                        <h6 class="card-header">
                            <i class="feather icon-rotate-ccw mr-2 text-danger"></i>
                            Data Retur
                        </h6>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_retur" class="form-control"
                                        value="{{ old('tanggal_retur', date('Y-m-d')) }}" required>
                                    @error('tanggal_retur')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label">Status Retur <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="pending"  {{ old('status','pending') == 'pending'  ? 'selected' : '' }}>Pending</option>
                                        <option value="diproses" {{ old('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                        <option value="selesai"  {{ old('status') == 'selesai'  ? 'selected' : '' }}>Selesai</option>
                                        <option value="ditolak"  {{ old('status') == 'ditolak'  ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                    @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="form-group col-md-12">
                                    <label class="form-label">Alasan Retur <span class="text-danger">*</span></label>
                                    <textarea name="alasan_retur" class="form-control" rows="3"
                                        placeholder="Jelaskan alasan retur secara singkat dan jelas..."
                                        required>{{ old('alasan_retur') }}</textarea>
                                    @error('alasan_retur')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL ITEM RETUR --}}
                    <div class="card mb-4">
                        <h6 class="card-header d-flex align-items-center justify-content-between">
                            <span><i class="feather icon-list mr-2"></i>Pilih Item yang Diretur</span>
                            <small class="text-muted font-weight-normal">Centang item yang ingin diretur</small>
                        </h6>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" id="tableItemRetur">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="check-col"><input type="checkbox" id="checkAll" title="Pilih semua"></th>
                                            <th>SKU</th>
                                            <th>Nama Barang</th>
                                            <th>No. Resi</th>
                                            <th>Qty Beli</th>
                                            <th width="100">Qty Retur</th>
                                            <th>Keterangan Item</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($penjualan->detail as $detail)
                                        <tr class="item-retur-row disabled" data-id="{{ $detail->id }}">
                                            <td class="check-col">
                                                <input type="checkbox" class="item-check" data-id="{{ $detail->id }}">
                                            </td>
                                            <td>{{ $detail->barang->sku ?? '-' }}</td>
                                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                            <td>{{ $detail->nomor_resi ?? '-' }}</td>
                                            <td>{{ $detail->qty }}</td>
                                            <td>
                                                <input type="number"
                                                    name="items[{{ $loop->index }}][qty_retur]"
                                                    class="form-control form-control-sm qty-input"
                                                    value="1" min="1" max="{{ $detail->qty }}" disabled>
                                                <input type="hidden" name="items[{{ $loop->index }}][penjualan_detail_id]" value="{{ $detail->id }}" disabled>
                                                <input type="hidden" name="items[{{ $loop->index }}][barang_id]" value="{{ $detail->barang_id }}" disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $loop->index }}][keterangan]"
                                                    class="form-control form-control-sm" placeholder="Opsional..." disabled>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('items')
                                <div class="px-3 pb-2"><small class="text-danger">{{ $message }}</small></div>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- ============ KANAN ============ -->
                <div class="col-lg-4">

                    {{-- LAMPIRAN BUKTI --}}
                    <div class="card mb-4">
                        <h6 class="card-header">
                            <i class="feather icon-paperclip mr-2"></i>
                            Lampiran Bukti <small class="text-muted">(Opsional)</small>
                        </h6>
                        <div class="card-body">

                            {{-- TABS --}}
                            <div class="bukti-tabs">
                                <button type="button" class="bukti-tab-btn active" data-tab="upload">
                                    <i class="feather icon-upload"></i> Upload
                                </button>
                                <button type="button" class="bukti-tab-btn" data-tab="foto">
                                    <i class="feather icon-camera"></i> Foto
                                </button>
                                <button type="button" class="bukti-tab-btn" data-tab="video">
                                    <i class="feather icon-video"></i> Video
                                </button>
                            </div>

                            {{-- ── PANEL: UPLOAD ── --}}
                            <div class="bukti-tab-panel active" id="panel-upload">
                                <div class="file-drop-area" id="fileDropArea">
                                    {{-- input ini adalah TRIGGER klik saja, bukan yang disubmit --}}
                                    <input type="file" id="fileInputTrigger" accept="image/*,video/*">
                                    <div id="filePlaceholder">
                                        <i class="feather icon-upload-cloud" style="font-size:2rem; color:#dc3545;"></i>
                                        <p class="mb-1 mt-2 font-weight-bold">Klik atau drag &amp; drop</p>
                                        <small class="text-muted">Gambar atau Video (maks. 50 MB)</small><br>
                                        <small class="text-muted">JPG, PNG, GIF, WEBP, MP4, MOV, AVI</small>
                                    </div>
                                </div>
                                <div id="file-preview-container" class="d-none">
                                    <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                                        <small class="text-muted" id="file-name-label"></small>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRemoveFile">
                                            <i class="feather icon-trash-2"></i>
                                        </button>
                                    </div>
                                    <div id="file-preview"></div>
                                </div>
                            </div>

                            {{-- ── PANEL: FOTO ── --}}
                            <div class="bukti-tab-panel" id="panel-foto">
                                <div id="fotoCameraSection">
                                    <div class="camera-wrap" id="fotoCameraWrap" style="min-height:0;">
                                        <video id="fotoVideo" autoplay playsinline muted
                                            style="width:100%; max-height:220px; object-fit:cover; border-radius:10px; display:none;"></video>
                                        <canvas id="fotoCanvas" style="display:none;"></canvas>
                                    </div>
                                    <div class="camera-status" id="fotoStatus">Kamera belum aktif</div>
                                    <div class="camera-controls">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnOpenFoto">
                                            <i class="feather icon-camera"></i> Buka Kamera
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger d-none" id="btnCaptureFoto">
                                            <i class="feather icon-aperture"></i> Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnCloseFoto">
                                            <i class="feather icon-x"></i> Tutup
                                        </button>
                                    </div>
                                </div>
                                <div id="fotoResult" class="d-none">
                                    <div class="result-preview">
                                        <img id="fotoResultImg" src="" alt="Foto bukti">
                                        <button type="button" class="btn-remove-result" id="btnRemoveFoto" title="Hapus">
                                            <i class="feather icon-x"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" id="fotoResultLabel"></small>
                                </div>
                            </div>

                            {{-- ── PANEL: VIDEO ── --}}
                            <div class="bukti-tab-panel" id="panel-video">
                                <div id="videoCameraSection">
                                    <div class="camera-wrap" id="videoCameraWrap" style="min-height:0;">
                                        <video id="videoStream" autoplay playsinline muted
                                            style="width:100%; max-height:220px; object-fit:cover; border-radius:10px; display:none;"></video>
                                    </div>
                                    <div class="camera-status" id="videoStatus">Kamera belum aktif</div>
                                    <div class="camera-controls">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnOpenVideo">
                                            <i class="feather icon-video"></i> Buka Kamera
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger d-none" id="btnStartRec">
                                            <i class="feather icon-circle"></i> Mulai Rekam
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning d-none" id="btnStopRec">
                                            <i class="feather icon-square"></i> Stop
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnCloseVideo">
                                            <i class="feather icon-x"></i> Tutup
                                        </button>
                                    </div>
                                </div>
                                <div id="videoResult" class="d-none">
                                    <div class="result-preview">
                                        <video id="videoResultPlayer" controls
                                            style="width:100%; max-height:220px; border-radius:8px;"></video>
                                        <button type="button" class="btn-remove-result" id="btnRemoveVideo" title="Hapus">
                                            <i class="feather icon-x"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1" id="videoResultLabel"></small>
                                </div>
                            </div>

                            @error('file')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror

                        </div>
                    </div>

                    {{-- RINGKASAN --}}
                    <div class="card mb-4">
                        <h6 class="card-header"><i class="feather icon-info mr-2"></i>Ringkasan</h6>
                        <div class="card-body small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Item Dipilih</span>
                                <span id="summaryItemCount" class="font-weight-bold">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Qty Retur</span>
                                <span id="summaryQtyTotal" class="font-weight-bold">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Bukti Terlampir</span>
                                <span id="summaryFileBadge" class="font-weight-bold text-muted">—</span>
                            </div>
                            <hr class="my-2">
                            <small class="text-muted">Pastikan item yang diretur sudah benar sebelum menyimpan.</small>
                        </div>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-danger btn-block" id="btnSimpan">
                            <i class="feather icon-save mr-1"></i> Simpan Retur
                        </button>
                        <a href="{{ route('penjualan.index') }}" class="btn btn-secondary btn-block">
                            <i class="feather icon-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>

                </div>

            </div>
        </form>
    </div>
    @include('components.footer')
</div>
@endsection

@section('scripts')
<script>
// ─────────────────────────────────────────────────────────────
// MASTER FILE INPUT — satu-satunya input[name=file] yang disubmit
// Semua sumber (upload, foto, video) inject blob ke sini.
// ─────────────────────────────────────────────────────────────
const masterInput = document.getElementById('masterFileInput');

/**
 * Inject sebuah Blob / File ke masterInput menggunakan DataTransfer.
 * Ini yang memastikan file benar-benar ikut multipart form.
 */
function injectToMaster(blob, filename) {
    const file = new File([blob], filename, { type: blob.type });
    const dt   = new DataTransfer();
    dt.items.add(file);
    masterInput.files = dt.files;
}

function clearMaster() {
    masterInput.value = '';
}

// ─────────────────────────────────────────────────────────────
// TAB SWITCHING
// ─────────────────────────────────────────────────────────────
document.querySelectorAll('.bukti-tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = this.dataset.tab;
        document.querySelectorAll('.bukti-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.bukti-tab-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('panel-' + target).classList.add('active');
        if (target !== 'foto')  stopFotoStream();
        if (target !== 'video') stopVideoStream();
    });
});

// ─────────────────────────────────────────────────────────────
// CHECKBOX ROWS
// ─────────────────────────────────────────────────────────────
function setRowEnabled(row, enabled) {
    row.find('input:not([type="checkbox"]), select, textarea').prop('disabled', !enabled);
    enabled ? row.removeClass('disabled').addClass('selected')
            : row.addClass('disabled').removeClass('selected');
    updateSummary();
}

$('#checkAll').on('change', function () {
    const checked = $(this).is(':checked');
    $('.item-check').prop('checked', checked);
    $('.item-retur-row').each(function () { setRowEnabled($(this), checked); });
});

$(document).on('change', '.item-check', function () {
    const row     = $(this).closest('tr');
    setRowEnabled(row, $(this).is(':checked'));
    const total   = $('.item-check').length;
    const checked = $('.item-check:checked').length;
    $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
    $('#checkAll').prop('checked', checked === total && total > 0);
});

// ─────────────────────────────────────────────────────────────
// RINGKASAN
// ─────────────────────────────────────────────────────────────
function updateSummary() {
    let itemCount = 0, qtyTotal = 0;
    $('.item-check:checked').each(function () {
        itemCount++;
        qtyTotal += parseInt($(this).closest('tr').find('.qty-input').val()) || 0;
    });
    $('#summaryItemCount').text(itemCount);
    $('#summaryQtyTotal').text(qtyTotal);
}
$(document).on('input', '.qty-input', updateSummary);

function updateFileBadge(label) {
    const el = document.getElementById('summaryFileBadge');
    el.innerHTML = label ? `<span class="badge badge-success">✔ ${label}</span>` : '—';
}

// ─────────────────────────────────────────────────────────────
// TAB UPLOAD — trigger klik ke fileInputTrigger, lalu copy ke master
// ─────────────────────────────────────────────────────────────
const fileInputTrigger = document.getElementById('fileInputTrigger');
const fileDropArea     = document.getElementById('fileDropArea');
const previewContainer = document.getElementById('file-preview-container');
const previewBox       = document.getElementById('file-preview');
const fileNameLabel    = document.getElementById('file-name-label');
const filePlaceholder  = document.getElementById('filePlaceholder');

// Saat user memilih file lewat trigger input, copy ke masterInput
fileInputTrigger.addEventListener('change', function () {
    if (this.files[0]) handleUploadFile(this.files[0]);
});

['dragenter','dragover'].forEach(ev =>
    fileDropArea.addEventListener(ev, e => { e.preventDefault(); fileDropArea.classList.add('dragover'); })
);
['dragleave','drop'].forEach(ev =>
    fileDropArea.addEventListener(ev, e => { e.preventDefault(); fileDropArea.classList.remove('dragover'); })
);
fileDropArea.addEventListener('drop', function (e) {
    const f = e.dataTransfer.files[0];
    if (f) handleUploadFile(f);
});

function handleUploadFile(file) {
    // Inject ke masterInput
    injectToMaster(file, file.name);

    fileNameLabel.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    previewBox.innerHTML = '';
    if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src   = URL.createObjectURL(file);
        previewBox.appendChild(img);
    } else if (file.type.startsWith('video/')) {
        const vid     = document.createElement('video');
        vid.src       = URL.createObjectURL(file);
        vid.controls  = true;
        previewBox.appendChild(vid);
    }
    filePlaceholder.classList.add('d-none');
    previewContainer.classList.remove('d-none');

    // Bersihkan capture kamera jika ada
    resetFotoResult();
    resetVideoResult();
    updateFileBadge('Upload');
}

document.getElementById('btnRemoveFile').addEventListener('click', function () {
    clearMaster();
    fileInputTrigger.value = '';
    previewBox.innerHTML   = '';
    previewContainer.classList.add('d-none');
    filePlaceholder.classList.remove('d-none');
    updateFileBadge(null);
});

// ─────────────────────────────────────────────────────────────
// KAMERA FOTO
// ─────────────────────────────────────────────────────────────
let fotoStream = null;

document.getElementById('btnOpenFoto').addEventListener('click', async function () {
    try {
        fotoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false
        });
        const vid       = document.getElementById('fotoVideo');
        vid.srcObject   = fotoStream;
        vid.style.display = 'block';
        setFotoStatus('Kamera aktif — posisikan barang dengan jelas');
        this.classList.add('d-none');
        document.getElementById('btnCaptureFoto').classList.remove('d-none');
        document.getElementById('btnCloseFoto').classList.remove('d-none');
    } catch (err) {
        setFotoStatus('Gagal: ' + err.message);
    }
});

document.getElementById('btnCaptureFoto').addEventListener('click', function () {
    const vid    = document.getElementById('fotoVideo');
    const canvas = document.getElementById('fotoCanvas');
    canvas.width  = vid.videoWidth;
    canvas.height = vid.videoHeight;
    canvas.getContext('2d').drawImage(vid, 0, 0);

    canvas.toBlob(blob => {
        const ts   = new Date().toISOString().replace(/[:.]/g, '-');
        const name = 'foto-retur-' + ts + '.jpg';

        // ✅ Inject langsung ke masterInput (bukan base64!)
        injectToMaster(blob, name);

        const url = URL.createObjectURL(blob);
        document.getElementById('fotoResultImg').src              = url;
        document.getElementById('fotoResultLabel').textContent    = name + ' (' + (blob.size / 1024).toFixed(0) + ' KB)';
        document.getElementById('fotoResult').classList.remove('d-none');

        clearUploadTab();
        resetVideoResult();
        updateFileBadge('Foto');
        stopFotoStream();
        setFotoStatus('Foto berhasil diambil');
    }, 'image/jpeg', 0.92);
});

document.getElementById('btnCloseFoto').addEventListener('click', stopFotoStream);

document.getElementById('btnRemoveFoto').addEventListener('click', function () {
    resetFotoResult();
    clearMaster();
    updateFileBadge(null);
});

function stopFotoStream() {
    if (fotoStream) { fotoStream.getTracks().forEach(t => t.stop()); fotoStream = null; }
    const vid         = document.getElementById('fotoVideo');
    vid.srcObject     = null;
    vid.style.display = 'none';
    document.getElementById('btnOpenFoto').classList.remove('d-none');
    document.getElementById('btnCaptureFoto').classList.add('d-none');
    document.getElementById('btnCloseFoto').classList.add('d-none');
}

function resetFotoResult() {
    document.getElementById('fotoResultImg').src = '';
    document.getElementById('fotoResult').classList.add('d-none');
}

function setFotoStatus(msg) {
    document.getElementById('fotoStatus').textContent = msg;
}

// ─────────────────────────────────────────────────────────────
// KAMERA VIDEO
// ─────────────────────────────────────────────────────────────
let videoStream    = null;
let mediaRecorder  = null;
let recordedChunks = [];
let recTimer       = null;
let recSeconds     = 0;

document.getElementById('btnOpenVideo').addEventListener('click', async function () {
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: true
        });
        const vid         = document.getElementById('videoStream');
        vid.srcObject     = videoStream;
        vid.style.display = 'block';
        setVideoStatus('Kamera aktif — siap merekam');
        this.classList.add('d-none');
        document.getElementById('btnStartRec').classList.remove('d-none');
        document.getElementById('btnCloseVideo').classList.remove('d-none');
    } catch (err) {
        setVideoStatus('Gagal: ' + err.message);
    }
});

document.getElementById('btnStartRec').addEventListener('click', function () {
    recordedChunks = [];
    recSeconds     = 0;

    const mimeType = getSupportedMime();
    try {
        mediaRecorder = new MediaRecorder(videoStream, mimeType ? { mimeType } : {});
    } catch (e) {
        mediaRecorder = new MediaRecorder(videoStream);
    }

    mediaRecorder.ondataavailable = e => { if (e.data && e.data.size > 0) recordedChunks.push(e.data); };
    mediaRecorder.onstop          = finishVideoRecording;
    mediaRecorder.start(500); // collect setiap 500ms

    recTimer = setInterval(() => {
        recSeconds++;
        const m = String(Math.floor(recSeconds / 60)).padStart(2,'0');
        const s = String(recSeconds % 60).padStart(2,'0');
        document.getElementById('videoStatus').innerHTML =
            `<span class="rec-dot"></span> Merekam… ${m}:${s}`;
    }, 1000);

    this.classList.add('d-none');
    document.getElementById('btnStopRec').classList.remove('d-none');
});

document.getElementById('btnStopRec').addEventListener('click', function () {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
    clearInterval(recTimer);
    this.classList.add('d-none');
    setVideoStatus('Memproses video…');
});

document.getElementById('btnCloseVideo').addEventListener('click', stopVideoStream);

document.getElementById('btnRemoveVideo').addEventListener('click', function () {
    resetVideoResult();
    clearMaster();
    updateFileBadge(null);
});

function finishVideoRecording() {
    if (recordedChunks.length === 0) {
        setVideoStatus('Tidak ada data video yang terekam.');
        return;
    }

    const mimeType = (mediaRecorder && mediaRecorder.mimeType) ? mediaRecorder.mimeType : 'video/webm';
    const ext      = mimeType.includes('mp4') ? 'mp4' : 'webm';
    const blob     = new Blob(recordedChunks, { type: mimeType });
    const ts       = new Date().toISOString().replace(/[:.]/g, '-');
    const name     = 'video-retur-' + ts + '.' + ext;

    // ✅ Inject langsung ke masterInput (bukan base64!)
    injectToMaster(blob, name);

    const url = URL.createObjectURL(blob);
    document.getElementById('videoResultPlayer').src           = url;
    document.getElementById('videoResultLabel').textContent    = name + ' (' + (blob.size / 1024 / 1024).toFixed(2) + ' MB)';
    document.getElementById('videoResult').classList.remove('d-none');

    clearUploadTab();
    resetFotoResult();
    updateFileBadge('Video');
    stopVideoStream();
    setVideoStatus('Video berhasil direkam (' + (blob.size / 1024 / 1024).toFixed(2) + ' MB)');
}

function stopVideoStream() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
    clearInterval(recTimer);
    if (videoStream) { videoStream.getTracks().forEach(t => t.stop()); videoStream = null; }
    const vid         = document.getElementById('videoStream');
    vid.srcObject     = null;
    vid.style.display = 'none';
    document.getElementById('btnOpenVideo').classList.remove('d-none');
    document.getElementById('btnStartRec').classList.add('d-none');
    document.getElementById('btnStopRec').classList.add('d-none');
    document.getElementById('btnCloseVideo').classList.add('d-none');
}

function resetVideoResult() {
    const player = document.getElementById('videoResultPlayer');
    player.pause();
    player.src = '';
    document.getElementById('videoResult').classList.add('d-none');
}

function setVideoStatus(msg) {
    document.getElementById('videoStatus').textContent = msg;
}

function getSupportedMime() {
    const types = [
        'video/mp4;codecs=h264',
        'video/mp4',
        'video/webm;codecs=vp9,opus',
        'video/webm;codecs=vp8,opus',
        'video/webm',
    ];
    return types.find(t => MediaRecorder.isTypeSupported(t)) || '';
}

// ─────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────
function clearUploadTab() {
    fileInputTrigger.value = '';
    previewBox.innerHTML   = '';
    previewContainer.classList.add('d-none');
    filePlaceholder.classList.remove('d-none');
}

// ─────────────────────────────────────────────────────────────
// FORM SUBMIT VALIDATION
// ─────────────────────────────────────────────────────────────
$('#formRetur').on('submit', function (e) {
    if ($('.item-check:checked').length === 0) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Pilih Item Retur', text: 'Centang minimal 1 item yang ingin diretur.' });
        return false;
    }

    let valid = true, errors = [];
    $('.item-check:checked').each(function () {
        const row      = $(this).closest('tr');
        const qtyBeli  = parseInt(row.find('td:nth-child(5)').text());
        const qtyRetur = parseInt(row.find('.qty-input').val());
        const sku      = row.find('td:nth-child(2)').text().trim();
        if (!qtyRetur || qtyRetur <= 0) { valid = false; errors.push('SKU ' + sku + ': qty retur harus > 0'); }
        else if (qtyRetur > qtyBeli)    { valid = false; errors.push('SKU ' + sku + ': qty retur (' + qtyRetur + ') > qty beli (' + qtyBeli + ')'); }
    });

    if (!valid) {
        e.preventDefault();
        Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: errors.map(m => `<p>❌ ${m}</p>`).join('') });
        return false;
    }
});
</script>
@endsection