@extends('layouts.main')

@section('style')
<style>
    .retur-header-card {
        background: linear-gradient(135deg, #fff5f5 0%, #fff 60%);
        border-left: 4px solid #dc3545;
    }

    .item-retur-row {
        transition: background .2s;
    }

    .item-retur-row:hover {
        background: #fff8f8;
    }

    .item-retur-row.selected {
        background: #fff3cd;
    }

    .item-retur-row.disabled td {
        opacity: .45;
        pointer-events: none;
    }

    .file-drop-area {
        border: 2px dashed #dc3545;
        border-radius: 10px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        background: #fff5f5;
        transition: background .2s, border-color .2s;
        position: relative;
    }

    .file-drop-area:hover,
    .file-drop-area.dragover {
        background: #ffe5e5;
        border-color: #b21c2c;
    }

    .file-drop-area input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    #file-preview-container {
        margin-top: 12px;
    }

    #file-preview-container img,
    #file-preview-container video {
        max-width: 100%;
        max-height: 260px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .badge-status {
        font-size: .75rem;
        padding: 4px 10px;
        border-radius: 20px;
    }

    .qty-input {
        width: 80px;
    }

    .check-col {
        width: 40px;
        text-align: center;
    }

    .info-penjualan span {
        font-weight: 600;
    }

    .existing-file-card {
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 10px 12px;
        background: #fafafa;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .existing-file-card img,
    .existing-file-card video {
        max-height: 60px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .replace-file-toggle {
        cursor: pointer;
        color: #dc3545;
        font-size: .8rem;
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="layout-content">
    <div class="container-fluid flex-grow-1 container-p-y">

        {{-- BREADCRUMB --}}
        <h4 class="font-weight-bold py-3 mb-0">Edit Retur Penjualan</h4>
        <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('penjualan.index') }}">Penjualan</a></li>
                <li class="breadcrumb-item"><a href="{{ route('laporan.retur.show', $retur->id) }}">Detail Retur</a></li>
                <li class="breadcrumb-item active">Edit Retur</li>
            </ol>
        </div>

        {{-- ERROR --}}
        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="feather icon-x-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <form action="{{ route('laporan.retur.update', $retur->id) }}" method="POST"
            enctype="multipart/form-data" id="formRetur">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- ============================================================
                     KOLOM KIRI — Info Penjualan + Form Retur
                     ============================================================ --}}
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
                                    <span>{{ $retur->penjualan->kode_penjualan ?? '-' }}</span>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Tanggal Penjualan</div>
                                    <span>{{ date('d M Y', strtotime($retur->penjualan->tanggal)) }}</span>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Dropshipper</div>
                                    <span>{{ $retur->penjualan->dropshipper->nama ?? '-' }}</span>
                                </div>
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">Total Harga</div>
                                    <span class="text-danger">Rp {{ number_format($retur->penjualan->total_harga, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FORM DATA RETUR --}}
                    <div class="card mb-4">
                        <h6 class="card-header">
                            <i class="feather icon-edit mr-2 text-danger"></i>
                            Data Retur
                        </h6>
                        <div class="card-body">
                            <div class="form-row">

                                {{-- TANGGAL RETUR --}}
                                <div class="form-group col-md-6">
                                    <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_retur" class="form-control"
                                        value="{{ old('tanggal_retur', \Carbon\Carbon::parse($retur->tanggal_retur)->format('Y-m-d')) }}"
                                        required>
                                    @error('tanggal_retur')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- STATUS --}}
                                <div class="form-group col-md-6">
                                    <label class="form-label">Status Retur <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        @foreach(['pending' => 'Pending', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $val => $label)
                                            <option value="{{ $val }}"
                                                {{ old('status', $retur->status) === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- ALASAN RETUR --}}
                                <div class="form-group col-md-12">
                                    <label class="form-label">Alasan Retur <span class="text-danger">*</span></label>
                                    <textarea name="alasan_retur" class="form-control" rows="3"
                                        placeholder="Jelaskan alasan retur secara singkat dan jelas..."
                                        required>{{ old('alasan_retur', $retur->alasan_retur) }}</textarea>
                                    @error('alasan_retur')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- TABEL ITEM RETUR --}}
                    <div class="card mb-4">
                        <h6 class="card-header d-flex align-items-center justify-content-between">
                            <span><i class="feather icon-list mr-2"></i>Item yang Diretur</span>
                            <small class="text-muted font-weight-normal">Centang item yang ingin diretur</small>
                        </h6>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" id="tableItemRetur">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="check-col">
                                                <input type="checkbox" id="checkAll" title="Pilih semua">
                                            </th>
                                            <th>SKU</th>
                                            <th>Nama Barang</th>
                                            <th>No. Resi</th>
                                            <th>Qty Beli</th>
                                            <th width="100">Qty Retur</th>
                                            <th>Keterangan Item</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // Build a lookup: penjualan_detail_id => retur detail
                                            $returDetailMap = $retur->detail->keyBy('penjualan_detail_id');
                                        @endphp

                                        @foreach ($retur->penjualan->detail as $detail)
                                        @php
                                            $existingDetail = $returDetailMap->get($detail->id);
                                            $isChecked      = $existingDetail !== null;
                                        @endphp
                                        <tr class="item-retur-row {{ $isChecked ? 'selected' : 'disabled' }}"
                                            data-id="{{ $detail->id }}">
                                            <td class="check-col">
                                                <input type="checkbox" class="item-check"
                                                    data-id="{{ $detail->id }}"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                            </td>
                                            <td>{{ $detail->barang->sku ?? '-' }}</td>
                                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                            <td>{{ $detail->nomor_resi ?? '-' }}</td>
                                            <td>{{ $detail->qty }}</td>
                                            <td>
                                                <input type="number"
                                                    name="items[{{ $loop->index }}][qty_retur]"
                                                    class="form-control form-control-sm qty-input"
                                                    value="{{ old('items.' . $loop->index . '.qty_retur', $existingDetail->qty_retur ?? 1) }}"
                                                    min="1"
                                                    max="{{ $detail->qty }}"
                                                    {{ $isChecked ? '' : 'disabled' }}>
                                                {{-- hidden fields --}}
                                                <input type="hidden"
                                                    name="items[{{ $loop->index }}][penjualan_detail_id]"
                                                    value="{{ $detail->id }}"
                                                    {{ $isChecked ? '' : 'disabled' }}>
                                                <input type="hidden"
                                                    name="items[{{ $loop->index }}][barang_id]"
                                                    value="{{ $detail->barang_id }}"
                                                    {{ $isChecked ? '' : 'disabled' }}>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="items[{{ $loop->index }}][keterangan]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Opsional..."
                                                    value="{{ old('items.' . $loop->index . '.keterangan', $existingDetail->keterangan ?? '') }}"
                                                    {{ $isChecked ? '' : 'disabled' }}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('items')
                                <div class="px-3 pb-2">
                                    <small class="text-danger">{{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                    </div>

                </div>

                {{-- ============================================================
                     KOLOM KANAN — Upload File + Aksi
                     ============================================================ --}}
                <div class="col-lg-4">

                    {{-- UPLOAD FILE --}}
                    <div class="card mb-4">
                        <h6 class="card-header">
                            <i class="feather icon-paperclip mr-2"></i>
                            Lampiran Bukti <small class="text-muted">(Opsional)</small>
                        </h6>
                        <div class="card-body">

                            {{-- FILE EXISTING --}}
                            @if ($retur->file_path)
                                <div id="existingFileSection">
                                    <p class="small text-muted mb-1">File saat ini:</p>
                                    <div class="existing-file-card">
                                        @if ($retur->isImage())
                                            <a href="{{ Storage::url($retur->file_path) }}" target="_blank">
                                                <img src="{{ Storage::url($retur->file_path) }}"
                                                    alt="{{ $retur->file_original_name }}">
                                            </a>
                                        @elseif ($retur->isVideo())
                                            <video src="{{ Storage::url($retur->file_path) }}" controls></video>
                                        @endif
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="small font-weight-bold text-truncate">
                                                {{ $retur->file_original_name ?? 'File Lampiran' }}
                                            </div>
                                            <div class="small text-muted">{{ $retur->file_mime }}</div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="replace-file-toggle" id="toggleReplaceFile">
                                            <i class="feather icon-refresh-cw"></i> Ganti file
                                        </span>
                                        <label class="d-flex align-items-center small text-danger mb-0"
                                            style="cursor:pointer; gap:5px;">
                                            <input type="checkbox" name="hapus_file" value="1"
                                                id="checkHapusFile" class="mr-1">
                                            Hapus file ini
                                        </label>
                                    </div>
                                </div>

                                <div id="newFileSection" style="display:none;">
                            @else
                                <div id="newFileSection">
                            @endif

                                <div class="file-drop-area" id="fileDropArea">
                                    <input type="file" name="file" id="fileInput"
                                        accept="image/*,video/*">
                                    <div id="filePlaceholder">
                                        <i class="feather icon-upload-cloud" style="font-size:2rem; color:#dc3545;"></i>
                                        <p class="mb-1 mt-2 font-weight-bold">Klik atau drag &amp; drop</p>
                                        <small class="text-muted">Gambar atau Video (maks. 50 MB)</small>
                                        <br>
                                        <small class="text-muted">JPG, PNG, GIF, WEBP, MP4, MOV, AVI</small>
                                    </div>
                                </div>

                                <div id="file-preview-container" class="d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted" id="file-name-label"></small>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            id="btnRemoveFile">
                                            <i class="feather icon-trash-2"></i>
                                        </button>
                                    </div>
                                    <div id="file-preview"></div>
                                </div>

                            </div>

                            @error('file')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror

                        </div>
                    </div>

                    {{-- RINGKASAN --}}
                    <div class="card mb-4">
                        <h6 class="card-header">
                            <i class="feather icon-info mr-2"></i>
                            Ringkasan
                        </h6>
                        <div class="card-body small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Item Dipilih</span>
                                <span id="summaryItemCount" class="font-weight-bold">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Qty Retur</span>
                                <span id="summaryQtyTotal" class="font-weight-bold">0</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">No. Retur</span>
                                <span class="font-weight-bold">#{{ $retur->id }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Dibuat Oleh</span>
                                <span class="font-weight-bold">{{ $retur->createdBy->nama ?? '-' }}</span>
                            </div>
                            <hr class="my-2">
                            <small class="text-muted">
                                Pastikan item yang diretur sudah benar sebelum menyimpan perubahan.
                            </small>
                        </div>
                    </div>

                    {{-- TOMBOL AKSI --}}
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-danger btn-block" id="btnSimpan">
                            <i class="feather icon-save mr-1"></i>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('laporan.retur.show', $retur->id) }}"
                            class="btn btn-secondary btn-block">
                            <i class="feather icon-arrow-left mr-1"></i>
                            Kembali ke Detail
                        </a>
                    </div>

                </div>

            </div><!-- /row -->

        </form>

    </div>

    @include('components.footer')
