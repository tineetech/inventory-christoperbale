<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class LaporanBarangExport implements FromView, WithStyles
{
    public function __construct(
        private $barang,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.barang-excel', [
            'barang' => $this->barang,
            'filters' => $this->filters,
        ]);
    }
    
    public function styles(Worksheet $sheet)
    {
        // Auto width semua kolom berdasarkan konten
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())
                  ->setAutoSize(true);
        }
    }
}
