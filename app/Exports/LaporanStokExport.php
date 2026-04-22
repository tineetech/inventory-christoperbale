<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanStokExport implements FromView
{
    public function __construct(
        private $barang,
        private array $filters
    ) {
    }

    public function view(): View
    {
        return view('pages.laporan.exports.stok-excel', [
            'barang' => $this->barang,
            'filters' => $this->filters,
        ]);
    }
}