</div>
@endsection

@section('scripts')
<script>
// ============================================================
// CHECKBOX — aktifkan / nonaktifkan input di baris
// ============================================================
function setRowEnabled(row, enabled) {
    row.find('input:not([type="checkbox"]), select, textarea').prop('disabled', !enabled);
    if (enabled) {
        row.removeClass('disabled').addClass('selected');
    } else {
        row.addClass('disabled').removeClass('selected');
    }
    updateSummary();
}

$('#checkAll').on('change', function () {
    let checked = $(this).is(':checked');
    $('.item-check').prop('checked', checked);
    $('.item-retur-row').each(function () {
        setRowEnabled($(this), checked);
    });
});

$(document).on('change', '.item-check', function () {
    let row = $(this).closest('tr');
    setRowEnabled(row, $(this).is(':checked'));
    let total   = $('.item-check').length;
    let checked = $('.item-check:checked').length;
    $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
    $('#checkAll').prop('checked', checked === total && total > 0);
});

// Sinkronkan checkAll state saat halaman load
$(function () {
    let total   = $('.item-check').length;
    let checked = $('.item-check:checked').length;
    $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
    $('#checkAll').prop('checked', checked === total && total > 0);
    updateSummary();
});

// ============================================================
// RINGKASAN
// ============================================================
function updateSummary() {
    let itemCount = 0;
    let qtyTotal  = 0;
    $('.item-check:checked').each(function () {
        let row = $(this).closest('tr');
        itemCount++;
        qtyTotal += parseInt(row.find('.qty-input').val()) || 0;
    });
    $('#summaryItemCount').text(itemCount);
    $('#summaryQtyTotal').text(qtyTotal);
}

