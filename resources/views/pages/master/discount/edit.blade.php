@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Discount</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('discount.index') }}">Discount</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Edit Discount: {{ $discount->name }}</h6>
                <div class="card-body">
                    <form action="{{ route('discount.update', $discount->id) }}" method="POST" id="formDiscount">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Nama Discount <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Nama discount" value="{{ old('name', $discount->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="percentage" {{ (old('type', $discount->type) === 'percentage') ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ (old('type', $discount->type) === 'fixed') ? 'selected' : '' }}>Fixed (Rp)</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="form-label">Nilai <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="value" class="form-control @error('value') is-invalid @enderror"
                                    placeholder="0" value="{{ old('value', $discount->value) }}" required>
                                @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Mulai</label>
                                <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror"
                                    value="{{ old('start_at', $discount->start_at ? \Carbon\Carbon::parse($discount->start_at)->format('Y-m-d\TH:i') : '') }}">
                                @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">Selesai</label>
                                <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror"
                                    value="{{ old('end_at', $discount->end_at ? \Carbon\Carbon::parse($discount->end_at)->format('Y-m-d\TH:i') : '') }}">
                                @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pilih Produk</label>
                            <select class="form-control" id="selectProduk" style="width:100%"></select>
                            <small class="text-muted">Cari produk, lalu pilih untuk menambahkan ke daftar.</small>
                        </div>

                        <div class="form-group" id="produkListContainer">
                            <label class="form-label">Produk Terpilih</label>
                            <div id="selectedProdukList"></div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('discount.index') }}" class="btn btn-secondary">
                                <i class="feather icon-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather icon-save"></i> Update Discount
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let selectedProduk = [];

        @foreach ($discount->discountProducts as $dp)
            selectedProduk.push({
                id: {{ $dp->product->id }},
                text: '{{ $dp->product->nama_produk }}',
                status: '{{ $dp->status }}'
            });
        @endforeach
        renderSelectedProduk();

        function renderSelectedProduk() {
            const container = document.getElementById('selectedProdukList');
            container.innerHTML = '';
            let ids = [];
            selectedProduk.forEach((p, i) => {
                ids.push(p.id);
                const el = document.createElement('div');
                el.className = 'd-flex align-items-center justify-content-between px-3 py-2 mb-1';
                el.style.cssText = 'background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;';
                el.innerHTML = '<span>' + p.text + '</span>' +
                    '<div class="d-flex align-items-center gap-2">' +
                        '<select class="form-control form-control-sm" style="width:auto;" onchange="updateStatus(' + i + ', this.value)">' +
                            '<option value="active" ' + (p.status === 'active' ? 'selected' : '') + '>Active</option>' +
                            '<option value="nonactive" ' + (p.status === 'nonactive' ? 'selected' : '') + '>Nonactive</option>' +
                        '</select>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProduk(' + i + ')">' +
                            '<i class="feather icon-x"></i>' +
                        '</button>' +
                    '</div>';
                container.appendChild(el);
            });

            document.querySelectorAll('.produk-id-input').forEach(el => el.remove());
            ids.forEach(id => {
                const idx = ids.indexOf(id);
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'products[' + idx + '][id]';
                inp.value = id;
                inp.className = 'produk-id-input';
                container.appendChild(inp);

                const sts = document.createElement('input');
                sts.type = 'hidden';
                sts.name = 'products[' + idx + '][status]';
                sts.value = selectedProduk.find(p => p.id === id).status || 'active';
                sts.className = 'produk-status-input';
                container.appendChild(sts);
            });
        }

        function updateStatus(index, value) {
            selectedProduk[index].status = value;
            renderSelectedProduk();
        }

        function removeProduk(index) {
            selectedProduk.splice(index, 1);
            renderSelectedProduk();
        }

        $('#selectProduk').select2({
            placeholder: 'Cari produk...',
            allowClear: true,
            ajax: {
                url: '/api/discount/search-products',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return {
                        results: data.filter(function(item) {
                            return !selectedProduk.some(function(p) { return p.id === item.id; });
                        }).map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama_produk + ' (' + item.slug + ')'
                            };
                        })
                    };
                }
            }
        }).on('select2:select', function(e) {
            const data = e.params.data;
            selectedProduk.push({
                id: data.id,
                text: data.text,
                status: 'active'
            });
            renderSelectedProduk();
            $('#selectProduk').val(null).trigger('change');
        });
    </script>
@endsection
