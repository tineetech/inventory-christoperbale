@extends('layouts.main')

@section('style')
    <style>
        .main-row {
            cursor: pointer;
        }

        .main-row .kode-click {
            color: #00499b;
            text-decoration: underline;
        }

        .detail-row {
            display: none;
            background: #f9f9f9;
        }

        .row-transit {
            background-color: #fff3e0 !important;
        }

        .row-retur {
            background-color: #fff5f5 !important;
        }

        #penjualanTable tr.row-retur td:first-child {
            border-left: 3px solid #dc3545;
        }
    </style>
@endsection

@section('content')
    @php
        $modeHarga = $filters['mode_harga'] ?? 'harga_reseller';
        $ekspedisiLabels = [
            'spx' => 'Shopee (SPX)',
            'jnt' => 'JNT (JX/JY/JZ)',
            'anteraja' => 'AnterAja (Angka)',
            'lazada' => 'Lazada (LX)',
            'lainnya' => 'Lainnya',
        ];
        $ekspedisiTerpilih = $filters['ekspedisi'] ?? null;
        $isSuperAdmin = Auth::guard('pengguna')->user()->role->nama_role === 'super_admin';
    @endphp
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Laporan Penjualan</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                    <li class="breadcrumb-item active">Penjualan</li>
                </ol>
            </div>

            @if (session('success'))
                <div class="card mb-4 border-success">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1 text-success"><i class="feather icon-check-circle"></i> Berhasil</h5>
                            <p class="mb-0 text-muted">{{ session('success') }}</p>
                        </div>
                        <div class="display-4 text-success"><i class="feather icon-check-circle"></i></div>
                    </div>
                </div>
            @endif

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

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('laporan.penjualan') }}" id="penjualanFilterForm">
                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Dari Tanggal</label>
                                <input type="date" class="form-control" name="dari_tanggal"
                                    value="{{ $filters['dari_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-1">
                                <label class="font-weight-bold">Jam</label>
                                <input type="time" class="form-control" name="dari_jam"
                                    value="{{ $filters['dari_jam'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Sampai Tanggal</label>
                                <input type="date" class="form-control" name="sampai_tanggal"
                                    value="{{ $filters['sampai_tanggal'] }}">
                            </div>
                            <div class="form-group col-md-1">
                                <label class="font-weight-bold">Jam</label>
                                <input type="time" class="form-control" name="sampai_jam"
                                    value="{{ $filters['sampai_jam'] ?? '' }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Dropshipper</label>
                                <select class="form-control" name="dropshipper_id">
                                    <option value="">(Semua)</option>
                                    @foreach ($dropshipperOptions as $option)
                                        <option value="{{ $option->id }}"
                                            {{ (string) $filters['dropshipper_id'] === (string) $option->id ? 'selected' : '' }}>
                                            {{ $option->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Cari</label>
                                <input type="text" class="form-control" name="search" placeholder="Kode / Resi / Pesanan / Keterangan" value="{{ $filters['search'] }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Show Penjualan Retur?</label>
                                <select class="form-control" name="show_retur">
                                    <option value="no" {{ ($filters['show_retur'] ?? 'yes') === 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ ($filters['show_retur'] ?? 'yes') === 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                <small class="text-muted">Pilih <strong>Yes</strong> untuk menampilkan penjualan normal + retur.</small>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="font-weight-bold">Hanya Penjualan Retur?</label>
                                <select class="form-control" name="only_retur">
                                    <option value="no" {{ ($filters['only_retur'] ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ ($filters['only_retur'] ?? 'no') === 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                <small class="text-muted">Pilih <strong>Yes</strong> untuk hanya menampilkan penjualan retur.</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Mode Harga Penjualan</label>
                                <select class="form-control" name="mode_harga">
                                    <option value="harga_reseller" {{ ($filters['mode_harga'] ?? 'harga_reseller') === 'harga_reseller' ? 'selected' : '' }}>Harga Reseller</option>
                                    <option value="harga_hpp" {{ ($filters['mode_harga'] ?? 'harga_reseller') === 'harga_hpp' ? 'selected' : '' }}>Harga HPP</option>
                                </select>
                                <small class="text-muted">Hanya untuk tampilan laporan, tidak mengubah data.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Ekspedisi (Prefix No Resi)</label>
                                <select class="form-control" name="ekspedisi">
                                    <option value="">(Semua)</option>
                                    @foreach ($ekspedisiLabels as $value => $label)
                                        <option value="{{ $value }}" {{ $ekspedisiTerpilih === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Dikelompokkan dari awal nomor resi.</small>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end" style="gap: 10px;">
                            @if ($isSuperAdmin)
                                <button type="button" class="btn btn-warning text-white" id="btnGenerateHargaKeuntungan">
                                    <i class="feather icon-refresh-cw"></i> Generate Harga Keuntungan
                                </button>
                            @endif
                            <a href="{{ route('laporan.penjualan.print', $filters) }}" target="_blank"
                                data-export-url="{{ route('laporan.penjualan.print') }}"
                                class="btn btn-success btn-export-penjualan">
                                <i class="feather icon-printer"></i> Print
                            </a>
                            <a href="{{ route('laporan.penjualan.pdf', $filters) }}"
                                data-export-url="{{ route('laporan.penjualan.pdf') }}"
                                class="btn btn-danger btn-export-penjualan">
                                <i class="feather icon-file-text"></i> PDF
                            </a>
                            <a href="{{ route('laporan.penjualan.excel', $filters) }}"
                                data-export-url="{{ route('laporan.penjualan.excel') }}"
                                class="btn btn-warning text-white btn-export-penjualan">
                                <i class="feather icon-download"></i> Excel
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="feather icon-refresh-cw"></i> Proses
                            </button>
                        </div>
                        <input type="hidden" name="generate_keuntungan" id="generateKeuntunganFlag" value="" disabled>
                        <input type="hidden" name="confirm_generate" id="confirmGenerateFlag" value="" disabled>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-file-text mr-2"></i> Data Laporan Penjualan
                    </h6>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal"
                            data-target="#modalAturKolom">
                            <i class="feather icon-sliders mr-1"></i> Atur Kolom
                        </button>
                        {{-- <span class="badge badge-info">{{ number_format($totalPasangTerjual, 0, ',', '.') }} pasang terjual</span> --}}
                        <span class="badge badge-info">{{ $penjualan->total() }} transaksi</span>
                        <span class="badge {{ $hargaCairKosong > 0 ? 'badge-warning' : 'badge-success' }}">
                            Harga Cair Kosong: {{ number_format($hargaCairKosong, 0, ',', '.') }} trx
                        </span>
                        {{-- @if (($filters['show_retur'] ?? 'yes') === 'yes')
                            <span class="badge badge-warning">Termasuk Retur</span>
                        @endif
                        @if (($filters['only_retur'] ?? 'no') === 'yes')
                            <span class="badge badge-danger">Hanya Retur</span>
                        @endif --}}
                        @if ($modeHarga === 'harga_hpp')
                            <span class="badge badge-info">Mode Harga HPP</span>
                        @else
                            <span class="badge badge-primary">Mode Harga Reseller</span>
                        @endif
                        @if ($ekspedisiTerpilih && isset($ekspedisiLabels[$ekspedisiTerpilih]))
                            <span class="badge badge-secondary">Ekspedisi: {{ $ekspedisiLabels[$ekspedisiTerpilih] }}</span>
                        @endif
                        <small class="text-muted">
                            Periode {{ \Carbon\Carbon::parse($filters['dari_tanggal'])->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse($filters['sampai_tanggal'])->format('d M Y') }}
                        </small>
                    </div>
                </div>

                <div class="table-responsive px-3 pb-3">
                    <table class="table table-modern table-hover mb-0" id="penjualanTable">
                        <thead>
                            <tr>
                                <th data-col="no">No</th>
                                <th data-col="kode_penjualan">Kode Penjualan</th>
                                <th data-col="nomor_resi">Nomor Resi</th>
                                <th data-col="nomor_pesanan">Nomor Pesanan</th>
                                <th data-col="dropshipper">Dropshipper</th>
                                <th data-col="tanggal">Tanggal</th>
                                @if ($isSuperAdmin)
                                <th data-col="total_harga">Total Harga</th>
                                <th data-col="keuntungan">Keuntungan</th>
                                @endif
                                <th data-col="harga_cair">Harga Cair</th>
                                <th data-col="retur">Retur?</th>
                                <th data-col="scan_out">Scan Out</th>
                                <th data-col="draft">Draft</th>
                                <th data-col="keterangan">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penjualan as $index => $item)
                                @php
                                    $scanOutClass = match ($item->scan_out) {
                                        'pending' => 'badge badge-warning',
                                        'done' => 'badge badge-success',
                                        'failed' => 'badge badge-danger',
                                        default => 'badge badge-secondary',
                                    };

                                    $scanOutLabel = $item->scan_out ? ucfirst($item->scan_out) : '-';
                                @endphp
                                <tr class="main-row {{ $item->keterangan === 'sedang transit' ? 'row-transit' : '' }} {{ $item->is_retur === 'yes' ? 'row-retur' : '' }}" data-report-main="true" data-id="{{ $item->id }}"
                                    data-expanded="false">
                                    <td data-col="no">
                                        {{ $penjualan->firstItem() + $index }}
                                        @if ($item->is_retur === 'yes')
                                            <span class="badge badge-danger ml-1" title="Penjualan Retur">Retur</span>
                                        @endif
                                    </td>
                                    <td data-col="kode_penjualan" class="kode-click" style="white-space: nowrap;">
                                        <strong>{{ $item->kode_penjualan }}</strong>
                                    </td>
                                    <td data-col="nomor_resi" style="white-space: nowrap;">{{ $item->nomor_resi ?: '-' }}</td>
                                    <td data-col="nomor_pesanan" style="white-space: nowrap;">{{ $item->nomor_pesanan ?: '-' }}</td>
                                    <td data-col="dropshipper">{{ $item->dropshipper->nama ?? '-' }}</td>
                                    <td data-col="tanggal" style="white-space: nowrap;">{{ $item->tanggal }}</td>
                                    @if ($isSuperAdmin)
                                    <td data-col="total_harga" style="white-space: nowrap;">Rp
                                        {{ number_format($item->totalHargaByMode($modeHarga), 0, ',', '.') }}</td>
                                    <td data-col="keuntungan" style="white-space: nowrap;">
                                        Rp {{ number_format($item->harga_keuntungan ?? 0, 0, ',', '.') }}
                                    </td>
                                    @endif
                                    <td data-col="harga_cair" style="white-space: nowrap;">
                                        {{ $item->harga_cair !== null ? 'Rp ' . number_format($item->harga_cair, 0, ',', '.') : '' }}
                                    </td>
                                    <td data-col="retur" style="white-space: nowrap;">
                                        @if ($item->is_retur === 'yes')
                                            <span class="badge badge-danger">Ya</span>
                                        @elseif ($item->is_retur === 'no')
                                            <span class="badge badge-success">Tidak</span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td data-col="scan_out"><span class="{{ $scanOutClass }}">{{ $scanOutLabel }}</span></td>
                                    <td data-col="draft">
                                        @if ($item->is_draft === 'yes')
                                            <span class="badge text-white" style="background:#00499b">Ya</span>
                                        @elseif ($item->is_draft === 'no')
                                            <span class="badge badge-danger">Tidak</span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td data-col="keterangan">
                                        {{-- {{ $item->keterangan ?? '-' }} --}}
                                        @php
                                            $role = Auth::guard('pengguna')->user()->role->nama_role;

                                            $canEditHargaCair = in_array($role, [
                                                'super_admin',
                                                'admin',
                                                'Admin Toko'
                                            ]);

                                            $showButton = $canEditHargaCair || is_null($item->harga_cair);
                                        @endphp

                                        <div class="d-flex align-items-center" style="gap:8px; white-space:nowrap;">
                                            <span>{{ $item->keterangan ?: '-' }}</span>
                                            @if ($showButton)
                                                <button class="btn btn-xs btn-primary btn-input-harga-cair" type="button"
                                                    data-id="{{ $item->id }}"
                                                    data-kode="{{ $item->kode_penjualan }}"
                                                    data-total="{{ $item->total_harga }}"
                                                    data-dropshipper="{{ $item->dropshipper->nama ?? '-' }}"
                                                    data-tanggal="{{ $item->tanggal }}"
                                                    onclick="openHargaCairModal(this)">
                                                    <i class="feather icon-edit"></i>
                                                    {{ $canEditHargaCair ? 'Edit' : 'Input' }} Harga Cair
                                                </button>
                                            @endif
                                            <div class="dropdown">
                                                <button class="btn btn-xs btn-outline-secondary dropdown-toggle" type="button"
                                                    data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true"
                                                    aria-expanded="false" title="Aksi">
                                                    <i class="feather icon-more-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if (hasPermission('buat', 'penjualan'))
                                                        <a class="dropdown-item" href="{{ route('penjualan.edit', $item->id) }}" target="_blank">
                                                            <i class="feather icon-edit mr-1"></i>
                                                            Edit Data
                                                        </a>
                                                    @endif
                                                    <button class="dropdown-item btn-transit cursor-pointer" type="button"
                                                        data-id="{{ $item->id }}"
                                                        data-transit="{{ $item->keterangan === 'sedang transit' ? 'true' : 'false' }}">
                                                        <i class="feather {{ $item->keterangan === 'sedang transit' ? 'icon-check-circle' : 'icon-navigation' }} mr-1"></i>
                                                        {{ $item->keterangan === 'sedang transit' ? 'Sudah Transit' : 'Sedang Transit' }}
                                                    </button>
                                                    @if ($canEditHargaCair)
                                                        <button class="dropdown-item btn-edit-harga-keuntungan cursor-pointer" type="button"
                                                            data-id="{{ $item->id }}"
                                                            data-kode="{{ $item->kode_penjualan }}"
                                                            data-tanggal="{{ $item->tanggal }}"
                                                            data-dropshipper="{{ $item->dropshipper->nama ?? '-' }}"
                                                            data-harga-keuntungan="{{ $item->harga_keuntungan ?? 0 }}"
                                                            onclick="openHargaKeuntunganModal(this)">
                                                            <i class="feather icon-trending-up mr-1"></i>
                                                            Edit Harga Keuntungan
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="detail-row" data-report-detail="true" id="detail-{{ $item->id }}"
                                    style="display:none; background:#f9f9f9;">
                                    <td colspan="{{ $isSuperAdmin ? 13 : 11 }}">
                                        <div class="p-3">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>No Resi</th>
                                                        <th>SKU</th>
                                                        <th>Nama Barang</th>
                                                        <th>Stok Sekarang</th>
                                                        <th>Qty Terjual</th>
                                                        <th>Harga</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $totalDetail = 0;
                                                    @endphp

                                                    @forelse ($item->detail as $detail)
                                                        @php
                                                            $hargaDetail = $detail->hargaByMode($modeHarga);
                                                            $subtotalDetail = $detail->qty * $hargaDetail;
                                                            $totalDetail += $subtotalDetail;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $detail->nomor_resi ?: '-' }}</td>
                                                            <td>{{ $detail->barang->sku ?? '-' }}</td>
                                                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                                            <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td>
                                                            <td>{{ $detail->qty }}</td>
                                                            <td>Rp {{ number_format($hargaDetail, 0, ',', '.') }}</td>
                                                            <td>Rp {{ number_format($subtotalDetail, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">
                                                                Belum ada detail barang pada transaksi ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                    @if ($isSuperAdmin && $item->detail->isNotEmpty())
                                                        <tr style="background:#f1f1f1;font-weight:bold">
                                                            <td colspan="6" class="text-right">Total Penjualan</td>
                                                            <td>Rp {{ number_format($totalDetail, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row="true">
                                    <td colspan="{{ $isSuperAdmin ? 13 : 11 }}" class="text-center text-muted py-4">Belum ada data penjualan pada
                                        filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center px-1 py-2 border-top"
                    style="gap:8px">
                    <div class="d-flex align-items-center">
                        <span class="mr-2 text-muted small">Show</span>
                        <select class="form-control form-control-sm" name="per_page"
                            form="penjualanFilterForm" style="width:72px"
                            onchange="document.getElementById('penjualanFilterForm').submit()">
                            <option value="10" {{ $filters['per_page'] == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $filters['per_page'] == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $filters['per_page'] == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ $filters['per_page'] == 200 ? 'selected' : '' }}>200</option>
                            <option value="500" {{ $filters['per_page'] == 500 ? 'selected' : '' }}>500</option>
                        </select>
                        <span class="ml-2 text-muted small">entries</span>
                    </div>
                    <div class="text-muted small">
                        @if ($penjualan->total() > 0)
                            Showing <strong>{{ $penjualan->firstItem() }}</strong>
                            to <strong>{{ $penjualan->lastItem() }}</strong>
                            of <strong>{{ $penjualan->total() }}</strong> entries
                        @else
                            No entries found
                        @endif
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            @php
                                $currentPage = $penjualan->currentPage();
                                $lastPage = $penjualan->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                                if ($start <= 3) $end = min($lastPage, 5);
                                if ($end >= $lastPage - 2) $start = max(1, $lastPage - 4);
                            @endphp

                            <li class="page-item {{ $penjualan->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->appends(['per_page' => request('per_page')])->url(1) }}">
                                    <i class="feather icon-chevrons-left"></i>
                                </a>
                            </li>
                            <li class="page-item {{ $penjualan->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->previousPageUrl() }}">
                                    <i class="feather icon-chevron-left"></i>
                                </a>
                            </li>

                            @if ($lastPage > 7 && $start > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $penjualan->url(1) }}">1</a>
                                </li>
                                @if ($start > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $penjualan->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if ($lastPage > 7 && $end < $lastPage)
                                @if ($end < $lastPage - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $penjualan->url($lastPage) }}">{{ $lastPage }}</a>
                                </li>
                            @endif

                            <li class="page-item {{ !$penjualan->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->nextPageUrl() }}">
                                    <i class="feather icon-chevron-right"></i>
                                </a>
                            </li>
                            <li class="page-item {{ !$penjualan->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $penjualan->appends(['per_page' => request('per_page')])->url($lastPage) }}">
                                    <i class="feather icon-chevrons-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Input Harga Cair --}}
    <div class="modal fade" id="modalHargaCair" tabindex="-1" role="dialog" aria-labelledby="modalHargaCairLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHargaCairLabel">
                        <i class="feather icon-dollar-sign mr-2 text-warning"></i> Input Harga Cair
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    {{-- Info transaksi --}}
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="row text-sm" style="font-size: 13px">
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Kode:</span>
                                    <strong id="info-kode">-</strong>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Tanggal:</span>
                                    <span id="info-tanggal">-</span>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Dropshipper:</span>
                                    <span id="info-dropshipper">-</span>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Total Harga:</span>
                                    <strong id="info-total" class="text-primary">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">
                            Harga Cair <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" id="inputHargaCair" class="form-control"
                                placeholder="Masukkan harga cair..." min="0" step="1">
                        </div>
                        <small class="text-muted">Masukkan nominal harga yang sudah cair.</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning text-white" id="btnSimpanHargaCair">
                        <i class="feather icon-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Edit Harga Keuntungan --}}
    <div class="modal fade" id="modalHargaKeuntungan" tabindex="-1" role="dialog"
        aria-labelledby="modalHargaKeuntunganLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHargaKeuntunganLabel">
                        <i class="feather icon-dollar-sign mr-2 text-warning"></i> Edit Harga Keuntungan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="row text-sm" style="font-size: 13px">
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Kode:</span>
                                    <strong id="info-keuntungan-kode">-</strong>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Tanggal:</span>
                                    <span id="info-keuntungan-tanggal">-</span>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Dropshipper:</span>
                                    <span id="info-keuntungan-dropshipper">-</span>
                                </div>
                                <div class="col-6 mb-1">
                                    <span class="text-muted">Nilai Saat Ini:</span>
                                    <strong id="info-keuntungan-current" class="text-primary">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">
                            Harga Keuntungan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" id="inputHargaKeuntungan" class="form-control"
                                placeholder="Masukkan harga keuntungan..." step="1">
                        </div>
                        <small class="text-muted">Nilai boleh negatif jika HPP lebih besar dari harga reseller.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning text-white" id="btnSimpanHargaKeuntungan">
                        <i class="feather icon-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Atur Kolom --}}
    <div class="modal fade" id="modalAturKolom" tabindex="-1" role="dialog" aria-labelledby="modalAturKolomLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAturKolomLabel">
                        <i class="feather icon-sliders mr-2"></i> Atur Kolom Tabel
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Pilih kolom yang ingin ditampilkan. Pengaturan disimpan di browser ini.
                    </p>
                    <div class="row" id="columnSettingsList"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="btnResetKolom">
                        <i class="feather icon-refresh-cw mr-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // ── Export mengikuti filter yang sedang dipilih ───────
        document.querySelectorAll('.btn-export-penjualan').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const form = document.getElementById('penjualanFilterForm');
                const params = new URLSearchParams(new FormData(form));

                for (const [key, value] of Array.from(params.entries())) {
                    if (value === '') params.delete(key);
                }

                const url = link.dataset.exportUrl + '?' + params.toString();

                if (link.target === '_blank') {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            });
        });

        // ── Expand detail row ─────────────────────────────────
        document.getElementById('penjualanTable').addEventListener('click', function(e) {
            const mainRow = e.target.closest('tr[data-report-main]');
            if (!mainRow) return;
            if (e.target.closest('a, button, input, select, textarea, label')) return;

            const rowId = mainRow.getAttribute('data-id');
            const detailRow = document.getElementById('detail-' + rowId);
            if (!detailRow) return;

            const expanded = mainRow.dataset.expanded === 'true';
            mainRow.dataset.expanded = expanded ? 'false' : 'true';
            detailRow.style.display = expanded ? 'none' : 'table-row';
        });

        // ── Transit Toggle ────────────────────────────────────
        $(document).on('click', '.btn-transit', async function() {
            const btn = $(this);
            const id = btn.data('id');
            const transit = btn.data('transit') === 'true';

            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm"></span>');

            try {
                const res = await fetch(`/transaksi/penjualan/${id}/transit`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const json = await res.json();
                if (json.success) {
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: json.message ?? 'Terjadi kesalahan.' });
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
            } finally {
                btn.prop('disabled', false);
            }
        });

        // ── Harga Cair Modal ──────────────────────────────────
        let activePenjualanId = null;

        function openHargaCairModal(btn) {
            activePenjualanId = btn.dataset.id;

            document.getElementById('info-kode').textContent = btn.dataset.kode;
            document.getElementById('info-tanggal').textContent = btn.dataset.tanggal;
            document.getElementById('info-dropshipper').textContent = btn.dataset.dropshipper;
            document.getElementById('info-total').textContent = 'Rp ' + Number(btn.dataset.total).toLocaleString('id-ID');
            document.getElementById('inputHargaCair').value = '';

            $('#modalHargaCair').modal('show');
        }

        document.getElementById('btnSimpanHargaCair').addEventListener('click', async function() {
            const hargaCair = document.getElementById('inputHargaCair').value;

            if (!hargaCair || isNaN(hargaCair)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Masukkan harga cair yang valid.'
                });
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...';

            try {
                const res = await fetch(`/transaksi/penjualan/${activePenjualanId}/harga-cair`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        harga_cair: hargaCair
                    })
                });

                const json = await res.json();

                if (json.success) {
                    $('#modalHargaCair').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: json.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: json.message ?? 'Terjadi kesalahan.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="feather icon-save mr-1"></i> Simpan';
            }
        });

        const btnGenerateHargaKeuntungan = document.getElementById('btnGenerateHargaKeuntungan');
        if (btnGenerateHargaKeuntungan) {
            btnGenerateHargaKeuntungan.addEventListener('click', async function() {
            const btn = this;
            const form = document.getElementById('penjualanFilterForm');
            const originalHtml = btn.innerHTML;
            const params = new URLSearchParams(new FormData(form));
            params.set('source', 'laporan');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Mengecek...';

            try {
                const res = await fetch(`{{ route('penjualan.harga-keuntungan.manual-count') }}?${params.toString()}`, {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) throw new Error('Gagal mengecek data edit manual.');

                const json = await res.json();
                const count = Number(json.count || 0);

                if (count > 0) {
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Konfirmasi Generate Harga Keuntungan',
                        html: `Ada <strong>${count.toLocaleString('id-ID')}</strong> transaksi yang sudah diedit manual.<br><br>Jika dilanjutkan, hasil edit manual akan diganti dengan perhitungan baru <strong>(harga reseller - HPP)</strong> dan tidak bisa dikembalikan.`,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true
                    });

                    if (!result.isConfirmed) return;
                }

                const generateFlag = document.getElementById('generateKeuntunganFlag');
                const confirmFlag = document.getElementById('confirmGenerateFlag');
                generateFlag.disabled = false;
                generateFlag.value = '1';
                if (count > 0) {
                    confirmFlag.disabled = false;
                    confirmFlag.value = '1';
                }
                form.submit();
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: e.message || 'Gagal mengecek data edit manual.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
            });
        }

        let activeHargaKeuntunganId = null;

        function openHargaKeuntunganModal(btn) {
            activeHargaKeuntunganId = btn.dataset.id;

            const current = Number(btn.dataset.hargaKeuntungan || 0);
            document.getElementById('info-keuntungan-kode').textContent = btn.dataset.kode;
            document.getElementById('info-keuntungan-tanggal').textContent = btn.dataset.tanggal;
            document.getElementById('info-keuntungan-dropshipper').textContent = btn.dataset.dropshipper;
            document.getElementById('info-keuntungan-current').textContent = 'Rp ' + current.toLocaleString('id-ID');
            document.getElementById('inputHargaKeuntungan').value = current;

            $('#modalHargaKeuntungan').modal('show');
        }

        document.getElementById('btnSimpanHargaKeuntungan').addEventListener('click', async function() {
            const value = document.getElementById('inputHargaKeuntungan').value;

            if (value === '' || isNaN(value)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Masukkan harga keuntungan yang valid.'
                });
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...';

            try {
                const res = await fetch(`/transaksi/penjualan/${activeHargaKeuntunganId}/harga-keuntungan`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        harga_keuntungan: value
                    })
                });

                const json = await res.json();

                if (json.success) {
                    $('#modalHargaKeuntungan').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: json.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: json.message ?? 'Terjadi kesalahan.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="feather icon-save mr-1"></i> Simpan';
            }
        });

        // ── Atur kolom tabel ─────────────────────────────────
        const COLUMN_STORAGE_KEY = 'laporan-penjualan-visible-columns-v1';
        const MANDATORY_COLUMNS = ['no', 'kode_penjualan', 'keterangan'];
        const penjualanTable = document.getElementById('penjualanTable');
        const columnHeaders = Array.from(penjualanTable.querySelectorAll('thead > tr > th[data-col]'));
        const columnKeys = columnHeaders.map(function(header) {
            return header.dataset.col;
        });
        const columnLabels = columnHeaders.reduce(function(labels, header) {
            labels[header.dataset.col] = header.textContent.trim();
            return labels;
        }, {});

        function getVisibleColumns() {
            try {
                const stored = JSON.parse(localStorage.getItem(COLUMN_STORAGE_KEY) || '[]');
                if (!Array.isArray(stored)) return columnKeys;

                const validStored = stored.filter(function(key) {
                    return columnKeys.includes(key);
                });

                if (validStored.length === 0) return columnKeys;

                return columnKeys.filter(function(key) {
                    return validStored.includes(key) || MANDATORY_COLUMNS.includes(key);
                });
            } catch (e) {
                return columnKeys;
            }
        }

        function saveVisibleColumns(visibleKeys) {
            try {
                localStorage.setItem(COLUMN_STORAGE_KEY, JSON.stringify(visibleKeys));
            } catch (e) {
                // Abaikan jika localStorage tidak tersedia.
            }
        }

        function syncDetailColspans(visibleCount) {
            penjualanTable.querySelectorAll(
                'tr[data-report-detail] > td:first-child, tr[data-empty-row] > td:first-child'
            ).forEach(function(cell) {
                cell.colSpan = Math.max(visibleCount, 1);
            });
        }

        function updateColumnCheckboxes(visibleSet) {
            document.querySelectorAll('.column-toggle').forEach(function(checkbox) {
                checkbox.checked = visibleSet.has(checkbox.dataset.col);
            });
        }

        function applyColumnVisibility(visibleKeys) {
            const visibleSet = new Set(visibleKeys);

            columnKeys.forEach(function(key) {
                const isVisible = visibleSet.has(key);
                penjualanTable.querySelectorAll(
                    'thead > tr > th[data-col="' + key + '"], tbody > tr[data-report-main] > td[data-col="' + key + '"]'
                ).forEach(function(cell) {
                    cell.classList.toggle('d-none', !isVisible);
                });
            });

            syncDetailColspans(visibleKeys.length);
            updateColumnCheckboxes(visibleSet);
        }

        function renderColumnSettings() {
            const container = document.getElementById('columnSettingsList');
            const visibleSet = new Set(getVisibleColumns());
            container.innerHTML = '';

            columnKeys.forEach(function(key) {
                const isMandatory = MANDATORY_COLUMNS.includes(key);
                const inputId = 'column-toggle-' + key;
                const wrapper = document.createElement('div');
                wrapper.className = 'col-12 col-sm-6 mb-2';
                wrapper.innerHTML = `
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input column-toggle" id="${inputId}"
                            data-col="${key}" ${visibleSet.has(key) ? 'checked' : ''} ${isMandatory ? 'disabled' : ''}>
                        <label class="custom-control-label" for="${inputId}">
                            ${columnLabels[key] || key}${isMandatory ? ' <span class="text-muted">(wajib)</span>' : ''}
                        </label>
                    </div>
                `;
                container.appendChild(wrapper);
            });
        }

        document.getElementById('columnSettingsList').addEventListener('change', function(event) {
            const checkbox = event.target.closest('.column-toggle');
            if (!checkbox || checkbox.disabled) return;

            const visibleSet = new Set(getVisibleColumns());

            if (checkbox.checked) {
                visibleSet.add(checkbox.dataset.col);
            } else {
                visibleSet.delete(checkbox.dataset.col);
            }

            MANDATORY_COLUMNS.forEach(function(key) {
                if (columnKeys.includes(key)) visibleSet.add(key);
            });

            const visibleKeys = columnKeys.filter(function(key) {
                return visibleSet.has(key);
            });

            saveVisibleColumns(visibleKeys);
            applyColumnVisibility(visibleKeys);
        });

        document.getElementById('btnResetKolom').addEventListener('click', function() {
            try {
                localStorage.removeItem(COLUMN_STORAGE_KEY);
            } catch (e) {
                // Abaikan jika localStorage tidak tersedia.
            }

            applyColumnVisibility(columnKeys);
        });

        renderColumnSettings();
        applyColumnVisibility(getVisibleColumns());
    </script>
@endsection
