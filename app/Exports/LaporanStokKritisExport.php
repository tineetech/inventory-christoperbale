<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class LaporanStokKritisExport implements FromView, WithStyles
{
    public function __construct(
        private $stokKritis,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.stok-kritis-excel', [
            'stokKritis' => $this->stokKritis,
            'filters' => $this->filters,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A3:H3')->getFont()->setBold(true);

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())
                  ->setAutoSize(true);
        }
    }
}