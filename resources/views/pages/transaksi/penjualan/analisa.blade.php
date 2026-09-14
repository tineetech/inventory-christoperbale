@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Analisa Stok Penjualan</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Transaksi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('penjualan.index') }}">Penjualan</a></li>
                    <li class="breadcrumb-item active">Analisa Stok</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-lg-12">

                    {{-- ===== CARD FILTER TANGGAL ===== --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center" style="border:none !important">
                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-calendar mr-2"></i> Filter Tanggal Analisa
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('penjualan.analisa') }}">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-3 mb-0">
                                        <label class="font-weight-bold">Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control"
                                            value="{{ $dateStr }}" max="{{ today()->format('Y-m-d') }}">
                                    </div>
                                    <div class="form-group col-md-3 mb-0">
                                        <button type="submit" class="btn btn-info btn-block mt-4">
                                            <i class="feather icon-bar-chart-2"></i> Analisa
                                        </button>
                                    </div>
                                    @if($dateStr !== today()->format('Y-m-d'))
                                        <div class="form-group col-md-2 mb-0">
                                            <a href="{{ route('penjualan.analisa') }}" class="btn btn-outline-secondary btn-block mt-4">
                                                <i class="feather icon-rotate-ccw"></i> Hari Ini
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ===== CARD ANALISA ===== --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="border:none !important">
                            <h6 class="card-header-title mb-0">
                                <i class="feather icon-activity mr-2"></i>
                                Analisa Stok Tanggal: <strong>{{ $date->format('d F Y') }}</strong>
                                @if($dateStr === today()->format('Y-m-d'))
                                    <span class="badge badge-success ml-2">Hari Ini</span>
                                @endif
                            </h6>
                            <a href="{{ route('penjualan.index') }}" class="btn btn-sm btn-outline-secondary mt-1 mt-md-0">
                                <i class="feather icon-arrow-left"></i> Kembali
                            </a>
                        </div>

                        <div class="card-body">

                            {{-- === RINGKASAN GLOBAL === --}}
                            <div class="row mb-4">
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #17a2b8 !important">
                                        <div class="card-body py-3 px-3">
                                            <div class="text-muted small mb-1">Total Transaksi Penjualan</div>
                                            <h4 class="mb-0 font-weight-bold text-info">{{ $totalTransaksi }}</h4>
                                            <div class="small text-muted mt-1">
                                                <span class="badge badge-success">{{ $totalDone }} done</span>
                                                <span class="badge badge-warning ml-1">{{ $totalPending }} pending</span>
                                                @if($totalNoScan > 0)
                                                    <span class="badge badge-secondary ml-1">{{ $totalNoScan }} lainnya</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #28a745 !important">
                                        <div class="card-body py-3 px-3">
                                            <div class="text-muted small mb-1">Total Nilai Penjualan</div>
                                            <h5 class="mb-0 font-weight-bold text-success">
                                                Rp {{ number_format($totalNilai, 0, ',', '.') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #ffc107 !important">
                                        <div class="card-body py-3 px-3">
                                            <div class="text-muted small mb-1">Total Qty Terjual</div>
                                            <h4 class="mb-0 font-weight-bold" style="color:#e0a800">{{ $totalQtyKeluar }}</h4>
                                            <div class="small text-muted mt-1">
                                                Terkonfirmasi scan: <strong>{{ $totalQtyTerkonfirmasi }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="card h-100 shadow-sm" style="border-left:4px solid #00499b !important">
                                        <div class="card-body py-3 px-3">
                                            <div class="text-muted small mb-1">Movement Gudang (Fisik)</div>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <div class="text-success small"><i class="feather icon-arrow-down"></i> Masuk</div>
                                                    <strong class="text-success">+{{ $totalMvMasuk }}</strong>
                                                </div>
                                                <div>
                                                    <div class="text-danger small"><i class="feather icon-arrow-up"></i> Keluar</div>
                                                    <strong class="text-danger">-{{ $totalMvKeluar }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- === PREDIKSI & VALIDASI GLOBAL === --}}
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 bg-light border-0">
                                        <div class="card-body py-3 px-4">
                                            <h6 class="font-weight-bold mb-3">
                                                <i class="feather icon-trending-up mr-2 text-info"></i>Prediksi & Rata-rata
                                            </h6>
                                            <table class="table table-sm mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted border-0">Total transaksi penjualan</td>
                                                        <td class="border-0 font-weight-bold">{{ $totalTransaksi }} transaksi</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted border-0">Total qty barang terjual</td>
                                                        <td class="border-0 font-weight-bold">{{ $totalQtyKeluar }} pcs</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted border-0">Rata-rata qty per transaksi</td>
                                                        <td class="border-0 font-weight-bold">{{ $prediksiRataQty }} pcs</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted border-0">Qty terkonfirmasi (scan done)</td>
                                                        <td class="border-0 font-weight-bold">{{ $totalQtyTerkonfirmasi }} pcs</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted border-0">Qty belum dikonfirmasi</td>
                                                        <td class="border-0">
                                                            @php $belumKonfirmasi = $totalQtyKeluar - $totalQtyTerkonfirmasi; @endphp
                                                            @if($belumKonfirmasi > 0)
                                                                <span class="badge badge-warning">{{ $belumKonfirmasi }} pcs pending/belum scan</span>
                                                            @else
                                                                <span class="badge badge-success">Semua terkonfirmasi</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    @php $globalSelisih = $totalQtyTerkonfirmasi - $totalMvKeluar; @endphp
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-body py-3 px-4">
                                            <h6 class="font-weight-bold mb-3">
                                                <i class="feather icon-shield mr-2 text-warning"></i>Validasi Gudang (Global)
                                            </h6>
                                            <table class="table table-sm mb-2">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted border-0">Qty terkonfirmasi penjualan</td>
                                                        <td class="border-0 font-weight-bold">{{ $totalQtyTerkonfirmasi }} pcs</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted border-0">Total keluar gudang (movement)</td>
                                                        <td class="border-0 font-weight-bold">{{ $totalMvKeluar }} pcs</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted border-0">Selisih</td>
                                                        <td class="border-0 font-weight-bold {{ $globalSelisih === 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $globalSelisih > 0 ? '+' : '' }}{{ $globalSelisih }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            @if($totalQtyTerkonfirmasi === 0 && $totalMvKeluar === 0)
                                                <div class="alert alert-secondary py-2 mb-0 small">
                                                    <i class="feather icon-info mr-1"></i>
                                                    Belum ada penjualan yang dikonfirmasi (scan done) hari ini.
                                                </div>
                                            @elseif($globalSelisih === 0)
                                                <div class="alert alert-success py-2 mb-0 small">
                                                    <i class="feather icon-check-circle mr-1"></i>
                                                    <strong>VALID</strong> — Qty keluar penjualan sesuai dengan movement gudang.
                                                </div>
                                            @elseif($globalSelisih > 0)
                                                <div class="alert alert-warning py-2 mb-0 small">
                                                    <i class="feather icon-alert-triangle mr-1"></i>
                                                    <strong>KURANG KELUAR</strong> — Ada {{ $globalSelisih }} pcs tercatat di penjualan (scan done) tapi movement gudang belum mencatat keluarnya.
                                                </div>
                                            @else
                                                <div class="alert alert-danger py-2 mb-0 small">
                                                    <i class="feather icon-alert-octagon mr-1"></i>
                                                    <strong>LEBIH KELUAR</strong> — Movement gudang mencatat {{ abs($globalSelisih) }} pcs lebih banyak keluar dari yang ada di penjualan terkonfirmasi.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($penjualanList->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="feather icon-inbox" style="font-size:2rem"></i>
                                    <p class="mt-2">Tidak ada data transaksi penjualan pada tanggal ini.</p>
                                </div>
                            @else

                            {{-- =================================================================== --}}
                            {{-- TABEL PENJUALAN PER TRANSAKSI (PARENT: NO, KODE, RESI, ITEM, NILAI, SCAN OUT) --}}
                            {{-- =================================================================== --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-weight-bold mb-0 text-dark">
                                    <i class="feather icon-shopping-cart mr-2 text-info"></i>
                                    Data Penjualan Per Transaksi
                                    <span class="badge badge-info ml-1">{{ $penjualanList->count() }} transaksi</span>
                                </h6>
                                <div>
                                    <button class="btn btn-xs btn-outline-info mr-1" onclick="expandAllDetails()">
                                        <i class="feather icon-chevron-down mr-1"></i>Buka Semua Detail
                                    </button>
                                    <button class="btn btn-xs btn-outline-secondary" onclick="collapseAllDetails()">
                                        <i class="feather icon-chevron-up mr-1"></i>Tutup Semua Detail
                                    </button>
                                </div>
                            </div>

                            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch" class="mb-4">
                                <table class="table table-bordered mb-0" style="min-width:850px">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width:40px"></th>
                                            <th style="width:50px">No</th>
                                            <th>Kode Penjualan</th>
                                            <th>No Resi</th>
                                            <th class="text-center">Total Item / Qty</th>
                                            <th class="text-right">Total Nilai</th>
                                            <th class="text-center">Status Scan Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($penjualanList as $index => $pj)
                                            @php
                                                $pjQty = $pj->detail->sum('qty');
                                                $pjItemCount = $pj->detail->count();
                                                $scanBadge = match($pj->scan_out ?? 'nothing') {
                                                    'done' => 'badge-success',
                                                    'pending' => 'badge-warning',
                                                    'failed' => 'badge-danger',
                                                    default => 'badge-secondary'
                                                };
                                                $scanLabel = match($pj->scan_out ?? 'nothing') {
                                                    'done' => 'Done',
                                                    'pending' => 'Pending',
                                                    'failed' => 'Failed',
                                                    default => '-'
                                                };
                                            @endphp

                                            {{-- PARENT ROW TRANSAKSI --}}
                                            <tr class="tx-header-row" style="background:#f8f9fa;cursor:pointer" onclick="toggleTxDetail({{ $pj->id }})">
                                                <td class="text-center">
                                                    <i class="feather icon-chevron-down tx-icon" id="tx-icon-{{ $pj->id }}"></i>
                                                </td>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong class="text-primary">{{ $pj->kode_penjualan }}</strong></td>
                                                <td><code>{{ $pj->nomor_resi ?? '-' }}</code></td>
                                                <td class="text-center font-weight-bold">
                                                    <span class="badge badge-info">{{ $pjItemCount }} item / {{ $pjQty }} pcs</span>
                                                </td>
                                                <td class="text-right font-weight-bold text-success">
                                                    Rp {{ number_format($pj->total_harga, 0, ',', '.') }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $scanBadge }}">{{ $scanLabel }}</span>
                                                </td>
                                            </tr>

                                            {{-- CHILD ROW: DETAIL BARANG (EXTENDABLE / DEFAULT OPEN) --}}
                                            <tr class="tx-detail-row" id="tx-detail-{{ $pj->id }}" style="display:table-row">
                                                <td colspan="7" class="p-2 bg-white">
                                                    <div class="px-3 py-2 border rounded" style="background:#fcfcfc">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="font-weight-bold small text-muted">
                                                                <i class="feather icon-box mr-1"></i> Rincian Barang Transaksi #{{ $pj->kode_penjualan }}
                                                                @if($pj->dropshipper)
                                                                    ({{ $pj->dropshipper->nama }})
                                                                @endif
                                                            </span>
                                                            @if($pj->scan_out === 'done')
                                                                <span class="text-success small font-weight-bold">
                                                                    <i class="feather icon-check-circle mr-1"></i>Scan Out Terkonfirmasi
                                                                </span>
                                                            @else
                                                                <span class="text-warning small font-weight-bold">
                                                                    <i class="feather icon-clock mr-1"></i>Pending Scan Out Gudang
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- TABEL CHILD (No, SKU, Nama Barang, Awal, Dipesan, Scan Out, Gudang, Sekarang, Validasi) --}}
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="thead-light" style="background:#fff5f5">
                                                                <tr style="font-size:.82rem">
                                                                    <th style="width:35px">No</th>
                                                                    <th>SKU</th>
                                                                    <th>Nama Barang</th>
                                                                    <th class="text-center">Awal</th>
                                                                    <th class="text-center text-warning">Dipesan</th>
                                                                    <th class="text-center text-success">Scan Out</th>
                                                                    <th class="text-center text-danger">Gudang</th>
                                                                    <th class="text-center text-primary">Sekarang</th>
                                                                    <th class="text-center">Validasi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($pj->detail as $subIdx => $d)
                                                                    @php
                                                                        $bData = $barangMap[$d->barang_id] ?? null;
                                                                        $stokAwal = $bData['stok_awal_hari'] ?? ($d->barang->stok->jumlah_stok ?? '?');
                                                                        $scanOutQty = ($pj->scan_out === 'done') ? $d->qty : 0;
                                                                        $mvKeluarQty = $bData['mv_keluar'] ?? 0;
                                                                        $stokNow = $d->barang->stok->jumlah_stok ?? 0;
                                                                        $stokMin = $d->barang->stok_minimum ?? 0;

                                                                        // Validasi per item
                                                                        if ($pj->scan_out === 'done') {
                                                                            $selisihItem = $scanOutQty - $mvKeluarQty;
                                                                            if ($mvKeluarQty == $scanOutQty) {
                                                                                $itemValStatus = 'valid';
                                                                            } elseif ($mvKeluarQty < $scanOutQty) {
                                                                                $itemValStatus = 'kurang';
                                                                            } else {
                                                                                $itemValStatus = 'lebih';
                                                                            }
                                                                        } else {
                                                                            $itemValStatus = 'pending';
                                                                        }
                                                                    @endphp
                                                                    <tr style="font-size:.85rem">
                                                                        <td>{{ $subIdx + 1 }}</td>
                                                                        <td><code>{{ $d->barang->sku ?? '-' }}</code></td>
                                                                        <td>{{ $d->barang->nama_barang ?? '-' }}</td>
                                                                        <td class="text-center">{{ $stokAwal }}</td>
                                                                        <td class="text-center font-weight-bold text-warning">{{ $d->qty }} pcs</td>
                                                                        <td class="text-center font-weight-bold text-success">
                                                                            @if($pj->scan_out === 'done')
                                                                                {{ $d->qty }} pcs
                                                                            @else
                                                                                <span class="badge badge-warning" style="font-size:.65rem">Belum</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center font-weight-bold text-danger">
                                                                            @if($mvKeluarQty > 0)
                                                                                -{{ $mvKeluarQty }} pcs
                                                                            @else
                                                                                <span class="text-muted">0</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center font-weight-bold">
                                                                            <span class="{{ $stokNow <= $stokMin ? 'text-danger' : 'text-dark' }}">
                                                                                {{ $stokNow }}
                                                                            </span>
                                                                            @if($stokNow <= $stokMin)
                                                                                <span class="badge badge-danger ml-1" style="font-size:.65rem">Kritis</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($itemValStatus === 'valid')
                                                                                <span class="badge badge-success" style="font-size:.65rem"><i class="feather icon-check"></i> Valid</span>
                                                                            @elseif($itemValStatus === 'kurang')
                                                                                <span class="badge badge-warning text-dark" style="font-size:.65rem"><i class="feather icon-alert-triangle"></i> Kurang {{ $selisihItem }}</span>
                                                                            @elseif($itemValStatus === 'lebih')
                                                                                <span class="badge badge-danger" style="font-size:.65rem"><i class="feather icon-alert-octagon"></i> Lebih {{ abs($selisihItem) }}</span>
                                                                            @else
                                                                                <span class="badge badge-secondary" style="font-size:.65rem">Belum Scan</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="9" class="text-center text-muted small py-2">Tidak ada detail barang.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @endif {{-- end if penjualanList empty --}}

                        </div>
                    </div>

                </div>
            </div>
        </div>

        @include('components.footer')
    </div>
@endsection

@section('scripts')
<script>
    function toggleTxDetail(id) {
        const row = document.getElementById('tx-detail-' + id);
        const icon = document.getElementById('tx-icon-' + id);
        if (!row) return;

        if (row.style.display === 'none' || row.style.display === '') {
            row.style.display = 'table-row';
            if (icon) icon.className = 'feather icon-chevron-down tx-icon';
        } else {
            row.style.display = 'none';
            if (icon) icon.className = 'feather icon-chevron-right tx-icon';
        }
    }

    function expandAllDetails() {
        document.querySelectorAll('.tx-detail-row').forEach(row => {
            row.style.display = 'table-row';
        });
        document.querySelectorAll('.tx-icon').forEach(icon => {
            icon.className = 'feather icon-chevron-down tx-icon';
        });
    }

    function collapseAllDetails() {
        document.querySelectorAll('.tx-detail-row').forEach(row => {
            row.style.display = 'none';
        });
        document.querySelectorAll('.tx-icon').forEach(icon => {
            icon.className = 'feather icon-chevron-right tx-icon';
        });
    }
</script>
@endsection
