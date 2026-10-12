@extends('layouts.main')

@section('content')
    <style>
        .dropzone-box {
            position: relative;
            width: 100%;
            min-height: 220px;
            border: 2px dashed #ced4da;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            transition: border-color .2s, background .2s;
        }
        .dropzone-box:hover { border-color: #26B4FF; background: #f1faff; }
        .dropzone-box.is-dragover { border-color: #26B4FF; background: #e3f5ff; border-style: solid; }
        .dropzone-box.has-file { border-style: solid; border-color: #ced4da; background: #fff; }
        .dropzone-box.is-invalid { border-color: #d9534f; }
        /* input file menutupi seluruh area: klik tetap membuka dialog & validasi "required" tetap jalan */
        .dropzone-box input[type=file] {
            position: absolute; inset: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; z-index: 1;
        }
        .dropzone-placeholder { padding: 24px; pointer-events: none; }
        .dropzone-placeholder .dz-icon { font-size: 40px; color: #adb5bd; line-height: 1; }
        .dropzone-preview { display: none; width: 100%; height: 100%; position: absolute; inset: 0; }
        .dropzone-box.has-file .dropzone-preview { display: block; }
        .dropzone-box.has-file .dropzone-placeholder { visibility: hidden; }
        .dropzone-preview img { width: 100%; height: 100%; object-fit: contain; background: #f8f9fa; }
        .dropzone-info {
            position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
            background: rgba(0, 0, 0, .6); color: #fff; font-size: 12px;
            padding: 6px 40px 6px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            pointer-events: none;
        }
        .dropzone-remove {
            position: absolute; top: 8px; right: 8px; z-index: 3; display: none;
            width: 30px; height: 30px; padding: 0; border-radius: 50%; line-height: 28px;
        }
        .dropzone-box.has-file .dropzone-remove { display: block; }
    </style>

    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Kelola Banner</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('banner.index') }}">Kelola Banner</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Tambah Banner Baru</h6>
                <div class="card-body">
                    <form action="{{ route('banner.store') }}" method="POST" id="bannerForm" enctype="multipart/form-data">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                    placeholder="Contoh: Promo Akhir Tahun 2026" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active', '1') == '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror"
                                    placeholder="0" value="{{ old('urutan') }}" min="0">
                                <small class="form-text text-muted">Urutan keberapa banner ini ditampilkan di website</small>
                                @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="form-label">Gambar / Banner <span class="text-danger">*</span></label>

                                <div class="dropzone-box @error('gambar') is-invalid @enderror" id="dropzone">
                                    <input type="file" name="gambar" id="gambarInput"
                                        accept="image/jpeg,image/png,image/webp,image/gif" required>

                                    <div class="dropzone-placeholder">
                                        <div class="dz-icon"><i class="feather icon-upload-cloud"></i></div>
                                        <div class="mt-2"><strong>Drag &amp; drop gambar ke sini</strong></div>
                                        <div class="text-muted small">atau klik untuk memilih file</div>
                                        <div class="text-muted small mt-1">jpeg, png, jpg, webp, gif - maks 5MB</div>
                                    </div>

                                    <div class="dropzone-preview" id="dropzonePreview"></div>
                                    <div class="dropzone-info" id="dropzoneInfo"></div>
                                    <button type="button" class="btn btn-danger btn-sm dropzone-remove" id="dropzoneRemove"
                                        title="Hapus gambar">&times;</button>
                                </div>

                                <div class="text-danger small mt-1" id="dropzoneError" style="display:none;"></div>
                                @error('gambar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="4"
                                    placeholder="Deskripsi/keterangan banner (opsional)...">{{ old('catatan') }}</textarea>
                                @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('banner.index') }}" class="btn btn-secondary"><i class="feather icon-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Simpan Banner</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var MAX_SIZE = 5 * 1024 * 1024; // 5MB
            var ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            var zone = document.getElementById('dropzone');
            var input = document.getElementById('gambarInput');
            var preview = document.getElementById('dropzonePreview');
            var info = document.getElementById('dropzoneInfo');
            var removeBtn = document.getElementById('dropzoneRemove');
            var errorBox = document.getElementById('dropzoneError');
            var currentUrl = null;

            function formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }

            function showError(msg) {
                errorBox.textContent = msg;
                errorBox.style.display = 'block';
                zone.classList.add('is-invalid');
            }

            function clearError() {
                errorBox.style.display = 'none';
                zone.classList.remove('is-invalid');
            }

            function reset() {
                input.value = '';
                if (currentUrl) { URL.revokeObjectURL(currentUrl); currentUrl = null; }
                preview.innerHTML = '';
                info.textContent = '';
                info.style.display = 'none';
                zone.classList.remove('has-file');
            }

            function showFile(file) {
                if (currentUrl) URL.revokeObjectURL(currentUrl);
                currentUrl = URL.createObjectURL(file);
                preview.innerHTML = '';
                var img = new Image();
                img.src = currentUrl;
                img.alt = file.name;
                preview.appendChild(img);
                info.textContent = file.name + ' (' + formatSize(file.size) + ')';
                info.style.display = 'block';
                zone.classList.add('has-file');
            }

            function handleFile(file) {
                clearError();
                if (!file) { reset(); return; }

                if (ALLOWED.indexOf(file.type) === -1) {
                    reset();
                    showError('Format file tidak didukung. Gunakan jpeg, png, jpg, webp, atau gif.');
                    return;
                }
                if (file.size > MAX_SIZE) {
                    reset();
                    showError('Ukuran file ' + formatSize(file.size) + ' melebihi batas maksimal 5MB.');
                    return;
                }
                showFile(file);
            }

            // Pilih lewat klik
            input.addEventListener('change', function () {
                handleFile(input.files[0]);
            });

            // Drag & drop
            ['dragenter', 'dragover'].forEach(function (evt) {
                zone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'dragend'].forEach(function (evt) {
                zone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.remove('is-dragover');
                });
            });

            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('is-dragover');

                var files = e.dataTransfer && e.dataTransfer.files;
                if (!files || !files.length) return;

                // Ambil file pertama saja, lalu masukkan ke input agar ikut terkirim saat submit
                var dt = new DataTransfer();
                dt.items.add(files[0]);
                input.files = dt.files;
                handleFile(files[0]);
            });

            // Hapus gambar
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                clearError();
                reset();
            });

            // Cegah browser membuka file jika di-drop di luar area dropzone
            ['dragover', 'drop'].forEach(function (evt) {
                window.addEventListener(evt, function (e) { e.preventDefault(); });
            });
        })();
    </script>
@endsection