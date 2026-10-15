@extends('layouts.main')

@section('style')
<style>
    .foto-dropzone {
        border: 2px dashed #b0bec5;
        border-radius: 8px;
        background-color: #fbfbfc;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .foto-dropzone:hover {
        border-color: #17a2b8;
        background-color: #f4fafc;
    }
    .foto-dropzone.dragover {
        border-color: #17a2b8 !important;
        background-color: #e6f7fa !important;
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2);
        transform: scale(1.005);
    }
    .preview-foto-card, .existing-foto-card {
        position: relative;
        width: 110px;
        height: 140px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        background: #fff;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        user-select: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    }
    .preview-foto-card:hover, .existing-foto-card:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }
    .preview-foto-card.is-dragging, .existing-foto-card.is-dragging {
        opacity: 0.4;
        border: 2px dashed #17a2b8;
        transform: scale(0.96);
    }
    .preview-foto-card.drag-over-card, .existing-foto-card.drag-over-card {
        border: 2px solid #17a2b8 !important;
        box-shadow: 0 0 8px rgba(23, 162, 184, 0.4);
    }
    .foto-card-thumb {
        position: relative;
        width: 100%;
        height: 105px;
        background: #f1f3f5;
        overflow: hidden;
        cursor: grab;
    }
    .foto-card-thumb:active {
        cursor: grabbing;
    }
    .foto-card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }
    .preview-foto-card .btn-hapus-foto, .existing-foto-card .btn-hapus-foto {
        position: absolute;
        top: 3px;
        right: 3px;
        background: rgba(220, 53, 69, 0.9);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 14px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 5;
        transition: background 0.15s, transform 0.15s;
    }
    .preview-foto-card .btn-hapus-foto:hover, .existing-foto-card .btn-hapus-foto:hover {
        background: #c82333;
        transform: scale(1.1);
    }
    .badge-urutan {
        position: absolute;
        top: 4px;
        left: 4px;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 700;
        z-index: 4;
        line-height: 1.2;
    }
    .badge-urutan-utama {
        background: rgba(40, 167, 69, 0.95);
        color: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .badge-urutan-secondary {
        background: rgba(52, 58, 64, 0.85);
        color: #fff;
    }
    .badge-urutan-info {
        background: rgba(23, 162, 184, 0.9);
        color: #fff;
    }
    .foto-card-size {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
        color: #fff;
        font-size: 9px;
        padding: 8px 3px 2px 3px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        pointer-events: none;
    }
    .foto-card-footer {
        height: 35px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6px;
    }
    .btn-shift {
        width: 24px;
        height: 24px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        background: #fff;
        color: #495057;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-shift:hover:not(:disabled) {
        background: #17a2b8;
        border-color: #17a2b8;
        color: #fff;
    }
    .btn-shift:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    .drag-handle-icon {
        color: #adb5bd;
        font-size: 13px;
        cursor: grab;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Produk</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('produk.index') }}">Produk</a></li>
                    <li class="breadcrumb-item active">Edit Produk</li>
                </ol>
            </div>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <h6 class="card-header">
                    <i class="feather icon-package mr-2 text-info"></i>
                    Edit Produk: {{ $produk->nama_produk }}
                </h6>
                <div class="card-body">
                    <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data" id="formProduk">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" class="form-control @error('nama_produk') is-invalid @enderror"
                                    placeholder="Nama produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                                @error('nama_produk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Slug <span class="text-danger">*</span></label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                    placeholder="slug-produk" value="{{ old('slug', $produk->slug) }}" required>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                rows="3" placeholder="Deskripsi produk">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Harga Normal <span class="text-danger">*</span></label>
                                <input type="number" name="harga_normal" id="inputHargaNormal"
                                    class="form-control @error('harga_normal') is-invalid @enderror"
                                    placeholder="Otomatis terisi dari barang terpilih"
                                    value="{{ old('harga_normal', $produk->harga_normal) }}" required>
                                @error('harga_normal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" class="form-control @error('brand_id') is-invalid @enderror">
                                    <option value="">-- Pilih Brand --</option>
                                    @foreach ($brands as $b)
                                        <option value="{{ $b->id }}" {{ old('brand_id', $produk->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->nama_brand }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror">
                                    <option value="aktif" {{ (old('status', $produk->status) === 'aktif') ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ (old('status', $produk->status) === 'nonaktif') ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label font-weight-semibold">Foto Produk</label>
                            <div class="foto-dropzone" id="fotoDropzone">
                                <input type="file" id="fotoInput" name="foto[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">
                                <div class="dropzone-content">
                                    <i class="feather icon-upload-cloud text-info mb-2" style="font-size: 38px; display: inline-block;"></i>
                                    <p class="mb-1 text-dark font-weight-semibold" style="font-size: 14px;">
                                        Tarik &amp; lepas foto di sini, atau <span class="text-info font-weight-bold">pilih dari perangkat</span>
                                    </p>
                                    <p class="text-muted small mb-0">Format: JPG, JPEG, PNG, WEBP &bull; Maksimal 5MB per foto &bull; Bisa pilih lebih dari 1 foto</p>
                                </div>
                            </div>
                            <div id="fotoUploadError" class="alert alert-warning py-1 px-3 small mt-2" style="display: none;"></div>
                            @error('foto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            @error('foto.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                            <div id="wrapperPreviewFotoBaru" class="mt-3" style="display: none;">
                                <label class="form-label small text-muted font-weight-bold mb-2">
                                    Foto Baru yang Akan Ditambahkan (<span class="text-success font-weight-bold">#1 = Foto Utama jika belum ada foto</span> &bull; Tarik atau tekan &larr; &rarr; untuk atur urutan):
                                </label>
                                <div id="previewFotoBaru" class="d-flex flex-wrap" style="gap: 12px;"></div>
                            </div>

                            {{-- Hidden inputs hapus & urutan existing foto --}}
                            <input type="hidden" name="hapus_foto_ids" id="hapusFotoIds" value="">
                            <input type="hidden" name="existing_foto_order" id="existingFotoOrder"
                                value="{{ json_encode($produk->foto->pluck('id')) }}">

                            @if ($produk->foto->isNotEmpty())
                                <div class="mt-3" id="wrapperExistingFoto">
                                    <label class="form-label small text-muted font-weight-bold mb-2">
                                        Foto Saat Ini (<span class="text-success font-weight-bold">#1 = Foto Utama</span> &bull; Tarik atau tekan &larr; &rarr; untuk atur urutan &bull; Klik <span class="text-danger">&times;</span> untuk hapus):
                                    </label>
                                    <div id="existingFotoContainer" class="d-flex flex-wrap" style="gap: 12px;">
                                        @foreach ($produk->foto as $loopIndex => $f)
                                            <div class="existing-foto-card"
                                                 data-foto-id="{{ $f->id }}"
                                                 data-order="{{ $loopIndex }}"
                                                 draggable="true">
                                                <div class="foto-card-thumb">
                                                    <img src="{{ asset('storage/' . $f->foto) }}" alt="Foto #{{ $loopIndex + 1 }}">
                                                    <span class="badge-urutan {{ $loopIndex === 0 ? 'badge-urutan-utama' : 'badge-urutan-secondary' }}">
                                                        @if($loopIndex === 0)
                                                            <i class="feather icon-star mr-1"></i>#1 Utama
                                                        @else
                                                            #{{ $loopIndex + 1 }}
                                                        @endif
                                                    </span>
                                                    <button type="button" class="btn-hapus-foto"
                                                            onclick="hapusFotoExisting(this)"
                                                            title="Hapus foto ini">&times;</button>
                                                    <div class="foto-card-size" title="{{ $f->foto }}">
                                                        #{{ $loopIndex + 1 }}
                                                    </div>
                                                </div>
                                                <div class="foto-card-footer">
                                                    <button type="button" class="btn-shift"
                                                            onclick="geserFotoExisting(this, -1)"
                                                            title="Geser ke kiri"
                                                            {{ $loopIndex === 0 ? 'disabled' : '' }}>
                                                        <i class="feather icon-chevron-left"></i>
                                                    </button>
                                                    <span class="drag-handle-icon" title="Tarik & lepas untuk atur urutan">
                                                        <i class="feather icon-move"></i>
                                                    </span>
                                                    <button type="button" class="btn-shift"
                                                            onclick="geserFotoExisting(this, 1)"
                                                            title="Geser ke kanan"
                                                            {{ $loopIndex === $produk->foto->count() - 1 ? 'disabled' : '' }}>
                                                        <i class="feather icon-chevron-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr class="my-3">
                        <h6 class="text-info mb-3"><i class="feather icon-box mr-1"></i> Spesifikasi Produk</h6>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="form-label">Berat (gram)</label>
                                <input type="number" name="berat_gram" class="form-control @error('berat_gram') is-invalid @enderror"
                                    placeholder="0" min="0" value="{{ old('berat_gram', $produk->berat_gram) }}">
                                @error('berat_gram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Panjang (cm)</label>
                                <input type="number" name="panjang_cm" class="form-control @error('panjang_cm') is-invalid @enderror"
                                    placeholder="0" min="0" value="{{ old('panjang_cm', $produk->panjang_cm) }}">
                                @error('panjang_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Lebar (cm)</label>
                                <input type="number" name="lebar_cm" class="form-control @error('lebar_cm') is-invalid @enderror"
                                    placeholder="0" min="0" value="{{ old('lebar_cm', $produk->lebar_cm) }}">
                                @error('lebar_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Tinggi (cm)</label>
                                <input type="number" name="tinggi_cm" class="form-control @error('tinggi_cm') is-invalid @enderror"
                                    placeholder="0" min="0" value="{{ old('tinggi_cm', $produk->tinggi_cm) }}">
                                @error('tinggi_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <hr class="my-3">

                        <div class="form-group">
                            <label class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                            <select class="form-control" id="selectBarang" style="width:100%"></select>
                            <small class="text-muted">Cari barang, lalu pilih. Sistem akan otomatis memilih barang dengan kata pertama atau kedua yang sama.</small>
                            @error('barang_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <div id="selectedBarangList"></div>
                        </div>


                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="feather icon-save mr-1"></i> Update Produk
                            </button>
                            <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelector('input[name="nama_produk"]')?.addEventListener('input', function() {
            if (document.querySelector('input[name="slug"]').dataset.manuallyEdited) return;
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            document.querySelector('input[name="slug"]').value = slug;
        });

        document.querySelector('input[name="slug"]')?.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
        });

        let selectedBarang = [];

        @foreach ($produk->varian as $v)
            selectedBarang.push({
                id: {{ $v->barang_id }},
                text: '{{ $v->barang->nama_barang }}',
                harga_reseller: {{ $v->barang->harga_2 ?? 0 }}
            });
        @endforeach
        renderSelectedBarang();
        updateHargaNormal();

        function renderSelectedBarang() {
            const container = document.getElementById('selectedBarangList');
            container.innerHTML = '';
            let ids = [];
            selectedBarang.forEach((b, i) => {
                ids.push(b.id);
                const el = document.createElement('div');
                el.className = 'd-flex align-items-center justify-content-between px-3 py-2 mb-1';
                el.style.cssText = 'background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;';
                el.innerHTML = '<span>' + b.text + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBarang(' + i + ')">' +
                    '<i class="feather icon-x"></i> Hapus</button>';
                el.dataset.harga = b.harga_reseller || 0;
                container.appendChild(el);
            });

            document.querySelectorAll('input[name="barang_ids[]"]').forEach(el => el.remove());
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'barang_ids[]';
                inp.value = id;
                container.appendChild(inp);
            });
        }

        function removeBarang(index) {
            selectedBarang.splice(index, 1);
            renderSelectedBarang();
            updateHargaNormal();
        }

        function updateHargaNormal() {
            const hargaInput = document.getElementById('inputHargaNormal');
            if (selectedBarang.length > 0) {
                const first = selectedBarang[0];
                const harga = first.harga_reseller || 0;
                hargaInput.value = harga;
            } else {
                hargaInput.value = '';
            }
        }

        $('#selectBarang').select2({
            placeholder: 'Cari barang...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '/api/product/search-grouped',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { q: params.term, produk_id: {{ $produk->id }} };
                },
                processResults: function(data) {
                    return {
                        results: data.filter(function(item) {
                            return !selectedBarang.some(function(b) { return b.id === item.id; });
                        }).map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama_barang,
                                harga_reseller: item.harga_2 || 0
                            };
                        })
                    };
                }
            }
        }).on('select2:open', function() {
            const $search = $(this).data('select2').$dropdown.find('.select2-search__field');
            if ($search.length && !$search.val()) {
                $search.trigger('input');
            }
        }).on('select2:select', function(e) {
            const data = e.params.data;
            const nama = data.text.split(' (')[0];

            fetch('/api/product/search-by-word?nama=' + encodeURIComponent(nama) + '&produk_id={{ $produk->id }}')
                .then(function(res) { return res.json(); })
                .then(function(items) {
                    items.forEach(function(item) {
                        if (!selectedBarang.some(function(b) { return b.id === item.id; })) {
                            selectedBarang.push({
                                id: item.id,
                                text: item.nama_barang,
                                harga_reseller: item.harga_2 || 0
                            });
                        }
                    });
                    renderSelectedBarang();
                    updateHargaNormal();
                    $('#selectBarang').val(null).trigger('change');
                });
        });

        // ===== EXISTING FOTO MANAGEMENT =====
        const hapusFotoIdsInput = document.getElementById('hapusFotoIds');
        const existingFotoOrderInput = document.getElementById('existingFotoOrder');
        const existingContainer = document.getElementById('existingFotoContainer');
        let hapusIds = [];

        function updateHiddenInputs() {
            hapusFotoIdsInput.value = JSON.stringify(hapusIds);
            const remaining = existingContainer
                ? Array.from(existingContainer.querySelectorAll('.existing-foto-card')).map(c => c.dataset.fotoId)
                : [];
            existingFotoOrderInput.value = JSON.stringify(remaining);
        }

        function refreshExistingBadges() {
            if (!existingContainer) return;
            const cards = existingContainer.querySelectorAll('.existing-foto-card');
            cards.forEach((card, idx) => {
                const badge = card.querySelector('.badge-urutan');
                const shiftBtns = card.querySelectorAll('.btn-shift');
                badge.className = 'badge-urutan ' + (idx === 0 ? 'badge-urutan-utama' : 'badge-urutan-secondary');
                badge.innerHTML = idx === 0
                    ? '<i class="feather icon-star mr-1"></i>#1 Utama'
                    : '#' + (idx + 1);
                const sizeDiv = card.querySelector('.foto-card-size');
                if (sizeDiv) sizeDiv.textContent = '#' + (idx + 1);
                shiftBtns[0].disabled = (idx === 0);
                shiftBtns[1].disabled = (idx === cards.length - 1);
            });
            if (window.feather) feather.replace();
            updateHiddenInputs();
        }

        function hapusFotoExisting(btn) {
            const card = btn.closest('.existing-foto-card');
            if (!card) return;
            const id = card.dataset.fotoId;
            hapusIds.push(id);
            card.style.transition = 'all 0.2s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8)';
            setTimeout(() => {
                card.remove();
                refreshExistingBadges();
                const wrapper = document.getElementById('wrapperExistingFoto');
                if (wrapper && existingContainer && existingContainer.querySelectorAll('.existing-foto-card').length === 0) {
                    wrapper.style.display = 'none';
                }
            }, 200);
        }

        function geserFotoExisting(btn, direction) {
            const card = btn.closest('.existing-foto-card');
            if (!card || !existingContainer) return;
            const cards = Array.from(existingContainer.querySelectorAll('.existing-foto-card'));
            const idx = cards.indexOf(card);
            const newIdx = idx + direction;
            if (newIdx < 0 || newIdx >= cards.length) return;
            if (direction === -1) {
                existingContainer.insertBefore(card, cards[newIdx]);
            } else {
                existingContainer.insertBefore(cards[newIdx], card);
            }
            refreshExistingBadges();
        }

        // Drag & drop reorder for existing cards
        if (existingContainer) {
            let dragSrcCard = null;

            existingContainer.addEventListener('dragstart', function(e) {
                dragSrcCard = e.target.closest('.existing-foto-card');
                if (!dragSrcCard) return;
                dragSrcCard.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', dragSrcCard.dataset.fotoId);
            });

            existingContainer.addEventListener('dragend', function(e) {
                if (dragSrcCard) dragSrcCard.classList.remove('is-dragging');
                existingContainer.querySelectorAll('.existing-foto-card').forEach(c => c.classList.remove('drag-over-card'));
                dragSrcCard = null;
            });

            existingContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const target = e.target.closest('.existing-foto-card');
                existingContainer.querySelectorAll('.existing-foto-card').forEach(c => c.classList.remove('drag-over-card'));
                if (target && target !== dragSrcCard) target.classList.add('drag-over-card');
            });

            existingContainer.addEventListener('dragleave', function(e) {
                const target = e.target.closest('.existing-foto-card');
                if (target) target.classList.remove('drag-over-card');
            });

            existingContainer.addEventListener('drop', function(e) {
                e.preventDefault();
                const target = e.target.closest('.existing-foto-card');
                existingContainer.querySelectorAll('.existing-foto-card').forEach(c => c.classList.remove('drag-over-card'));
                if (!target || !dragSrcCard || target === dragSrcCard) return;
                const cards = Array.from(existingContainer.querySelectorAll('.existing-foto-card'));
                const fromIdx = cards.indexOf(dragSrcCard);
                const toIdx = cards.indexOf(target);
                if (fromIdx < toIdx) {
                    existingContainer.insertBefore(dragSrcCard, target.nextSibling);
                } else {
                    existingContainer.insertBefore(dragSrcCard, target);
                }
                refreshExistingBadges();
            });
        }

        // ===== NEW PHOTO UPLOAD =====
        const hasExistingPhotos = {{ $produk->foto->isNotEmpty() ? 'true' : 'false' }};
        const fotoDt = new DataTransfer();
        const fotoDropzone = document.getElementById('fotoDropzone');
        const fotoInput = document.getElementById('fotoInput');
        const previewContainer = document.getElementById('previewFotoBaru');
        const wrapperPreview = document.getElementById('wrapperPreviewFotoBaru');
        const errorAlert = document.getElementById('fotoUploadError');

        window.addEventListener('dragover', function(e) { e.preventDefault(); }, false);
        window.addEventListener('drop', function(e) { e.preventDefault(); }, false);

        fotoDropzone.addEventListener('click', function(e) { fotoInput.click(); });

        ['dragenter', 'dragover'].forEach(eventName => {
            fotoDropzone.addEventListener(eventName, function(e) {
                e.preventDefault(); e.stopPropagation();
                fotoDropzone.classList.add('dragover');
            }, false);
        });
        ['dragleave', 'dragend'].forEach(eventName => {
            fotoDropzone.addEventListener(eventName, function(e) {
                e.preventDefault(); e.stopPropagation();
                fotoDropzone.classList.remove('dragover');
            }, false);
        });
        fotoDropzone.addEventListener('drop', function(e) {
            e.preventDefault(); e.stopPropagation();
            fotoDropzone.classList.remove('dragover');
            if (e.dataTransfer && e.dataTransfer.files) handleFiles(e.dataTransfer.files);
        }, false);
        fotoInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) handleFiles(this.files);
        });

        function handleFiles(files) {
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            const maxSize = 5 * 1024 * 1024;
            let errors = [];
            Array.from(files).forEach(file => {
                if (!validTypes.includes(file.type)) {
                    errors.push(`"${file.name}" bukan format yang didukung.`); return;
                }
                if (file.size > maxSize) {
                    errors.push(`"${file.name}" melebihi 5MB.`); return;
                }
                const exists = Array.from(fotoDt.files).some(ex => ex.name === file.name && ex.size === file.size);
                if (!exists) fotoDt.items.add(file);
            });
            if (errors.length > 0) {
                errorAlert.innerHTML = '<i class="feather icon-alert-triangle mr-1"></i> ' + errors.join('<br>');
                errorAlert.style.display = 'block';
            } else {
                errorAlert.style.display = 'none';
            }
            syncFileInputAndPreview();
        }

        function syncFileInputAndPreview() {
            fotoInput.files = fotoDt.files;
            previewContainer.innerHTML = '';
            const files = Array.from(fotoDt.files);
            if (files.length === 0) { wrapperPreview.style.display = 'none'; return; }
            wrapperPreview.style.display = 'block';
            files.forEach((file, index) => {
                const card = document.createElement('div');
                card.className = 'preview-foto-card';
                card.setAttribute('draggable', 'true');
                card.dataset.index = index;

                const remainingExisting = existingContainer
                    ? existingContainer.querySelectorAll('.existing-foto-card').length
                    : 0;
                const isUtama = !hasExistingPhotos && remainingExisting === 0 && index === 0;
                const badgeClass = isUtama ? 'badge-urutan badge-urutan-utama' : 'badge-urutan badge-urutan-info';
                const badgeText = isUtama ? '<i class="feather icon-star mr-1"></i>#1 Utama' : 'Baru #' + (index + 1);
                const sizeStr = file.size > 1024 * 1024
                    ? (file.size / (1024 * 1024)).toFixed(1) + ' MB'
                    : Math.round(file.size / 1024) + ' KB';

                card.innerHTML = `
                    <div class="foto-card-thumb">
                        <img class="img-preview" src="" alt="${file.name}">
                        <span class="${badgeClass}">${badgeText}</span>
                        <button type="button" class="btn-hapus-foto" onclick="hapusFotoBaru(${index})" title="Hapus">&times;</button>
                        <div class="foto-card-size">${sizeStr}</div>
                    </div>
                    <div class="foto-card-footer">
                        <button type="button" class="btn-shift" onclick="geserFotoBaru(${index}, -1)" ${index === 0 ? 'disabled' : ''}>
                            <i class="feather icon-chevron-left"></i>
                        </button>
                        <span class="drag-handle-icon" title="Tarik untuk atur urutan"><i class="feather icon-move"></i></span>
                        <button type="button" class="btn-shift" onclick="geserFotoBaru(${index}, 1)" ${index === files.length - 1 ? 'disabled' : ''}>
                            <i class="feather icon-chevron-right"></i>
                        </button>
                    </div>`;

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = card.querySelector('.img-preview');
                    if (img) img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                card.addEventListener('dragstart', function(evt) {
                    evt.stopPropagation();
                    card.classList.add('is-dragging');
                    evt.dataTransfer.effectAllowed = 'move';
                    evt.dataTransfer.setData('text/plain', String(index));
                });
                card.addEventListener('dragend', function(evt) {
                    evt.stopPropagation();
                    card.classList.remove('is-dragging');
                    previewContainer.querySelectorAll('.preview-foto-card').forEach(c => c.classList.remove('drag-over-card'));
                });
                card.addEventListener('dragover', function(evt) {
                    evt.preventDefault(); evt.stopPropagation();
                    card.classList.add('drag-over-card');
                });
                card.addEventListener('dragleave', function(evt) {
                    evt.stopPropagation(); card.classList.remove('drag-over-card');
                });
                card.addEventListener('drop', function(evt) {
                    evt.preventDefault(); evt.stopPropagation();
                    card.classList.remove('drag-over-card');
                    const fromIdx = parseInt(evt.dataTransfer.getData('text/plain'), 10);
                    if (!isNaN(fromIdx) && fromIdx !== index) reorderFotoBaru(fromIdx, index);
                });

                previewContainer.appendChild(card);
            });
            if (window.feather) feather.replace();
        }

        function reorderFotoBaru(fromIndex, toIndex) {
            const files = Array.from(fotoDt.files);
            if (fromIndex < 0 || fromIndex >= files.length || toIndex < 0 || toIndex >= files.length) return;
            const item = files.splice(fromIndex, 1)[0];
            files.splice(toIndex, 0, item);
            while (fotoDt.items.length > 0) fotoDt.items.remove(0);
            files.forEach(f => fotoDt.items.add(f));
            syncFileInputAndPreview();
        }

        function geserFotoBaru(index, direction) {
            reorderFotoBaru(index, index + direction);
        }

        function hapusFotoBaru(index) {
            const newDt = new DataTransfer();
            Array.from(fotoDt.files).forEach((f, i) => { if (i !== index) newDt.items.add(f); });
            while (fotoDt.items.length > 0) fotoDt.items.remove(0);
            Array.from(newDt.files).forEach(f => fotoDt.items.add(f));
            syncFileInputAndPreview();
        }

        // Initialize hidden inputs on load
        updateHiddenInputs();
    </script>
@endsection
