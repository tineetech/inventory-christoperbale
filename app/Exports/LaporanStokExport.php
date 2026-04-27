<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanStokExport implements FromView
{
    public function __construct(
        private $rows,
        private array $filters,
        private string $tableType = 'summary'
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.stok-excel', [
            'rows' => $this->rows,
            'filters' => $this->filters,
            'tableType' => $this->tableType,
        ]);
    }
}