$(document).on('input', '.qty-input', function () {
    updateSummary();
});

// ============================================================
// TOGGLE GANTI FILE
// ============================================================
$('#toggleReplaceFile').on('click', function () {
    let section = $('#newFileSection');
    if (section.is(':visible')) {
        section.hide();
        $(this).html('<i class="feather icon-refresh-cw"></i> Ganti file');
    } else {
        section.show();
        $('#checkHapusFile').prop('checked', false);
        $(this).html('<i class="feather icon-x"></i> Batal ganti');
    }
});

$('#checkHapusFile').on('change', function () {
    if ($(this).is(':checked')) {
        $('#newFileSection').hide();
        if ($('#toggleReplaceFile').length) {
            $('#toggleReplaceFile').html('<i class="feather icon-refresh-cw"></i> Ganti file');
        }
    }
});

// ============================================================
// FILE UPLOAD PREVIEW
// ============================================================
const fileInput       = document.getElementById('fileInput');
const fileDropArea    = document.getElementById('fileDropArea');
const previewContainer= document.getElementById('file-preview-container');
const previewBox      = document.getElementById('file-preview');
const fileNameLabel   = document.getElementById('file-name-label');
const filePlaceholder = document.getElementById('filePlaceholder');

if (fileInput) {
    fileInput.addEventListener('change', function () {
        handleFile(this.files[0]);
    });
}

