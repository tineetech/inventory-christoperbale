@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Kelola Banner</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('banner.index') }}">Kelola Banner</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Edit Banner</h6>
                <div class="card-body">
                    <form action="{{ route('banner.update', $banner->id) }}" method="POST" id="bannerForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                    placeholder="Contoh: Promo Akhir Tahun 2026" value="{{ old('title', $banner->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', $banner->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active', $banner->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-8">
                                <label class="form-label">Gambar / Banner</label>
                                <div class="custom-file">
                                    <input type="file" name="gambar" id="gambarInput"
                                        class="custom-file-input @error('gambar') is-invalid @enderror"
                                        accept="image/*">
                                    <label class="custom-file-label" for="gambarInput">Pilih gambar baru untuk diganti... (opsional)</label>
                                </div>
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                                @error('gambar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror"
                                    placeholder="0" value="{{ old('urutan', $banner->urutan) }}" min="0">
                                <small class="form-text text-muted">Urutan keberapa banner ini ditampilkan di website</small>
                                @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label d-block">Gambar Saat Ini</label>
                                <div style="width:100%;max-width:320px;height:160px;border:2px dashed #ced4da;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;">
                                    @if ($banner->gambar)
                                        <img src="{{ asset('storage/' . $banner->gambar) }}" alt="{{ $banner->title }}"
                                            style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span class="text-muted small">Tidak ada gambar</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="4"
                                    placeholder="Deskripsi/keterangan banner (opsional)...">{{ old('catatan', $banner->catatan) }}</textarea>
                                @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('banner.index') }}" class="btn btn-secondary"><i class="feather icon-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('gambarInput').addEventListener('change', function (e) {
            var file = e.target.files[0];
            var label = document.querySelector('.custom-file-label');
            if (label) label.textContent = file ? file.name : 'Pilih gambar baru untuk diganti... (opsional)';
        });
    </script>
@endsection