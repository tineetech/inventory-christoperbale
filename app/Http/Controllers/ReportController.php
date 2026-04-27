<?php

namespace App\Http\Controllers;

use App\Exports\LaporanBarangExport;
use App\Exports\LaporanPembelianExport;
use App\Exports\LaporanPenjualanExport;
use App\Exports\LaporanStokExport;
use App\Models\Barang;
use App\Models\Dropshipper;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\StokMovement;
use App\Models\StokReport;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function pembelian(Request $request)
    {
        $filters = $this->resolvePembelianFilters($request);
        $pembelian = $this->getPembelianReportQuery($filters)->get();
        $supplierOptions = Supplier::orderBy('nama_supplier')->get(['id', 'nama_supplier']);

        return view('pages.laporan.pembelian', compact('pembelian', 'supplierOptions', 'filters'));
    }

    public function pembelianPrint(Request $request)
    {
        $filters = $this->resolvePembelianFilters($request);
        $pembelian = $this->getPembelianReportQuery($filters)->get();

        return view('pages.laporan.exports.pembelian-print', compact('pembelian', 'filters'));
    }

    public function pembelianExcel(Request $request)
    {
        $filters = $this->resolvePembelianFilters($request);
        $pembelian = $this->getPembelianReportQuery($filters)->get();

        return Excel::download(
            new LaporanPembelianExport($pembelian, $filters),
            'laporan-pembelian-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function pembelianPdf(Request $request)
    {
        $filters = $this->resolvePembelianFilters($request);
        $pembelian = $this->getPembelianReportQuery($filters)->get();

        $pdf = Pdf::loadView('pages.laporan.exports.pembelian-pdf', [
            'pembelian' => $pembelian,
            'filters' => $filters,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pembelian-' . now()->format('YmdHis') . '.pdf');
    }

    public function penjualan(Request $request)
    {
        $filters = $this->resolvePenjualanFilters($request);
        $penjualan = $this->getPenjualanReportQuery($filters)->get();
        $dropshipperOptions = Dropshipper::orderBy('nama')->get(['id', 'nama']);

        return view('pages.laporan.penjualan', compact('penjualan', 'dropshipperOptions', 'filters'));
    }

    public function penjualanPrint(Request $request)
    {
        $filters = $this->resolvePenjualanFilters($request);
        $penjualan = $this->getPenjualanReportQuery($filters)->get();

        return view('pages.laporan.exports.penjualan-print', compact('penjualan', 'filters'));
    }

    public function penjualanExcel(Request $request)
    {
        $filters = $this->resolvePenjualanFilters($request);
        $penjualan = $this->getPenjualanReportQuery($filters)->get();

        return Excel::download(
            new LaporanPenjualanExport($penjualan, $filters),
            'laporan-penjualan-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function penjualanPdf(Request $request)
    {
        $filters = $this->resolvePenjualanFilters($request);
        $penjualan = $this->getPenjualanReportQuery($filters)->get();

        $pdf = Pdf::loadView('pages.laporan.exports.penjualan-pdf', [
            'penjualan' => $penjualan,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-' . now()->format('YmdHis') . '.pdf');
    }

    public function stok(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $barangOptions = Barang::orderBy('nama_barang')->get(['id', 'nama_barang']);
        $leftTableRows = $this->getStokInputRows($filters);
        $reportRows = $this->getStokSummaryRows($filters);

        return view('pages.laporan.stok', compact('barangOptions', 'filters', 'leftTableRows', 'reportRows'));
    }

    public function storeStokReport(Request $request)
    {
        $user = Auth::guard('pengguna')->user();

        if (!$user || optional($user->role)->nama_role !== 'karyawan') {
            abort(403);
        }

        $request->validate([
            'dari_tanggal' => 'required|date',
            'sampai_tanggal' => 'required|date',
            'barang_filter_id' => 'nullable|exists:barang,id',
            'status_filter' => 'nullable|in:semua,aman,minimum,habis',
            'per_page' => 'nullable|in:10,25,50,100',
            'input_per_page' => 'nullable|in:10,25,50,100',
            'summary_per_page' => 'nullable|in:10,25,50,100',
            'items' => 'required|array',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.stok_saat_ini' => 'required|integer|min:0',
            'items.*.stok_minimum' => 'required|integer|min:0',
        ]);

        [$dariTanggal, $sampaiTanggal] = $this->resolveDateRange(
            $request->dari_tanggal,
            $request->sampai_tanggal
        );

        DB::transaction(function () use ($request, $dariTanggal, $sampaiTanggal, $user) {
            foreach ($request->items as $item) {
                StokReport::updateOrCreate(
                    [
                        'barang_id' => $item['barang_id'],
                        'dari_tanggal' => $dariTanggal,
                        'sampai_tanggal' => $sampaiTanggal,
                    ],
                    [
                        'stok_saat_ini' => $item['stok_saat_ini'],
                        'stok_minimum' => $item['stok_minimum'],
                        'status' => 'pending',
                        'input_by' => $user->id,
                        'confirmed_by' => null,
                        'confirmed_at' => null,
                    ]
                );
            }
        });

        return redirect()->route('laporan.stok', [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'barang_id' => $request->filled('barang_filter_id') ? (int) $request->barang_filter_id : null,
            'status' => in_array($request->status_filter, ['aman', 'minimum', 'habis'], true)
                ? $request->status_filter
                : 'semua',
            'per_page' => $this->resolvePerPage($request),
            'input_per_page' => $this->resolvePerPageValue($request->input('input_per_page', $request->input('per_page'))),
            'summary_per_page' => $this->resolvePerPageValue($request->input('summary_per_page', $request->input('per_page'))),
        ])->with('success', 'Input laporan stok berhasil disimpan dan menunggu konfirmasi super admin.');
    }

    public function confirmStokReport(StokReport $stokReport)
    {
        $user = Auth::guard('pengguna')->user();

        if (!$user || optional($user->role)->nama_role !== 'super_admin') {
            abort(403);
        }

        $stokReport->update([
            'status' => 'confirmed',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Laporan stok berhasil dikonfirmasi.');
    }

    public function stokPrint(Request $request)
    {
        return $this->stokSummaryPrint($request);
    }

    public function stokExcel(Request $request)
    {
        return $this->stokSummaryExcel($request);
    }

    public function stokPdf(Request $request)
    {
        return $this->stokSummaryPdf($request);
    }

    public function stokInputPrint(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $rows = $this->getStokInputRows($filters);

        return view('pages.laporan.exports.stok-print', [
            'rows' => $rows,
            'filters' => $filters,
            'tableType' => 'input',
            'title' => 'Data Laporan Stok',
        ]);
    }

    public function stokInputExcel(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $rows = $this->getStokInputRows($filters);

        return Excel::download(
            new LaporanStokExport($rows, $filters, 'input'),
            'laporan-stok-data-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function stokInputPdf(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $rows = $this->getStokInputRows($filters);

        $pdf = Pdf::loadView('pages.laporan.exports.stok-pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'tableType' => 'input',
            'title' => 'Data Laporan Stok',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-data-' . now()->format('YmdHis') . '.pdf');
    }

    public function stokSummaryPrint(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $rows = $this->getStokSummaryRows($filters);

        return view('pages.laporan.exports.stok-print', [
            'rows' => $rows,
            'filters' => $filters,
            'tableType' => 'summary',
            'title' => 'Ringkasan Laporan Stok',
        ]);
    }

    public function stokSummaryExcel(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $rows = $this->getStokSummaryRows($filters);

        return Excel::download(
            new LaporanStokExport($rows, $filters, 'summary'),
            'laporan-stok-ringkasan-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function stokSummaryPdf(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $rows = $this->getStokSummaryRows($filters);

        $pdf = Pdf::loadView('pages.laporan.exports.stok-pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'tableType' => 'summary',
            'title' => 'Ringkasan Laporan Stok',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-stok-ringkasan-' . now()->format('YmdHis') . '.pdf');
    }

    public function barang(Request $request)
    {
        $filters = $this->resolveBarangFilters($request);
        $barang = $this->getBarangReportQuery($filters)->get();
        $barangOptions = Barang::orderBy('nama_barang')->get(['id', 'nama_barang']);

        return view('pages.laporan.barang', compact('barang', 'barangOptions', 'filters'));
    }

    public function barangPdf(Request $request)
    {
        $filters = $this->resolveBarangFilters($request);
        $barang = $this->getBarangReportQuery($filters)->get();

        $pdf = Pdf::loadView('pages.laporan.exports.barang-pdf', [
            'barang' => $barang,
            'filters' => $filters,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-barang-' . now()->format('YmdHis') . '.pdf');
    }

    public function barangPrint(Request $request)
    {
        $filters = $this->resolveBarangFilters($request);
        $barang = $this->getBarangReportQuery($filters)->get();

        return view('pages.laporan.exports.barang-print', [
            'barang' => $barang,
            'filters' => $filters,
        ]);
    }

    public function barangExcel(Request $request)
    {
        $filters = $this->resolveBarangFilters($request);
        $barang = $this->getBarangReportQuery($filters)->get();

        return Excel::download(
            new LaporanBarangExport($barang, $filters),
            'laporan-barang-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    private function getBarangReportQuery(array $filters)
    {
        $query = Barang::with('satuan', 'stok')
            ->whereDate('created_at', '>=', $filters['dari_tanggal'])
            ->whereDate('created_at', '<=', $filters['sampai_tanggal']);

        if ($filters['barang_id']) {
            $query->where('id', $filters['barang_id']);
        }

        if ($filters['stok'] === 'habis') {
            $query->where(function ($stokQuery) {
                $stokQuery->whereHas('stok', function ($stokRelationQuery) {
                    $stokRelationQuery->where('jumlah_stok', '<=', 0);
                })->orWhereDoesntHave('stok');
            });
        }

        if ($filters['stok'] === 'minimum') {
            $query->whereHas('stok', function ($stokQuery) {
                $stokQuery->whereColumn('jumlah_stok', '<=', 'barang.stok_minimum')
                    ->where('jumlah_stok', '>', 0);
            });
        }

        if ($filters['stok'] === 'aman') {
            $query->whereHas('stok', function ($stokQuery) {
                $stokQuery->whereColumn('jumlah_stok', '>', 'barang.stok_minimum');
            });
        }

        return $query->latest();
    }

    private function getPembelianReportQuery(array $filters)
    {
        $query = Pembelian::with('supplier', 'user', 'detail.barang.stok')
            ->whereDate('tanggal', '>=', $filters['dari_tanggal'])
            ->whereDate('tanggal', '<=', $filters['sampai_tanggal']);

        if ($filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->latest('tanggal');
    }

    private function getPenjualanReportQuery(array $filters)
    {
        $query = Penjualan::with('dropshipper', 'user', 'detail.barang.stok')
            ->whereDate('tanggal', '>=', $filters['dari_tanggal'])
            ->whereDate('tanggal', '<=', $filters['sampai_tanggal']);

        if ($filters['dropshipper_id']) {
            $query->where('dropshipper_id', $filters['dropshipper_id']);
        }

        return $query->latest('tanggal');
    }

    private function getLegacyStokTableQuery(array $filters)
    {
        $query = Barang::with('satuan', 'stok')
            ->whereDate('created_at', '>=', $filters['dari_tanggal'])
            ->whereDate('created_at', '<=', $filters['sampai_tanggal']);

        if ($filters['barang_id']) {
            $query->where('id', $filters['barang_id']);
        }

        if ($filters['status'] === 'habis') {
            $query->where(function ($stokQuery) {
                $stokQuery->whereHas('stok', function ($stokRelationQuery) {
                    $stokRelationQuery->where('jumlah_stok', '<=', 0);
                })->orWhereDoesntHave('stok');
            });
        }

        if ($filters['status'] === 'minimum') {
            $query->whereHas('stok', function ($stokQuery) {
                $stokQuery->whereColumn('jumlah_stok', '<=', 'barang.stok_minimum')
                    ->where('jumlah_stok', '>', 0);
            });
        }

        if ($filters['status'] === 'aman') {
            $query->whereHas('stok', function ($stokQuery) {
                $stokQuery->whereColumn('jumlah_stok', '>', 'barang.stok_minimum');
            });
        }

        return $query->orderBy('nama_barang');
    }

    private function getStokInputRows(array $filters)
    {
        $stokItems = $this->getLegacyStokTableQuery($filters)->get();

        return $this->buildLegacyStokTableRows($stokItems, $filters);
    }

    private function buildLegacyStokTableRows($stokItems, array $filters)
    {
        $stokReports = StokReport::with(['inputByUser', 'confirmedByUser'])
            ->whereDate('dari_tanggal', $filters['dari_tanggal'])
            ->whereDate('sampai_tanggal', $filters['sampai_tanggal'])
            ->get()
            ->keyBy('barang_id');

        return $stokItems->values()->map(function ($item, $index) use ($stokReports) {
            $report = $stokReports->get($item->id);
            $stokSaatIni = (int) ($report->stok_saat_ini ?? ($item->stok->jumlah_stok ?? 0));
            $stokMinimum = (int) ($report->stok_minimum ?? ($item->stok_minimum ?? 0));

            return (object) [
                'no' => $index + 1,
                'barang_id' => $item->id,
                'sku' => $item->sku,
                'nama_barang' => $item->nama_barang,
                'satuan' => $item->satuan->nama_satuan ?? '-',
                'stok_saat_ini' => $stokSaatIni,
                'stok_minimum' => $stokMinimum,
                'stok_status' => $this->resolveStockStatus($stokSaatIni, $stokMinimum),
                'report_id' => $report->id ?? null,
                'approval_status' => $report->status ?? null,
                'input_by_name' => optional($report?->inputByUser)->nama,
                'confirmed_by_name' => optional($report?->confirmedByUser)->nama,
                'confirmed_at' => $report->confirmed_at ?? null,
                'has_input' => (bool) $report,
            ];
        });
    }

    private function getStokReportQuery(array $filters)
    {
        $movements = StokMovement::with(['barang.satuan', 'user'])
            ->whereDate('created_at', '>=', $filters['dari_tanggal'])
            ->whereDate('created_at', '<=', $filters['sampai_tanggal']);

        if ($filters['barang_id']) {
            $movements->where('barang_id', $filters['barang_id']);
        }

        $movements = $movements
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->unique('barang_id')
            ->values();

        if ($filters['status'] !== 'semua') {
            $movements = $movements->filter(function ($movement) use ($filters) {
                $stokSaatIni = (int) $movement->stok_sesudah;
                $stokMinimum = (int) optional($movement->barang)->stok_minimum;

                return $this->resolveStockStatus($stokSaatIni, $stokMinimum) === $filters['status'];
            })->values();
        }

        return $movements->sortBy(function ($movement) {
            return strtolower(optional($movement->barang)->nama_barang ?? '');
        })->values();
    }

    private function buildStokReportRows($stokMovements, array $filters)
    {
        $stokReports = StokReport::with(['inputByUser', 'confirmedByUser'])
            ->whereDate('dari_tanggal', $filters['dari_tanggal'])
            ->whereDate('sampai_tanggal', $filters['sampai_tanggal'])
            ->get()
            ->keyBy('barang_id');

        return $stokMovements->values()->map(function ($movement, $index) use ($stokReports) {
            $barang = $movement->barang;
            $report = $stokReports->get($movement->barang_id);
            $stokSaatIni = (int) ($movement->stok_sesudah ?? 0);
            $stokMinimum = (int) ($barang->stok_minimum ?? 0);

            return (object) [
                'no' => $index + 1,
                'movement_id' => $movement->id,
                'movement_date' => $movement->created_at,
                'barang_id' => $movement->barang_id,
                'sku' => $barang->sku ?? '-',
                'nama_barang' => $barang->nama_barang ?? '-',
                'satuan' => $barang->satuan->nama_satuan ?? '-',
                'jenis' => $movement->jenis,
                'qty' => (int) $movement->qty,
                'stok_sebelum' => (int) $movement->stok_sebelum,
                'stok_sesudah' => (int) $movement->stok_sesudah,
                'referensi_tipe' => $movement->referensi_tipe,
                'referensi_id' => $movement->referensi_id,
                'keterangan' => $movement->keterangan,
                'movement_by_name' => optional($movement->user)->nama,
                'stok_saat_ini' => $stokSaatIni,
                'stok_minimum' => $stokMinimum,
                'selisih_minimum' => $stokSaatIni - $stokMinimum,
                'stok_status' => $this->resolveStockStatus($stokSaatIni, $stokMinimum),
                'report_id' => $report->id ?? null,
                'approval_status' => $report->status ?? null,
                'input_by_name' => optional($report?->inputByUser)->nama,
                'confirmed_by_name' => optional($report?->confirmedByUser)->nama,
                'confirmed_at' => $report->confirmed_at ?? null,
                'has_input' => (bool) $report,
            ];
        });
    }

    private function getStokSummaryRows(array $filters)
    {
        $stokMovements = $this->getStokReportQuery($filters);

        return $this->buildStokReportRows($stokMovements, $filters);
    }

    private function resolveStockStatus(int $stokSaatIni, int $stokMinimum): string
    {
        if ($stokSaatIni <= 0) {
            return 'habis';
        }

        if ($stokSaatIni <= $stokMinimum) {
            return 'minimum';
        }

        return 'aman';
    }

    private function resolveBarangFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'barang_id' => 'nullable|exists:barang,id',
            'stok' => 'nullable|in:semua,aman,minimum,habis',
            'per_page' => 'nullable|in:10,25,50,100',
        ]);

        $today = now();
        $defaultStart = $today->copy()->startOfMonth()->toDateString();
        $defaultEnd = $today->copy()->endOfMonth()->toDateString();

        $dariTanggal = $request->filled('dari_tanggal')
            ? Carbon::parse($request->dari_tanggal)->toDateString()
            : $defaultStart;

        $sampaiTanggal = $request->filled('sampai_tanggal')
            ? Carbon::parse($request->sampai_tanggal)->toDateString()
            : $defaultEnd;

        if ($dariTanggal > $sampaiTanggal) {
            [$dariTanggal, $sampaiTanggal] = [$sampaiTanggal, $dariTanggal];
        }

        return [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'barang_id' => $request->filled('barang_id') ? (int) $request->barang_id : null,
            'stok' => in_array($request->stok, ['aman', 'minimum', 'habis'], true) ? $request->stok : 'semua',
            'per_page' => $this->resolvePerPage($request),
        ];
    }

    private function resolvePembelianFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'supplier_id' => 'nullable|exists:supplier,id',
            'per_page' => 'nullable|in:10,25,50,100',
        ]);

        [$dariTanggal, $sampaiTanggal] = $this->resolveDateRange(
            $request->dari_tanggal,
            $request->sampai_tanggal,
            'today'
        );

        return [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'supplier_id' => $request->filled('supplier_id') ? (int) $request->supplier_id : null,
            'per_page' => $this->resolvePerPage($request),
        ];
    }

    private function resolvePenjualanFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'dropshipper_id' => 'nullable|exists:dropshipper,id',
            'per_page' => 'nullable|in:10,25,50,100',
        ]);

        [$dariTanggal, $sampaiTanggal] = $this->resolveDateRange(
            $request->dari_tanggal,
            $request->sampai_tanggal,
            'today'
        );

        return [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'dropshipper_id' => $request->filled('dropshipper_id') ? (int) $request->dropshipper_id : null,
            'per_page' => $this->resolvePerPage($request),
        ];
    }

    private function resolveStokFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'barang_id' => 'nullable|exists:barang,id',
            'status' => 'nullable|in:semua,aman,minimum,habis',
            'per_page' => 'nullable|in:10,25,50,100',
            'input_per_page' => 'nullable|in:10,25,50,100',
            'summary_per_page' => 'nullable|in:10,25,50,100',
        ]);

        [$dariTanggal, $sampaiTanggal] = $this->resolveDateRange(
            $request->dari_tanggal,
            $request->sampai_tanggal
        );

        return [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'barang_id' => $request->filled('barang_id') ? (int) $request->barang_id : null,
            'status' => in_array($request->status, ['aman', 'minimum', 'habis'], true) ? $request->status : 'semua',
            'per_page' => $this->resolvePerPage($request),
            'input_per_page' => $this->resolvePerPageValue($request->input('input_per_page', $request->input('per_page'))),
            'summary_per_page' => $this->resolvePerPageValue($request->input('summary_per_page', $request->input('per_page'))),
        ];
    }

    private function resolvePerPage(Request $request): int
    {
        return $this->resolvePerPageValue($request->input('per_page', 10));
    }

    private function resolvePerPageValue($value): int
    {
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $value;

        return in_array($perPage, $allowedPerPage, true) ? $perPage : 10;
    }

    private function resolveDateRange(?string $startDate, ?string $endDate, string $defaultRange = 'month'): array
    {
        $today = now();
        $defaultStart = $defaultRange === 'today'
            ? $today->copy()->toDateString()
            : $today->copy()->startOfMonth()->toDateString();
        $defaultEnd = $defaultRange === 'today'
            ? $today->copy()->toDateString()
            : $today->copy()->endOfMonth()->toDateString();

        $dariTanggal = $startDate ? Carbon::parse($startDate)->toDateString() : $defaultStart;
        $sampaiTanggal = $endDate ? Carbon::parse($endDate)->toDateString() : $defaultEnd;

        if ($dariTanggal > $sampaiTanggal) {
            [$dariTanggal, $sampaiTanggal] = [$sampaiTanggal, $dariTanggal];
        }

        return [$dariTanggal, $sampaiTanggal];
    }
}