if (fileDropArea) {
    ['dragenter','dragover'].forEach(ev => {
        fileDropArea.addEventListener(ev, e => {
            e.preventDefault();
            fileDropArea.classList.add('dragover');
        });
    });
    ['dragleave','drop'].forEach(ev => {
        fileDropArea.addEventListener(ev, e => {
            e.preventDefault();
            fileDropArea.classList.remove('dragover');
        });
    });
    fileDropArea.addEventListener('drop', function (e) {
        let file = e.dataTransfer.files[0];
        if (file) {
            let dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            handleFile(file);
        }
    });
}

function handleFile(file) {
    if (!file) return;
    fileNameLabel.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    previewBox.innerHTML = '';

    if (file.type.startsWith('image/')) {
        let img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        previewBox.appendChild(img);
    } else if (file.type.startsWith('video/')) {
        let vid = document.createElement('video');
        vid.src = URL.createObjectURL(file);
        vid.controls = true;
        previewBox.appendChild(vid);
    } else {
        previewBox.innerHTML = '<span class="text-muted small">File tidak dapat dipreview.</span>';
    }

    if (filePlaceholder) filePlaceholder.classList.add('d-none');
    if (previewContainer) previewContainer.classList.remove('d-none');
}

const btnRemoveFile = document.getElementById('btnRemoveFile');
if (btnRemoveFile) {
    btnRemoveFile.addEventListener('click', function () {
        fileInput.value = '';
        previewBox.innerHTML = '';
        previewContainer.classList.add('d-none');
        filePlaceholder.classList.remove('d-none');
    });
}

// ============================================================
// FORM SUBMIT VALIDATION
// ============================================================
$('#formRetur').on('submit', function (e) {
    let checkedItems = $('.item-check:checked').length;

    if (checkedItems === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Item Retur',
            text: 'Centang minimal 1 item yang ingin diretur.',
        });
        return false;
    }

    let valid  = true;
    let errors = [];

    $('.item-check:checked').each(function () {
        let row     = $(this).closest('tr');
        let qtyBeli = parseInt(row.find('td:nth-child(5)').text());
        let qtyRetur= parseInt(row.find('.qty-input').val());
        let sku     = row.find('td:nth-child(2)').text().trim();

        if (!qtyRetur || qtyRetur <= 0) {
            valid = false;
            errors.push('SKU ' + sku + ': qty retur harus lebih dari 0');
        } else if (qtyRetur > qtyBeli) {
            valid = false;
            errors.push('SKU ' + sku + ': qty retur (' + qtyRetur + ') melebihi qty beli (' + qtyBeli + ')');
        }
    });

    if (!valid) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            html: errors.map(m => `<p>❌ ${m}</p>`).join(''),
        });
        return false;
    }
});
</script>
@endsection