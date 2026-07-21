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
    </style>
@endsection

@section('content')
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

                        <div class="d-flex flex-wrap justify-content-end" style="gap: 10px;">
                            <a href="{{ route('laporan.penjualan.print', $filters) }}" target="_blank"
                                class="btn btn-success">
                                <i class="feather icon-printer"></i> Print
                            </a>
                            <a href="{{ route('laporan.penjualan.pdf', $filters) }}" class="btn btn-danger">
                                <i class="feather icon-file-text"></i> PDF
                            </a>
                            <a href="{{ route('laporan.penjualan.excel', $filters) }}" class="btn btn-warning text-white">
                                <i class="feather icon-download"></i> Excel
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="feather icon-refresh-cw"></i> Proses
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div style="border: none !important" class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-header-title mb-0">
                        <i class="feather icon-file-text mr-2"></i> Data Laporan Penjualan
                    </h6>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <span class="badge badge-light">{{ $penjualan->total() }} transaksi</span>
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
                                <th>No</th>
                                <th>Kode Penjualan</th>
                                <th>Nomor Resi</th>
                                <th>No. Pesanan</th>
                                {{-- <th>No. Transaksi</th> --}}
                                <th>Dropshipper</th>
                                <th>Tanggal</th>
                                <th>Total Harga</th>
                                <th>Harga Cair</th>
                                <th>Scan Out</th>
                                <th>Draft</th>
                                <th>Keterangan</th>
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
                                <tr class="main-row" data-report-main="true" data-id="{{ $item->id }}"
                                    data-expanded="false">
                                    <td>{{ $penjualan->firstItem() + $index }}</td>
                                    <td class="kode-click" style="white-space: nowrap;">
                                        <strong>{{ $item->kode_penjualan }}</strong></td>
                                    <td style="white-space: nowrap;">{{ $item->nomor_resi ?: '-' }}</td>
                                    <td>{{ $item->nomor_pesanan ?: '-' }}</td>
                                    {{-- <td>{{ $item->nomor_transaksi ?: '-' }}</td> --}}
                                    <td>{{ $item->dropshipper->nama ?? '-' }}</td>
                                    <td style="white-space: nowrap;">{{ $item->tanggal }}</td>
                                    <td style="white-space: nowrap;">Rp
                                        {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    <td style="white-space: nowrap;">Rp {{ number_format($item->harga_cair, 0, ',', '.') }}
                                    </td>
                                    <td><span class="{{ $scanOutClass }}">{{ $scanOutLabel }}</span></td>
                                    <td>
                                        @if ($item->is_draft === 'yes')
                                            <span class="badge text-white" style="background:#00499b">Ya</span>
                                        @elseif ($item->is_draft === 'no')
                                            <span class="badge badge-danger">Tidak</span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->keterangan ?: '-' }}
                                        @php
                                            $role = Auth::guard('pengguna')->user()->role->nama_role;

                                            $canEditHargaCair = in_array($role, [
                                                'super_admin',
                                                'admin',
                                                'Admin Toko'
                                            ]);

                                            $showButton = $canEditHargaCair || is_null($item->harga_cair);
                                        @endphp

                                        @if ($showButton)
                                            <button class="btn btn-xs btn-primary mx-1 btn-input-harga-cair"
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
                                    </td>
                                </tr>
                                <tr class="detail-row" data-report-detail="true" id="detail-{{ $item->id }}"
                                    style="display:none; background:#f9f9f9;">
                                    <td colspan="11">
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
                                                            $totalDetail += $detail->subtotal;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $detail->nomor_resi ?: '-' }}</td>
                                                            <td>{{ $detail->barang->sku ?? '-' }}</td>
                                                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                                            <td>{{ $detail->barang->stok->jumlah_stok ?? 0 }}</td>
                                                            <td>{{ $detail->qty }}</td>
                                                            <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">
                                                                Belum ada detail barang pada transaksi ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                    @if ($item->detail->isNotEmpty())
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
                                    <td colspan="11" class="text-center text-muted py-4">Belum ada data penjualan pada
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
@endsection

@section('scripts')
    <script>
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

            if (!hargaCair || isNaN(hargaCair) || Number(hargaCair) < 0) {
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
    </script>
@endsection
