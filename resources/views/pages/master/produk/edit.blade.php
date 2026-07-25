@extends('layouts.main')

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
                                    placeholder="Otomatis dari barang terpilih" readonly
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
                            <label class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                            <select class="form-control" id="selectBarang" style="width:100%"></select>
                            <small class="text-muted">Cari barang, lalu pilih. Sistem akan otomatis memilih barang dengan kata pertama atau kedua yang sama.</small>
                            @error('barang_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <div id="selectedBarangList"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Foto Produk</label>
                            <input type="file" class="form-control-file" name="foto[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewFoto(this)">
                            <small class="text-muted">Bisa pilih lebih dari 1 foto. Maks 5MB per foto.</small>
                            <div id="previewFotoBaru" class="d-flex flex-wrap mt-2" style="gap:8px;"></div>

                            @if ($produk->foto->isNotEmpty())
                                <div class="mt-3">
                                    <label class="form-label">Foto Saat Ini</label>
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        @foreach ($produk->foto as $f)
                                            <div style="position:relative;width:80px;height:80px;border-radius:6px;overflow:hidden;border:1px solid #dee2e6;flex-shrink:0;">
                                                <img src="{{ asset('storage/' . $f->foto) }}" style="width:100%;height:100%;object-fit:cover;">
                                                @if ($f->is_utama)
                                                    <span style="position:absolute;top:2px;left:2px;background:rgba(40,167,69,0.85);color:#fff;border-radius:3px;padding:0 4px;font-size:9px;">Utama</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
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
            ajax: {
                url: '/api/product/search',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return {
                        results: data.filter(function(item) {
                            return !selectedBarang.some(function(b) { return b.id === item.id; });
                        }).map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama_barang + ' (' + (item.stok ? item.stok.jumlah_stok : 0) + ')',
                                harga_reseller: item.harga_2 || 0
                            };
                        })
                    };
                }
            }
        }).on('select2:select', function(e) {
            const data = e.params.data;

            fetch('/api/product/search-by-word?nama=' + encodeURIComponent(data.text.split(' (')[0]))
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

        function previewFoto(input) {
            const container = document.getElementById('previewFotoBaru');
            container.innerHTML = '';
            Array.from(input.files).forEach(function(file, i) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = 'position:relative;width:80px;height:80px;border-radius:6px;overflow:hidden;border:1px solid #dee2e6;flex-shrink:0;';
                    wrapper.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">' +
                        '<span style="position:absolute;top:2px;right:2px;background:rgba(220,53,69,0.85);color:#fff;border-radius:50%;width:18px;height:18px;font-size:11px;display:flex;align-items:center;justify-content:center;cursor:pointer;" onclick="hapusPreviewFoto(this,' + i + ')">&times;</span>' +
                        '<span style="position:absolute;bottom:2px;right:2px;background:rgba(0,0,0,0.5);color:#fff;border-radius:3px;padding:0 4px;font-size:9px;">' + (i + 1) + '</span>';
                    container.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        function hapusPreviewFoto(el, index) {
            const input = document.querySelector('input[name="foto[]"]');
            const dt = new DataTransfer();
            Array.from(input.files).forEach(function(f, i) {
                if (i !== index) dt.items.add(f);
            });
            input.files = dt.files;
            previewFoto(input);
        }
    </script>
@endsection
