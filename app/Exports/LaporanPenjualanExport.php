<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanPenjualanExport implements FromView, ShouldAutoSize
{
    public function __construct(
        private $penjualan,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.penjualan-excel', [
            'penjualan' => $this->penjualan,
            'filters' => $this->filters,
        ]);
    }
}
