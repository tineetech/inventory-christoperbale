@extends('layouts.main')

@section('content')
    <div class="layout-content">

        <div class="container-fluid flex-grow-1 container-p-y">

            <h4 class="font-weight-bold py-3 mb-0">Dashboard</h4>

            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>

            <div class="row">

                <!-- LEFT SIDE -->

                @if (hasPermission('lihat', 'dashboard_trx'))
                    <div class="col-lg-5">
                    @else
                        <div class="col-lg-12">
                @endif
                <div class="row">

                    <!-- TOTAL BARANG -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h2 class="mb-2">{{ $totalBarang }}</h2>
                                        <p class="text-muted mb-0">
                                            <span class="badge badge-primary">Barang</span> Terdaftar
                                        </p>
                                    </div>
                                    <div class="lnr lnr-database display-4 text-primary"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAL STOK -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h2 class="mb-2">{{ $totalStok }}</h2>
                                        <p class="text-muted mb-0">
                                            <span class="badge badge-success">Stok</span> Gudang
                                        </p>
                                    </div>
                                    <div class="lnr lnr-layers display-4 text-success"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PENJUALAN HARI INI -->

                    @if (hasPermission('lihat', 'dashboard_trx'))
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-2">{{ $penjualanHariIni }}</h2>
                                            <p class="text-muted mb-0">
                                                <span class="badge badge-danger">Penjualan</span> Hari Ini
                                            </p>
                                        </div>
                                        <div class="lnr lnr-cart display-4 text-danger"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PEMBELIAN HARI INI -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h2 class="mb-2">{{ $pembelianHariIni }}</h2>
                                            <p class="text-muted mb-0">
                                                <span class="badge badge-warning">Pembelian</span> Hari Ini
                                            </p>
                                        </div>
                                        <div class="lnr lnr-enter display-4 text-warning"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- INFO TAMBAHAN -->
                    <div class="col-sm-12">
                        <div class="card d-flex w-100 mb-4">
                            <div class="row no-gutters row-bordered row-border-light h-100">

                                <!-- SUPPLIER -->
                                <div class="d-flex col-md-6 align-items-center">
                                    <div class="card-body">
                                        <div class="row align-items-center mb-3">
                                            <div class="col-auto">
                                                <i class="lnr lnr-store text-primary display-4"></i>
                                            </div>
                                            <div class="col">
                                                <h6 class="mb-0 text-muted">Total <span class="text-primary">Supplier</span>
                                                </h6>
                                                <h4 class="mt-3 mb-0">{{ $totalSupplier }}</h4>
                                            </div>
                                        </div>
                                        <p class="mb-0 text-muted">Supplier terdaftar</p>
                                    </div>
                                </div>

                                <!-- DROPSHIPPER -->
                                <div class="d-flex col-md-6 align-items-center">
                                    <div class="card-body">
                                        <div class="row align-items-center mb-3">
                                            <div class="col-auto">
                                                <i class="lnr lnr-user text-success display-4"></i>
                                            </div>
                                            <div class="col">
                                                <h6 class="mb-0 text-muted">Total <span
                                                        class="text-success">Dropshipper</span></h6>
                                                <h4 class="mt-3 mb-0">{{ $totalDropshipper }}</h4>
                                            </div>
                                        </div>
                                        <p class="mb-0 text-muted">Dropshipper aktif</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT SIDE -->

            <div class="col-lg-7">

                @if (hasPermission('lihat', 'dashboard_trx'))
                    <div class="card mb-4">
                        <div class="card-header with-elements">
                            <div>
                                <h6 class="card-header-title mb-0">
                                    <i class="feather icon-bar-chart-2 mr-1 text-primary"></i>
                                    Statistik Penjualan
                                    <span class="badge badge-primary badge-pill ml-1" style="font-size:.7rem;">
                                        Jan – Jun {{ $tahun }}
                                    </span>
                                </h6>
                                <small class="text-muted">Jumlah transaksi &amp; omzet per bulan</small>
                            </div>
                            <div class="card-header-elements ml-auto d-flex align-items-center gap-3">

                                {{-- Summary kecil --}}
                                <div class="text-right mr-3 d-none d-md-block">
                                    <div class="small text-muted">Total Transaksi</div>
                                    <strong class="text-primary">{{ number_format($totalTransaksiH1) }}</strong>
                                </div>
                                <div class="text-right mr-3 d-none d-md-block">
                                    <div class="small text-muted">Total Omzet</div>
                                    <strong class="text-danger">Rp {{ number_format($totalOmzetH1, 0, ',', '.') }}</strong>
                                </div>

                                {{-- Toggle show/hide --}}
                                <label class="text m-0">
                                    <span class="text-light text-tiny font-weight-semibold align-middle">SHOW STATS</span>
                                    <span
                                        class="switcher switcher-primary switcher-sm d-inline-block align-middle mr-0 ml-2">
                                        <input type="checkbox" class="switcher-input" id="toggleStats" checked>
                                        <span class="switcher-indicator">
                                            <span class="switcher-yes"></span>
                                            <span class="switcher-no"></span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="card-body" id="statisticsChartWrap">

                            {{-- Mini summary row (mobile) --}}
                            <div class="row mb-3 d-md-none">
                                <div class="col-6 text-center">
                                    <div class="small text-muted">Total Transaksi</div>
                                    <strong class="text-primary">{{ number_format($totalTransaksiH1) }}</strong>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="small text-muted">Total Omzet</div>
                                    <strong class="text-danger">Rp
                                        {{ number_format($totalOmzetH1, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            {{-- Legend manual --}}
                            <div class="d-flex align-items-center mb-2" style="gap:16px; font-size:.8rem;">
                                <span>
                                    <span
                                        style="display:inline-block;width:24px;height:3px;background:#4e73df;border-radius:2px;vertical-align:middle;margin-right:4px;"></span>
                                    Transaksi (pcs)
                                </span>
                                <span>
                                    <span
                                        style="display:inline-block;width:24px;height:3px;background:#ff4a00;border-radius:2px;vertical-align:middle;margin-right:4px;"></span>
                                    Omzet (Rp)
                                </span>
                            </div>

                            <div id="statistics-chart-1" style="height:300px; width:100%;"></div>
                        </div>
                    </div>
                @endif
            </div>

        </div>

        <!-- BOTTOM SECTION -->
        {{-- Stok Kritis dan penjualan draft --}}
        <div class="row mt-2">
            {{-- Draft Penjualan --}}
            @if (hasPermission('lihat', 'dashboard_trx'))
                <div class="col-md-6">
                    <div class="card p-3 mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center" style="border:none">
                            <h6 class="mb-0 text-warning">
                                <i class="feather icon-file-text mr-2"></i> Penjualan Draft
                                <small class="text-muted">Transaksi yang belum diselesaikan</small>
                            </h6>
                            <span class="badge badge-warning">{{ $penjualanDraft->total() }} transaksi</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Tanggal</th>
                                        <th>Dropshipper</th>
                                        <th>No. Pesanan</th>
                                        <th>Total Harga</th>
                                        <th>Dibuat Oleh</th>
                                        <th>Terakhir Update</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($penjualanDraft as $index => $draft)
                                        <tr onclick="window.location='{{ route('penjualan.edit', $draft->id) }}'"
                                            style="cursor: pointer">
                                            <td>{{ $penjualanDraft->firstItem() + $index }}</td>
                                            <td>
                                                <code>{{ $draft->kode_penjualan ?? '-' }}</code>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($draft->tanggal)->format('d/m/Y') }}</td>
                                            <td>{{ $draft->dropshipper->nama ?? '-' }}</td>
                                            <td>{{ $draft->nomor_pesanan ?? '-' }}</td>
                                            <td>Rp {{ number_format($draft->total_harga ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $draft->user->nama ?? '-' }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $draft->updated_at->diffForHumans() }}
                                                </small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="feather icon-check-circle text-success mr-2"></i>
                                                Tidak ada penjualan draft
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer: info + pagination --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">

                            <div class="text-muted small">
                                @if ($penjualanDraft->total() > 0)
                                    Showing <strong>{{ $penjualanDraft->firstItem() }}</strong>
                                    to <strong>{{ $penjualanDraft->lastItem() }}</strong>
                                    of <strong>{{ $penjualanDraft->total() }}</strong> entries
                                @else
                                    No entries found
                                @endif
                            </div>

                            @if ($penjualanDraft->hasPages())
                                @php
                                    $currentPage = $penjualanDraft->currentPage();
                                    $lastPage    = $penjualanDraft->lastPage();
                                    $showStartEllipsis = $currentPage > 3;
                                    $showEndEllipsis   = $currentPage < $lastPage - 2;
                                    $start = max(2, $currentPage - 1);
                                    $end   = min($lastPage - 1, $currentPage + 1);
                                    if ($currentPage <= 3) $end   = min($lastPage - 1, 4);
                                    if ($currentPage >= $lastPage - 2) $start = max(2, $lastPage - 3);
                                @endphp

                                <ul class="pagination pagination-sm mb-0 flex-wrap">

                                    <li class="page-item {{ $penjualanDraft->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $penjualanDraft->previousPageUrl() ?? '#' }}">
                                            <i class="feather icon-chevron-left"></i>
                                        </a>
                                    </li>

                                    @if ($lastPage <= 7)
                                        @for ($i = 1; $i <= $lastPage; $i++)
                                            <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $penjualanDraft->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endfor
                                    @else
                                        <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $penjualanDraft->url(1) }}">1</a>
                                        </li>

                                        @if ($showStartEllipsis)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif

                                        @for ($i = $start; $i <= $end; $i++)
                                            <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $penjualanDraft->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endfor

                                        @if ($showEndEllipsis)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif

                                        <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $penjualanDraft->url($lastPage) }}">{{ $lastPage }}</a>
                                        </li>
                                    @endif

                                    <li class="page-item {{ !$penjualanDraft->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $penjualanDraft->nextPageUrl() ?? '#' }}">
                                            <i class="feather icon-chevron-right"></i>
                                        </a>
                                    </li>

                                </ul>
                            @endif

                        </div>

                    </div>
                </div>
            @endif
            @if (hasPermission('lihat', 'dashboard_trx'))
            <div class="col-md-6">
            @else
            <div class="col-md-12">
            @endif
                <div class="card p-3 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center" style="border:none">
                        <h6 class="mb-0 text-danger">
                            <i class="feather icon-alert-triangle mr-2"></i> Stok Kritis
                            <small class="text-muted">Stok dibawah minimum dan dibawah 10 yang perlu di restock</small>
                        </h6>
                        <span class="badge badge-danger">{{ $stokKritis->total() }} barang</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>SKU</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Stok Minimum</th>
                                    <th>Stok Sekarang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stokKritis as $index => $item)
                                    <tr onclick="window.open('/master/barang/edit/' + {{ $item->id }})"
                                        style="cursor: pointer">
                                        <td>{{ $stokKritis->firstItem() + $index }}</td>
                                        <td><code>{{ $item->sku }}</code></td>
                                        <td><strong>{{ $item->nama_barang }}</strong></td>
                                        <td>{{ $item->satuan->nama_satuan }}</td>
                                        <td>{{ $item->stok_minimum }}</td>
                                        <td>
                                            <span class="text-danger font-weight-bold">
                                                {{ $item->stok->jumlah_stok ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $stokNow = $item->stok->jumlah_stok ?? 0; @endphp
                                            @if ($stokNow == 0)
                                                <span class="badge badge-danger">Habis</span>
                                            @else
                                                <span class="badge badge-warning">Kritis</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="feather icon-check-circle text-success mr-2"></i>
                                            Semua stok aman
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer: per_page + info + pagination --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-3 border-top gap-2">

                        {{-- Per page selector --}}
                        <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                            {{-- Pertahankan query string lain --}}
                            @foreach (request()->except(['kritis_per_page', 'kritis_page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach

                            <span class="text-muted small">Show</span>
                            <select name="kritis_per_page" class="form-control form-control-sm" style="width: 75px"
                                onchange="this.form.submit()">
                                @foreach ([10, 25, 50, 100] as $n)
                                    <option value="{{ $n }}"
                                        {{ request('kritis_per_page', 10) == $n ? 'selected' : '' }}>
                                        {{ $n }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-muted small">entries</span>
                        </form>

                        {{-- Info showing --}}
                        <div class="text-muted small">
                            @if ($stokKritis->total() > 0)
                                Showing <strong>{{ $stokKritis->firstItem() }}</strong>
                                to <strong>{{ $stokKritis->lastItem() }}</strong>
                                of <strong>{{ $stokKritis->total() }}</strong> entries
                            @else
                                No entries found
                            @endif
                        </div>

                        {{-- Pagination --}}
                        @if ($stokKritis->hasPages())
                            @php
                                $currentPage = $stokKritis->currentPage();
                                $lastPage = $stokKritis->lastPage();
                                $showStartEllipsis = $currentPage > 3;
                                $showEndEllipsis = $currentPage < $lastPage - 2;
                                $start = max(2, $currentPage - 1);
                                $end = min($lastPage - 1, $currentPage + 1);
                                if ($currentPage <= 3) {
                                    $end = min($lastPage - 1, 4);
                                }
                                if ($currentPage >= $lastPage - 2) {
                                    $start = max(2, $lastPage - 3);
                                }
                            @endphp

                            <ul class="pagination pagination-sm mb-0 flex-wrap">

                                {{-- Prev --}}
                                <li class="page-item {{ $stokKritis->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $stokKritis->previousPageUrl() ?? '#' }}">
                                        <i class="feather icon-chevron-left"></i>
                                    </a>
                                </li>

                                @if ($lastPage <= 7)
                                    @for ($i = 1; $i <= $lastPage; $i++)
                                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                            <a class="page-link"
                                                href="{{ $stokKritis->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor
                                @else
                                    <li class="page-item {{ $currentPage == 1 ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $stokKritis->url(1) }}">1</a>
                                    </li>

                                    @if ($showStartEllipsis)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif

                                    @for ($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                            <a class="page-link"
                                                href="{{ $stokKritis->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    @if ($showEndEllipsis)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif

                                    <li class="page-item {{ $currentPage == $lastPage ? 'active' : '' }}">
                                        <a class="page-link"
                                            href="{{ $stokKritis->url($lastPage) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif

                                {{-- Next --}}
                                <li class="page-item {{ !$stokKritis->hasMorePages() ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $stokKritis->nextPageUrl() ?? '#' }}">
                                        <i class="feather icon-chevron-right"></i>
                                    </a>
                                </li>

                            </ul>
                        @endif

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
        $(function() {
            @if (hasPermission('lihat', 'dashboard_trx'))

                // ── Data dari PHP ──────────────────────────────────────────
                const bulanLabels = @json($bulanLabels); // ['Jan','Feb',...]
                const dataTransaksi = @json($dataTransaksi); // [12, 8, ...]
                const dataOmzet = @json($dataOmzet); // [1500000, ...]

                // Gabung jadi format amCharts
                const chartData = bulanLabels.map((label, i) => ({
                    bulan: label,
                    transaksi: dataTransaksi[i],
                    omzet: dataOmzet[i],
                }));

                // ── amCharts ───────────────────────────────────────────────
                setTimeout(function() {
                    am4core.useTheme(am4themes_animated);
                    var chart = am4core.create("statistics-chart-1", am4charts.XYChart);
                    chart.data = chartData;

                    // ── X Axis (bulan) ──
                    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                    categoryAxis.dataFields.category = "bulan";
                    categoryAxis.renderer.minGridDistance = 20;
                    categoryAxis.renderer.grid.template.strokeOpacity = 0;
                    categoryAxis.renderer.labels.template.fontSize = 12;

                    // ── Y Axis kiri — Transaksi ──
                    var valueAxisLeft = chart.yAxes.push(new am4charts.ValueAxis());
                    valueAxisLeft.title.text = "Transaksi";
                    valueAxisLeft.title.fontSize = 11;
                    valueAxisLeft.renderer.labels.template.fontSize = 11;
                    valueAxisLeft.min = 0;
                    valueAxisLeft.strictMinMax = true;
                    valueAxisLeft.renderer.grid.template.strokeDasharray = "4,4";
                    valueAxisLeft.renderer.grid.template.strokeOpacity = 0.4;

                    // ── Y Axis kanan — Omzet ──
                    var valueAxisRight = chart.yAxes.push(new am4charts.ValueAxis());
                    valueAxisRight.title.text = "Omzet (Rp)";
                    valueAxisRight.title.fontSize = 11;
                    valueAxisRight.renderer.opposite = true;
                    valueAxisRight.renderer.labels.template.fontSize = 11;
                    valueAxisRight.renderer.grid.template.strokeOpacity = 0;
                    valueAxisRight.min = 0;
                    valueAxisRight.strictMinMax = true;
                    valueAxisRight.numberFormatter.numberFormat = "#,###";

                    // ── Series 1: Transaksi (garis biru putus-putus) ──
                    var seriesTransaksi = chart.series.push(new am4charts.LineSeries());
                    seriesTransaksi.dataFields.valueY = "transaksi";
                    seriesTransaksi.dataFields.categoryX = "bulan";
                    seriesTransaksi.yAxis = valueAxisLeft;
                    seriesTransaksi.name = "Transaksi";
                    seriesTransaksi.tooltipText = "Transaksi: [bold]{transaksi} pcs[/]";
                    seriesTransaksi.strokeWidth = 3;
                    seriesTransaksi.strokeDasharray = "8,4";
                    seriesTransaksi.tensionX = 0.8;
                    seriesTransaksi.tensionY = 1;
                    seriesTransaksi.stroke = am4core.color("#4e73df");
                    seriesTransaksi.fill = am4core.color("#4e73df");

                    // Bullet titik
                    var bulletT = seriesTransaksi.bullets.push(new am4charts.CircleBullet());
                    bulletT.circle.radius = 5;
                    bulletT.circle.strokeWidth = 2;
                    bulletT.circle.fill = am4core.color("#fff");
                    bulletT.circle.stroke = am4core.color("#4e73df");

                    // Label nilai
                    var labelT = seriesTransaksi.bullets.push(new am4charts.LabelBullet());
                    labelT.label.text = "{transaksi}";
                    labelT.label.dy = -14;
                    labelT.label.fontSize = 11;
                    labelT.label.fill = am4core.color("#4e73df");
                    labelT.label.fontWeight = "bold";

                    // ── Series 2: Omzet (garis merah solid) ──
                    var seriesOmzet = chart.series.push(new am4charts.LineSeries());
                    seriesOmzet.dataFields.valueY = "omzet";
                    seriesOmzet.dataFields.categoryX = "bulan";
                    seriesOmzet.yAxis = valueAxisRight;
                    seriesOmzet.name = "Omzet";
                    seriesOmzet.tooltipText = "Omzet: [bold]Rp {omzet.formatNumber('#,###')}[/]";
                    seriesOmzet.strokeWidth = 3;
                    seriesOmzet.tensionX = 0.8;
                    seriesOmzet.tensionY = 1;
                    seriesOmzet.stroke = am4core.color("#ff4a00");
                    seriesOmzet.fill = am4core.color("#ff4a00");

                    // Drop shadow omzet
                    var shadow = new am4core.DropShadowFilter();
                    shadow.dy = 10;
                    shadow.dx = 1;
                    shadow.blur = 8;
                    shadow.opacity = 0.4;
                    shadow.color = '#ff4a00';
                    seriesOmzet.filters.push(shadow);

                    // Bullet titik
                    var bulletO = seriesOmzet.bullets.push(new am4charts.CircleBullet());
                    bulletO.circle.radius = 5;
                    bulletO.circle.strokeWidth = 2;
                    bulletO.circle.fill = am4core.color("#fff");
                    bulletO.circle.stroke = am4core.color("#ff4a00");

                    // ── Cursor & Scrollbar ──
                    chart.cursor = new am4charts.XYCursor();
                    chart.cursor.lineY.opacity = 0;

                    // ── Tooltip styling ──
                    chart.tooltip.getFillFromObject = false;
                    chart.tooltip.background.fill = am4core.color("#333");

                }, 400);
            @endif

            // ── Toggle show/hide chart ──────────────────────────────
            $('#toggleStats').on('change', function() {
                $('#statisticsChartWrap').slideToggle(300);
            });

        });
    </script>
@endsection
