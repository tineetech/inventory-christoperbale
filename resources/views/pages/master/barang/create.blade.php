@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <!-- [ content ] Start -->
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Barang</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Master</a></li>
                    <li class="breadcrumb-item active">Barang</li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
            <div class="row">

                <div class="card col-lg-12 mb-4">
                    <h6 class="card-header">Formulir Pembuatan Data Barang</h6>
                    <div class="card-body">
                        <form action="{{ route('barang.store') }}" method="POST">
                            @csrf
                            <div class="form-row">

                                <div class="form-group col-md-6">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control"
                                        placeholder="Masukkan kode SKU" required>
                                    @error('sku')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Nama Barang</label>
                                    <input type="text" name="nama_barang" class="form-control"
                                        placeholder="Masukkan nama barang" required>
                                    @error('nama_barang')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Satuan</label>

                                    <select name="satuan_id" class="form-control" required>

                                        <option value="">-- Pilih Satuan --</option>

                                        @foreach($satuan as $s)
                                            <option value="{{ $s->id }}">
                                                {{ $s->nama_satuan }}
                                            </option>
                                        @endforeach

                                    </select>

                                    @error('satuan_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Harga 1</label>
                                    <input type="number" name="harga_1" class="form-control"
                                        placeholder="Masukkan harga pertama" required>
                                    @error('harga_1')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-12">
                                    <label class="form-label">Harga 2</label>
                                    <input type="number" name="harga_2" class="form-control"
                                        placeholder="Masukkan harga kedua">
                                    @error('harga_2')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Stok Minimum</label>
                                    <input type="number" value="1" name="stok_minimum" class="form-control"
                                        placeholder="Masukkan stok minimum">
                                    @error('stok_minimum')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-label">Stok Awal</label>
                                    <input type="number" name="stok_awal" class="form-control"
                                        placeholder="Masukkan stok awal barang" required>
                                    @error('stok_awal')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-12">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2"
                                        placeholder="Catatan tambahan (opsional)"></textarea>
                                </div>

                            </div>

                            <div class="d-flex justify-content-between">

                                <a href="{{ route('barang.index') }}" class="btn btn-secondary">
                                    <i class="feather icon-arrow-left"></i> Kembali
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Simpan Barang
                                </button>

                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
        <!-- [ content ] End -->
        @include('components.footer')
    </div>
@endsection