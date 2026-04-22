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
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function stok(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $barang = $this->getStokReportQuery($filters)->get();
        $barangOptions = Barang::orderBy('nama_barang')->get(['id', 'nama_barang']);

        return view('pages.laporan.stok', compact('barang', 'barangOptions', 'filters'));
    }

    public function stokPrint(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $barang = $this->getStokReportQuery($filters)->get();

        return view('pages.laporan.exports.stok-print', compact('barang', 'filters'));
    }

    public function stokExcel(Request $request)
    {
        $filters = $this->resolveStokFilters($request);
        $barang = $this->getStokReportQuery($filters)->get();

        return Excel::download(
            new LaporanStokExport($barang, $filters),
            'laporan-stok-' . now()->format('YmdHis') . '.xlsx'
        );
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
        $query = Pembelian::with('supplier', 'user')
            ->whereDate('tanggal', '>=', $filters['dari_tanggal'])
            ->whereDate('tanggal', '<=', $filters['sampai_tanggal']);

        if ($filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->latest('tanggal');
    }

    private function getPenjualanReportQuery(array $filters)
    {
        $query = Penjualan::with('dropshipper', 'user')
            ->whereDate('tanggal', '>=', $filters['dari_tanggal'])
            ->whereDate('tanggal', '<=', $filters['sampai_tanggal']);

        if ($filters['dropshipper_id']) {
            $query->where('dropshipper_id', $filters['dropshipper_id']);
        }

        return $query->latest('tanggal');
    }

    private function getStokReportQuery(array $filters)
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

    private function resolveBarangFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'barang_id' => 'nullable|exists:barang,id',
            'stok' => 'nullable|in:semua,aman,minimum,habis',
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
        ];
    }

    private function resolvePembelianFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'supplier_id' => 'nullable|exists:supplier,id',
        ]);

        [$dariTanggal, $sampaiTanggal] = $this->resolveDateRange(
            $request->dari_tanggal,
            $request->sampai_tanggal
        );

        return [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'supplier_id' => $request->filled('supplier_id') ? (int) $request->supplier_id : null,
        ];
    }

    private function resolvePenjualanFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'dropshipper_id' => 'nullable|exists:dropshipper,id',
        ]);

        [$dariTanggal, $sampaiTanggal] = $this->resolveDateRange(
            $request->dari_tanggal,
            $request->sampai_tanggal
        );

        return [
            'dari_tanggal' => $dariTanggal,
            'sampai_tanggal' => $sampaiTanggal,
            'dropshipper_id' => $request->filled('dropshipper_id') ? (int) $request->dropshipper_id : null,
        ];
    }

    private function resolveStokFilters(Request $request): array
    {
        $request->validate([
            'dari_tanggal' => 'nullable|date',
            'sampai_tanggal' => 'nullable|date',
            'barang_id' => 'nullable|exists:barang,id',
            'status' => 'nullable|in:semua,aman,minimum,habis',
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
        ];
    }

    private function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        $today = now();
        $defaultStart = $today->copy()->startOfMonth()->toDateString();
        $defaultEnd = $today->copy()->endOfMonth()->toDateString();

        $dariTanggal = $startDate ? Carbon::parse($startDate)->toDateString() : $defaultStart;
        $sampaiTanggal = $endDate ? Carbon::parse($endDate)->toDateString() : $defaultEnd;

        if ($dariTanggal > $sampaiTanggal) {
            [$dariTanggal, $sampaiTanggal] = [$sampaiTanggal, $dariTanggal];
        }

        return [$dariTanggal, $sampaiTanggal];
    }
}
